<?php

namespace App\Http\Controllers\Teacher\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function personalInformation(Request $request)
    {
        $user = $request->user();
        $teacher = $user?->teacher;

        return view('pov.teacher.account.personal-information', [
            'user' => $user,
            'teacher' => $teacher,
        ]);
    }

    public function security(Request $request)
    {
        $user = $request->user();

        return view('pov.teacher.account.security', [
            'user' => $user,
        ]);
    }
}
