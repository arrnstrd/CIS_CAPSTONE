<?php

namespace App\Http\Controllers\SchoolAdmin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\SchoolYear;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        return view('pov.school-admin.settings.settings', [
            'user' => $request->user(),
            'schoolYears' => SchoolYear::orderByDesc('is_active')
                ->orderBy('school_year', 'desc')
                ->get(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $rules = [
            'current_password' => ['required', 'current_password'],
        ];

        if ($request->has('first_name') || $request->has('last_name')) {
            $rules['first_name'] = ['required', 'string', 'max:100'];
            $rules['last_name']  = ['required', 'string', 'max:100'];
        }

        if ($request->has('email')) {
            $rules['email'] = ['required', 'email', 'max:255', 'unique:users,email,' . $user->id];
        }

        $validated = $request->validate($rules);

        $data = [];
        if (isset($validated['first_name'])) {
            $data['first_name'] = trim($validated['first_name']);
        }
        if (isset($validated['last_name'])) {
            $data['last_name'] = trim($validated['last_name']);
        }
        if (isset($validated['email'])) {
            $data['email'] = trim($validated['email']);
        }

        if (!empty($data)) {
            $user->update($data);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Account details updated successfully.',
                'user'    => [
                    'id'         => $user->id,
                    'first_name' => $user->first_name,
                    'last_name'  => $user->last_name,
                    'full_name'  => trim($user->first_name . ' ' . $user->last_name),
                    'email'      => $user->email,
                    'initials'   => mb_strtoupper(mb_substr($user->first_name ?? '', 0, 1) . mb_substr($user->last_name ?? '', 0, 1)) ?: 'SA',
                ],
            ]);
        }

        return redirect()->route('settings.index')
            ->with('settings_tab', $request->input('settings_tab', 'account-security'))
            ->with('success', 'Account details updated successfully.');
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

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully.',
            ]);
        }

        return redirect()->route('settings.index')
            ->with('settings_tab', $request->input('settings_tab', 'account-security'))
            ->with('success', 'Password updated successfully.');
    }
}
