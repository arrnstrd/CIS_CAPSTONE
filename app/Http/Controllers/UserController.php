<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],

            'role' => ['required', 'in:admin,teacher,scanner_operator'],
            'status' => ['required', 'in:active,inactive,suspended'],
            'email' => ['required', 'email', 'unique:users,email'],


            'password' => [
                'required',
                'confirmed',
                'string',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ]
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);

        $user = User::create($validatedData);

        return response()->json([
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }



    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        // Prevent self-demotion or self-deactivation
        if (Auth::id() === $user->id) {
            return response()->json([
                'message' => 'You cannot delete your own account.'
            ], 403);
        } 

        

        $validatedData = $request->validate([

            'first_name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z\s\-]+$/'
            ],

            'last_name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z\s\-]+$/'
            ],

            'role' => [
                'required',
                'in:admin,teacher,scanner_operator'
            ],

            'status' => [
                'required',
                'in:active,inactive,suspended'
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . $user->id
            ],

            'password' => [
                'sometimes',
                'confirmed',
                'string',

                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ]
        ]);

        // Normalize email
        $validatedData['email'] =
            strtolower($validatedData['email']);

        // Hash password only if provided
        if (isset($validatedData['password'])) {
            $validatedData['password'] =
                Hash::make($validatedData['password']);
        }

        $user->update($validatedData);

        return response()->json([
            'message' => 'User updated successfully',
            'data' => $user
        ], 200);
    }



    public function destroy(string $id)
    {
        //i dont know here haha
    }
}
