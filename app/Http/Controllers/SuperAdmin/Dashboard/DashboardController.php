<?php

namespace App\Http\Controllers\SuperAdmin\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LoginLog;
use App\Models\AdminActivityLog;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $activeAccounts = User::where('status', 'active')->count();
        $pendingInvitations = User::where('status', 'pending')->count();
        $inactiveAccounts = User::where('status', 'inactive')->count();

        $pendingUsers = User::where('status', 'pending')->latest('created_at')->take(10)->get();

        $recentLoginLogs = LoginLog::with('user')
            ->latest('attempted_at')
            ->take(6)
            ->get();

        $recentActivities = AdminActivityLog::with('actor')
            ->latest('created_at')
            ->take(6)
            ->get();

        return view('pov.super-admin.dashboard.overview', compact(
            'totalUsers',
            'activeAccounts',
            'pendingInvitations',
            'inactiveAccounts',
            'pendingUsers',
            'recentLoginLogs',
            'recentActivities'
        ));
    }
}
