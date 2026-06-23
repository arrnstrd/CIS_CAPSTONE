<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate(array_merge(
            $this->sharedValidationRules(),
            [
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
                'password' => [
                    'required',
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
            $user = User::create($validatedData);

            return response()->json([
                'message' => 'User created successfully',
                'data' => $user,
            ], 201);
        } catch (QueryException $e) {
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
            $this->sharedValidationRules(),
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

    private function sharedValidationRules(): array
    {
        return [
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
                'in:active,inactive,suspended',
            ],
        ];
    }

    private function normalizeEmail(array $validatedData): array
    {
        $validatedData['email'] = strtolower($validatedData['email']);

        return $validatedData;
    }
}