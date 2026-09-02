<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $teacher = $user?->teacher;

        return view('teacher-modules.settings.settings-index', [
            'user' => $user,
            'teacher' => $teacher,
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('teacher.settings')
            ->with('success', 'Password updated successfully.');
    }
}