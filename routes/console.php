<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('attendance:expire-not-in-classroom', function () {
    $count = \App\Models\AttendanceVerification::expireNotInClassroomRecords();
    $this->info("Successfully evaluated grace periods. {$count} 'not in classroom' record(s) transitioned to 'absent'.");
})->purpose('Automatically expire Not-in-Classroom attendance verifications past the 1-hour grace period');

\Illuminate\Support\Facades\Schedule::command('attendance:expire-not-in-classroom')->everyFifteenMinutes();

