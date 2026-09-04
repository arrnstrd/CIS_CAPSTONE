<?php

namespace App\Http\Controllers\SuperAdmin\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\User;
use App\Services\InvitationService;
use App\Mail\InvitationMail;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));

            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(first_name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(COALESCE(employee_id, \'\')) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw("LOWER(CONCAT(first_name, ' ', last_name)) LIKE ?", ["%{$search}%"]);
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->latest('created_at')
            ->paginate(20)
            ->appends($request->query());

        return view('pov.super-admin.user-management.users', compact('users'));
    }
    public function store(Request $request)
    {
        $actor = Auth::user();

        if (! $actor?->isSuperAdmin()) {
            AdminActivityLog::record($actor, 'Attempted User Creation', $request->email, 'denied', 'Only the Super Admin can create new users.');

            return response()->json([
                'message' => 'Unauthorized. Only the Super Admin can create new user accounts.',
            ], 403);
        }

        // Build validation rules without current_password for user creation
        $rules = [
            'first_name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z\s\-]+$/',
            ],
            'last_name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z\s\-]+$/',
            ],
            'role' => [
                'required',
                'in:super_admin,admin,teacher,scanner_operator',
            ],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
        ];

        $validatedData = $request->validate($rules);
        $validatedData = $this->normalizeEmail($validatedData);

        // New users are created as pending with no usable password
        $validatedData['status'] = 'pending';
        $validatedData['password'] = null;

        try {
            $user = User::create($validatedData);

            // Generate invitation and send email
            $invitationService = app(InvitationService::class);
            $result = $invitationService->generate($user);

            $setupLink = $this->buildSetupLink($result['plainToken']);
            $expiresAt = $result['invitation']->expires_at->format('Y-m-d H:i:s');

            Mail::to($user->email)->send(new InvitationMail(
                $user->first_name . ' ' . $user->last_name,
                $setupLink,
                $expiresAt
            ));

            AdminActivityLog::record($actor, 'Created User', $user->email, 'success', "Created {$user->role} account for {$user->first_name} {$user->last_name}", 'User', $user->id);

            return response()->json([
                'message' => 'User created successfully',
                'data' => $user,
                'setup_link' => $setupLink,
                'expires_at' => $expiresAt,
            ], 201);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Unable to create user',
                'error' => $e->getMessage(),
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to create user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $actor = Auth::user();
        $user = User::findOrFail($id);

        if ($user->isSuperAdmin() && Auth::id() !== $user->id) {
            AdminActivityLog::record($actor, 'Attempted Super Admin Modification', $user->email, 'denied', 'Super Admin accounts cannot be modified by another user.', 'User', $user->id);

            return response()->json([
                'message' => 'Super Admin accounts cannot be modified by another user.',
            ], 403);
        }

        if (! $actor?->isSuperAdmin()) {
            AdminActivityLog::record($actor, 'Attempted User Modification', $user->email, 'denied', 'Only the Super Admin can edit user accounts.', 'User', $user->id);

            return response()->json([
                'message' => 'Unauthorized. Only the Super Admin can modify user accounts.',
            ], 403);
        }

        $rules = [
            'first_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s\-]+$/'],
            'last_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s\-]+$/'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['sometimes', 'required', 'in:super_admin,admin,teacher,scanner_operator'],
            'status' => ['sometimes', 'required', 'in:active,inactive,suspended,pending'],
        ];

        if ($request->has('current_password')) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $validatedData = $request->validate($rules);
        $validatedData = $this->normalizeEmail($validatedData);

        if ($user->isSuperAdmin()) {
            $validatedData['role'] = 'super_admin';
            $validatedData['status'] = 'active';
        }

        try {
            $user->update($validatedData);

            AdminActivityLog::record($actor, 'Updated User Details', $user->email, 'success', "Updated account details for {$user->first_name} {$user->last_name}", 'User', $user->id);

            return response()->json([
                'message' => 'User updated successfully',
                'data' => $user,
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Unable to update user information',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        return $this->archive($id);
    }

    public function archive(string $id)
    {
        $actor = Auth::user();
        $user = User::findOrFail($id);

        if (! $actor?->isSuperAdmin()) {
            AdminActivityLog::record($actor, 'Attempted User Deletion', $user->email, 'denied', 'Only the Super Admin can delete user accounts.', 'User', $user->id);

            return response()->json([
                'message' => 'Unauthorized. Only the Super Admin can delete user accounts.',
            ], 403);
        }

        if ($user->isSuperAdmin()) {
            AdminActivityLog::record($actor, 'Attempted Super Admin Deletion', $user->email, 'denied', 'Super Admin accounts cannot be deleted.', 'User', $user->id);

            return response()->json([
                'message' => 'Super Admin accounts cannot be deleted or archived.',
            ], 403);
        }

        try {
            $user->delete();

            AdminActivityLog::record($actor, 'Deleted User', $user->email, 'success', "Archived user account for {$user->first_name} {$user->last_name}", 'User', $user->id);

            return response()->json([
                'message' => 'User archived successfully',
                'data' => $user,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to archive user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function restore(string $id)
    {
        $actor = Auth::user();
        $user = User::withTrashed()->findOrFail($id);

        if (! $actor?->isSuperAdmin()) {
            AdminActivityLog::record($actor, 'Attempted User Restoration', $user->email, 'denied', 'Only the Super Admin can restore user accounts.', 'User', $user->id);

            return response()->json([
                'message' => 'Unauthorized. Only the Super Admin can restore user accounts.',
            ], 403);
        }

        if (! $user->trashed()) {
            return response()->json([
                'message' => 'User is already active',
            ], 422);
        }

        try {
            $user->restore();

            AdminActivityLog::record($actor, 'Restored User', $user->email, 'success', "Restored user account for {$user->first_name} {$user->last_name}", 'User', $user->id);

            return response()->json([
                'message' => 'User restored successfully',
                'data' => $user->fresh(),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to restore user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function resendInvitation(Request $request, string $id)
    {
        $actor = Auth::user();
        $user = User::findOrFail($id);

        if (! $actor?->isSuperAdmin()) {
            AdminActivityLog::record($actor, 'Attempted Resending Invitation', $user->email, 'denied', 'Only the Super Admin can resend setup invitations.', 'User', $user->id);

            return response()->json([
                'message' => 'Unauthorized. Only the Super Admin can resend setup invitations.',
            ], 403);
        }

        // Only pending users can receive a resend
        if ($user->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending users can receive invitation resends.',
            ], 422);
        }

        try {
            $invitationService = app(InvitationService::class);
            $result = $invitationService->resend($user);

            // Send the invitation email
            $setupLink = $this->buildSetupLink($result['plainToken']);
            $expiresAt = $result['invitation']->expires_at->format('Y-m-d H:i:s');

            Mail::to($user->email)->send(new InvitationMail(
                $user->first_name . ' ' . $user->last_name,
                $setupLink,
                $expiresAt
            ));

            AdminActivityLog::record($actor, 'Resent Invitation', $user->email, 'success', "Resent setup invitation email to {$user->email}", 'User', $user->id);

            return response()->json([
                'message' => 'Invitation resent successfully.',
                'setup_link' => $setupLink,
                'expires_at' => $expiresAt,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to resend invitation.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Deactivate a user account (status: active → inactive).
     * Does NOT soft-delete the user record.
     */
    public function deactivate(Request $request, string $id)
    {
        $actor = Auth::user();
        $user = User::findOrFail($id);

        if (! $actor?->isSuperAdmin()) {
            AdminActivityLog::record($actor, 'Attempted User Deactivation', $user->email, 'denied', 'Only the Super Admin can deactivate user accounts.', 'User', $user->id);

            return response()->json([
                'message' => 'Unauthorized. Only the Super Admin can deactivate user accounts.',
            ], 403);
        }

        if ($request->has('current_password')) {
            $request->validate(['current_password' => ['required', 'current_password']]);
        }

        if (Auth::id() === $user->id) {
            return response()->json([
                'message' => 'You cannot deactivate your own account.',
            ], 403);
        }

        if ($user->isSuperAdmin()) {
            AdminActivityLog::record($actor, 'Attempted Super Admin Deactivation', $user->email, 'denied', 'Super Admin accounts cannot be deactivated.', 'User', $user->id);

            return response()->json([
                'message' => 'Super Admin accounts cannot be deactivated.',
            ], 403);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Only active users can be deactivated.',
            ], 422);
        }

        try {
            $user->update(['status' => 'inactive']);

            AdminActivityLog::record($actor, 'Deactivated User', $user->email, 'success', "Deactivated user account for {$user->first_name} {$user->last_name}", 'User', $user->id);

            return response()->json([
                'message' => 'User deactivated successfully',
                'data' => $user,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to deactivate user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reactivate a user account (status: inactive → active).
     * Does NOT soft-delete the user record.
     */
    public function reactivate(Request $request, string $id)
    {
        $actor = Auth::user();
        $user = User::findOrFail($id);

        if (! $actor?->isSuperAdmin()) {
            AdminActivityLog::record($actor, 'Attempted User Reactivation', $user->email, 'denied', 'Only the Super Admin can reactivate user accounts.', 'User', $user->id);

            return response()->json([
                'message' => 'Unauthorized. Only the Super Admin can reactivate user accounts.',
            ], 403);
        }

        if ($request->has('current_password')) {
            $request->validate(['current_password' => ['required', 'current_password']]);
        }

        if (Auth::id() === $user->id) {
            return response()->json([
                'message' => 'You cannot reactivate your own account.',
            ], 403);
        }

        if ($user->isSuperAdmin()) {
            AdminActivityLog::record($actor, 'Attempted Super Admin Modification', $user->email, 'denied', 'Super Admin accounts cannot be modified.', 'User', $user->id);

            return response()->json([
                'message' => 'Super Admin accounts cannot be modified.',
            ], 403);
        }

        if ($user->status !== 'inactive') {
            return response()->json([
                'message' => 'Only inactive users can be reactivated.',
            ], 422);
        }

        try {
            $user->update(['status' => 'active']);

            AdminActivityLog::record($actor, 'Reactivated User', $user->email, 'success', "Reactivated user account for {$user->first_name} {$user->last_name}", 'User', $user->id);

            return response()->json([
                'message' => 'User reactivated successfully',
                'data' => $user,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to reactivate user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk deactivate user accounts.
     */
    public function bulkDeactivate(Request $request)
    {
        $actor = Auth::user();

        if (! $actor?->isSuperAdmin()) {
            AdminActivityLog::record($actor, 'Attempted Bulk Deactivation', 'Multiple Users', 'denied', 'Only the Super Admin can execute bulk user actions.');

            return response()->json([
                'message' => 'Unauthorized. Only the Super Admin can execute bulk user actions.',
            ], 403);
        }

        $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'integer', 'exists:users,id'],
            'current_password' => ['required', 'current_password'],
        ]);

        $protectedIds = User::where('role', 'super_admin')
            ->pluck('id')
            ->toArray();

        $userIds = array_diff($request->user_ids, array_merge([Auth::id()], $protectedIds));

        if (empty($userIds)) {
            return response()->json([
                'message' => 'No valid users selected for deactivation.',
            ], 422);
        }

        $count = User::whereIn('id', $userIds)
            ->where('status', 'active')
            ->update(['status' => 'inactive']);

        AdminActivityLog::record($actor, 'Bulk Deactivated Users', "{$count} User(s)", 'success', "Bulk deactivated {$count} active user accounts");

        return response()->json([
            'message' => "{$count} user(s) deactivated successfully.",
            'affected' => $count,
        ], 200);
    }

    /**
     * Bulk reactivate user accounts.
     */
    public function bulkReactivate(Request $request)
    {
        $actor = Auth::user();

        if (! $actor?->isSuperAdmin()) {
            AdminActivityLog::record($actor, 'Attempted Bulk Reactivation', 'Multiple Users', 'denied', 'Only the Super Admin can execute bulk user actions.');

            return response()->json([
                'message' => 'Unauthorized. Only the Super Admin can execute bulk user actions.',
            ], 403);
        }

        $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'integer', 'exists:users,id'],
            'current_password' => ['required', 'current_password'],
        ]);

        $protectedIds = User::where('role', 'super_admin')
            ->pluck('id')
            ->toArray();

        $userIds = array_diff($request->user_ids, array_merge([Auth::id()], $protectedIds));

        if (empty($userIds)) {
            return response()->json([
                'message' => 'No valid users selected for reactivation.',
            ], 422);
        }

        $count = User::whereIn('id', $userIds)
            ->where('status', 'inactive')
            ->update(['status' => 'active']);

        AdminActivityLog::record($actor, 'Bulk Reactivated Users', "{$count} User(s)", 'success', "Bulk reactivated {$count} inactive user accounts");

        return response()->json([
            'message' => "{$count} user(s) reactivated successfully.",
            'affected' => $count,
        ], 200);
    }

    /**
     * Bulk resend invitations to pending users.
     */
    public function bulkResendInvitation(Request $request)
    {
        $actor = Auth::user();

        if (! $actor?->isSuperAdmin()) {
            AdminActivityLog::record($actor, 'Attempted Bulk Resending Invitations', 'Multiple Users', 'denied', 'Only the Super Admin can execute bulk user actions.');

            return response()->json([
                'message' => 'Unauthorized. Only the Super Admin can execute bulk user actions.',
            ], 403);
        }

        $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'integer', 'exists:users,id'],
        ]);

        $users = User::whereIn('id', $request->user_ids)
            ->where('status', 'pending')
            ->get();

        $count = 0;
        $invitationService = app(InvitationService::class);

        foreach ($users as $user) {
            try {
                $result = $invitationService->resend($user);
                $setupLink = $this->buildSetupLink($result['plainToken']);
                $expiresAt = $result['invitation']->expires_at->format('Y-m-d H:i:s');

                Mail::to($user->email)->send(new InvitationMail(
                    $user->first_name . ' ' . $user->last_name,
                    $setupLink,
                    $expiresAt
                ));
                $count++;
            } catch (Exception $e) {
                // Continue sending to remaining users
            }
        }

        AdminActivityLog::record($actor, 'Bulk Resent Invitations', "{$count} User(s)", 'success', "Bulk resent setup invitations to {$count} pending user(s)");

        return response()->json([
            'message' => "Invitation resent to {$count} pending user(s).",
            'affected' => $count,
        ], 200);
    }

    private function sharedValidationRules(Request $request): array
    {
        $rules = [
            'first_name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z\s\-]+$/',
            ],
            'last_name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z\s\-]+$/',
            ],
            'role' => [
                'required',
                'in:super_admin,admin,teacher,scanner_operator',
            ],
            'status' => [
                'required',
                'in:active,inactive,suspended,pending',
            ],
        ];

        if (Auth::check() && Auth::user()?->isAdmin()) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        return $rules;
    }

    private function normalizeEmail(array $validatedData): array
    {
        $validatedData['email'] = strtolower($validatedData['email']);

        return $validatedData;
    }

    private function buildSetupLink(string $plainToken): string
    {
        $appUrl = config('app.url');

        if (empty($appUrl) || str_contains($appUrl, 'localhost') || str_contains($appUrl, '127.0.0.1')) {
            $baseUrl = 'https://cis-capstone.onrender.com';
        } else {
            $baseUrl = rtrim($appUrl, '/');
        }

        return $baseUrl . '/setup/' . $plainToken;
    }
}
