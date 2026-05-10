<?php

namespace App\Http\Controllers;

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
            'email' => ['required', 'email' , 'unique:users,email'],


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
       ] , 201);
    }



}
