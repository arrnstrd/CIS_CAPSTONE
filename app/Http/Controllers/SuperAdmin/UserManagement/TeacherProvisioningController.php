<?php

namespace App\Http\Controllers\SuperAdmin\UserManagement;

use App\Http\Controllers\Controller;
use App\Mail\InvitationMail;
use App\Models\Teacher;
use App\Models\User;
use App\Services\InvitationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class TeacherProvisioningController extends Controller
{
    private function validationRules(?User $user = null): array
    {
        return [
            'first_name' => [
                'required',
                'string',
                'max:255',
            ],
            'last_name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')
                    ->ignore($user?->id),
            ],
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            $this->validationRules()
        );

        try {
            $teacher = DB::transaction(function () use ($validated) {
                $user = User::create([
                    'first_name' => ucwords(strtolower(trim($validated['first_name']))),
                    'last_name' => ucwords(strtolower(trim($validated['last_name']))),
                    'email' => strtolower(trim($validated['email'])),
                    'role' => 'teacher',
                    'status' => 'pending',
                    'password' => null,
                ]);

                // Generate invitation (plaintext token exists only in memory)
                $invitationService = app(InvitationService::class);
                $result = $invitationService->generate($user);

                // Create teacher within same transaction
                return [
                    'user' => $user,
                    'teacher' => Teacher::create([
                        'user_id' => $user->id,
                        'status' => 'pending',
                    ]),
                    'result' => $result,
                ];
            });

            // Send invitation email AFTER transaction commits successfully
            $result = $teacher['result'];
            $setupLink = $this->buildSetupLink($result['plainToken']);
            $expiresAt = $result['invitation']->expires_at->format('Y-m-d H:i:s');

            try {
                Mail::to($teacher['user']->email)->send(new InvitationMail(
                    $teacher['user']->first_name . ' ' . $teacher['user']->last_name,
                    $setupLink,
                    $expiresAt
                ));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send teacher invitation email: ' . $e->getMessage(), [
                    'email' => $teacher['user']->email
                ]);
                return response()->json([
                    'message' => 'Teacher created successfully, but the invitation email failed to send: ' . $e->getMessage(),
                    'data' => $teacher['teacher']->fresh()->load('user'),
                    'mail_error' => $e->getMessage()
                ], 201);
            }

            return response()->json([
                'message' => 'Teacher created successfully. Invitation sent to email.',
                'data' => $teacher['teacher']->fresh()->load('user'),
            ], 201);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Unable to create teacher.',
                'error' => $e->getMessage()
            ], 422);
        }
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
