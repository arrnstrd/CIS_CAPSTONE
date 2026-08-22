<?php

namespace App\Http\Controllers\AdministrationFeature\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\InvitationService;
use App\Mail\InvitationMail;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function store(Request $request)
    {
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
                'in:admin,teacher,scanner_operator',
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

            $setupLink = url('/setup/' . $result['plainToken']);
            $expiresAt = $result['invitation']->expires_at->format('Y-m-d H:i:s');

            Mail::to($user->email)->send(new InvitationMail(
                $user->first_name . ' ' . $user->last_name,
                $setupLink,
                $expiresAt
            ));

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
        $user = User::findOrFail($id);

        if (Auth::id() === $user->id) {
            return response()->json([
                'message' => 'You cannot update your own account.',
            ], 403);
        }

        $validatedData = $request->validate(array_merge(
            $this->sharedValidationRules($request),
            [
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->ignore($user->id),
                ],
                'password' => [
                    'sometimes',
                    'confirmed',
                    'string',
                    Password::min(8)
                        ->letters()
                        ->mixedCase()
                        ->numbers()
                        ->symbols(),
                ],
            ]
        ));

        $validatedData = $this->normalizeEmail($validatedData);

        try {
            $user->update($validatedData);

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
        $user = User::findOrFail($id);

        if (Auth::id() === $user->id) {
            return response()->json([
                'message' => 'You cannot archive your own account.',
            ], 403);
        }

        try {
            $user->delete();

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
        $user = User::withTrashed()->findOrFail($id);

        if (! $user->trashed()) {
            return response()->json([
                'message' => 'User is already active',
            ], 422);
        }

        try {
            $user->restore();

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
        $user = User::findOrFail($id);

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
            $setupLink = url('/setup/' . $result['plainToken']);
            $expiresAt = $result['invitation']->expires_at->format('Y-m-d H:i:s');

            Mail::to($user->email)->send(new InvitationMail(
                $user->first_name . ' ' . $user->last_name,
                $setupLink,
                $expiresAt
            ));

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
        if ($request->has('current_password')) {
            $request->validate(['current_password' => ['required', 'current_password']]);
        }

        $user = User::findOrFail($id);

        if (Auth::id() === $user->id) {
            return response()->json([
                'message' => 'You cannot deactivate your own account.',
            ], 403);
        }

        if ($user->isProtectedAdmin()) {
            return response()->json([
                'message' => 'The protected administrator account cannot be deactivated.',
            ], 403);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Only active users can be deactivated.',
            ], 422);
        }

        try {
            $user->update(['status' => 'inactive']);

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
        if ($request->has('current_password')) {
            $request->validate(['current_password' => ['required', 'current_password']]);
        }

        $user = User::findOrFail($id);

        if (Auth::id() === $user->id) {
            return response()->json([
                'message' => 'You cannot reactivate your own account.',
            ], 403);
        }

        if ($user->isProtectedAdmin()) {
            return response()->json([
                'message' => 'The protected administrator account cannot be modified.',
            ], 403);
        }

        if ($user->status !== 'inactive') {
            return response()->json([
                'message' => 'Only inactive users can be reactivated.',
            ], 422);
        }

        try {
            $user->update(['status' => 'active']);

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
        $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'integer', 'exists:users,id'],
            'current_password' => ['required', 'current_password'],
        ]);

        $protectedIds = User::where('id', 1)->orWhere('employee_id', 'EMP-2026-0001')->pluck('id')->toArray();
        $userIds = array_diff($request->user_ids, array_merge([Auth::id()], $protectedIds));

        if (empty($userIds)) {
            return response()->json([
                'message' => 'No valid users selected for deactivation.',
            ], 422);
        }

        $count = User::whereIn('id', $userIds)
            ->where('status', 'active')
            ->update(['status' => 'inactive']);

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
        $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'integer', 'exists:users,id'],
            'current_password' => ['required', 'current_password'],
        ]);

        $protectedIds = User::where('id', 1)->orWhere('employee_id', 'EMP-2026-0001')->pluck('id')->toArray();
        $userIds = array_diff($request->user_ids, array_merge([Auth::id()], $protectedIds));

        if (empty($userIds)) {
            return response()->json([
                'message' => 'No valid users selected for reactivation.',
            ], 422);
        }

        $count = User::whereIn('id', $userIds)
            ->where('status', 'inactive')
            ->update(['status' => 'active']);

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
                $setupLink = url('/setup/' . $result['plainToken']);
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
                'in:admin,teacher,scanner_operator',
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
}
