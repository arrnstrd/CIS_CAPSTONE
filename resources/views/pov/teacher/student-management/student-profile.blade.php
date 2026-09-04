<x-layouts.teacher>
    <x-slot name="title">
        {{ $student->first_name }}
        {{ $student->middle_name ? $student->middle_name . ' ' : '' }}{{ $student->last_name }}
    </x-slot>

    <x-slot name="pageName">Student Profile</x-slot>
    <x-slot name="subtitle">Read-only student profile and enrollment records.</x-slot>

    <x-slot name="headerActions">
        <a href="{{ route('teacher.student-management') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Student Management
        </a>
    </x-slot>

    @include('pov.teacher.student-management.student-profile-content')
</x-layouts.teacher>