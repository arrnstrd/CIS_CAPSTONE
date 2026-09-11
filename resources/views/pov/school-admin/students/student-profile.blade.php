<x-dynamic-component :component="auth()->user()?->isTeacher() ? 'layouts.teacher' : 'layouts.school-admin'">
    <x-slot name="title">
        {{ $student->first_name }}
        {{ $student->middle_name ? $student->middle_name . ' ' : '' }}{{ $student->last_name }}
    </x-slot>

    <x-slot name="pageName">Student Profile</x-slot>
    <x-slot name="subtitle">View and manage the student's complete profile and records.</x-slot>

    @include('pov.school-admin.students.student-profile-content')
</x-dynamic-component>