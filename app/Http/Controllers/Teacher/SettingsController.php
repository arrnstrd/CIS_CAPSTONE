<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function profile(Request $request)
    {
        $user = $request->user();
        $teacher = $user?->teacher;

        return view('teacher-modules.settings.profile', [
            'user' => $user,
            'teacher' => $teacher,
        ]);
    }

    public function notifications(Request $request)
    {
        $user = $request->user();
        $preferences = $user->getOrCreateNotificationPreference();

        return view('teacher-modules.settings.notifications', [
            'user' => $user,
            'preferences' => $preferences,
        ]);
    }

    public function appearance(Request $request)
    {
        $user = $request->user();
        $dashboardPreferences = $user->getOrCreateDashboardPreference();

        return view('teacher-modules.settings.appearance', [
            'user' => $user,
            'dashboardPreferences' => $dashboardPreferences,
        ]);
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $teacher = $user?->teacher;
        $dashboardPreferences = $user->getOrCreateDashboardPreference();

        $assignedClasses = $teacher
            ? TeachingAssignment::where('teacher_id', $teacher->id)
                ->where('status', 'active')
                ->with(['section', 'subject'])
                ->get()
            : collect();

        return view('teacher-modules.settings.dashboard-preferences', [
            'user' => $user,
            'teacher' => $teacher,
            'dashboardPreferences' => $dashboardPreferences,
            'assignedClasses' => $assignedClasses,
        ]);
    }

    public function security(Request $request)
    {
        $user = $request->user();

        return view('teacher-modules.settings.security', [
            'user' => $user,
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

        return redirect()->route('teacher.settings.security')
            ->with('success', 'Password updated successfully.');
    }

    public function updateNotificationPreferences(Request $request)
    {
        $user = $request->user();
        $preferences = $user->getOrCreateNotificationPreference();

        $preferences->update([
            'attendance_enabled' => $request->boolean('attendance_enabled'),
            'grading_enabled' => $request->boolean('grading_enabled'),
            'at_risk_enabled' => $request->boolean('at_risk_enabled'),
            'analytics_enabled' => $request->boolean('analytics_enabled'),
            'announcement_enabled' => $request->boolean('announcement_enabled'),
            'import_enabled' => $request->boolean('import_enabled'),
        ]);

        return redirect()->route('teacher.settings.notifications')
            ->with('success', 'Notification preferences updated successfully.');
    }

    public function updateDashboardPreferences(Request $request)
    {
        $user = $request->user();
        $teacher = $user?->teacher;

        $request->validate([
            'default_view' => ['required', 'string', 'in:overview,my_classes,analytics'],
            'dashboard_density' => ['required', 'string', 'in:comfortable,compact'],
            'default_class_id' => ['nullable', 'integer'],
            'default_term' => ['nullable', 'string', 'in:current,term_1,term_2,term_3'],
        ]);

        $defaultClassId = $request->filled('default_class_id') ? (int) $request->input('default_class_id') : null;
        if ($defaultClassId && $teacher) {
            $valid = TeachingAssignment::where('teacher_id', $teacher->id)
                ->where('id', $defaultClassId)
                ->where('status', 'active')
                ->exists();
            if (! $valid) {
                $defaultClassId = null;
            }
        } else {
            $defaultClassId = null;
        }

        $dashboardPreferences = $user->getOrCreateDashboardPreference();
        $dashboardPreferences->update([
            'default_view' => $request->input('default_view', 'overview'),
            'dashboard_density' => $request->input('dashboard_density', 'comfortable'),
            'show_grading_progress' => $request->boolean('show_grading_progress'),
            'show_class_health' => $request->boolean('show_class_health'),
            'show_at_risk' => $request->boolean('show_at_risk'),
            'show_recent_activity' => $request->boolean('show_recent_activity'),
            'show_summary_cards' => $request->boolean('show_summary_cards'),
            'show_student_counts' => $request->boolean('show_student_counts'),
            'show_progress_indicators' => $request->boolean('show_progress_indicators'),
            'default_class_id' => $defaultClassId,
            'default_term' => $request->input('default_term', 'current'),
        ]);

        return redirect()->route('teacher.settings.dashboard')
            ->with('success', 'Dashboard preferences updated successfully.');
    }

    public function updateAppearance(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'theme' => ['required', 'string', 'in:light,dark,system'],
        ]);

        $dashboardPreferences = $user->getOrCreateDashboardPreference();
        $dashboardPreferences->update([
            'theme' => $request->input('theme', 'system'),
        ]);

        return redirect()->route('teacher.settings.appearance')
            ->with('success', 'Appearance preferences updated successfully.');
    }
}
