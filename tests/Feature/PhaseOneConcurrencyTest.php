<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\QrAttendance;
use App\Models\ScheduleConfig;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Services\QrSystem\QrScanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PhaseOneConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected $connectionsToTransact = [];

    public function test_two_overlapping_scans_preserve_daily_state_for_first_and_existing_in_scans(): void
    {
        $firstFixture = $this->createFixture('QR-CONCURRENT-FIRST', 'STU-CONCURRENT-FIRST');
        $existingFixture = $this->createFixture('QR-CONCURRENT-EXISTING', 'STU-CONCURRENT-EXISTING');

        $firstResults = $this->runConcurrentScans($firstFixture['code'], '2026-09-06 08:30:00');
        $this->assertSame(1, collect($firstResults)->where('status', 200)->count());
        $this->assertSame(1, collect($firstResults)->where('status', 429)->count());
        $this->assertSame(1, \App\Models\AttendanceLog::where('enrollment_id', $firstFixture['enrollment']->id)
            ->where('scan_type', 'IN')
            ->count());
        $this->assertDatabaseHas('qr_attendances', [
            'enrollment_id' => $firstFixture['enrollment']->id,
            'time_out_log_id' => null,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-09-06 08:30:00', 'Asia/Manila'));
        app(QrScanService::class)->processScan(['code' => $existingFixture['code']]);
        QrAttendance::where('enrollment_id', $existingFixture['enrollment']->id)
            ->where('attendance_date', '2026-09-06')
            ->update(['cooldown_expires_at' => null]);

        $existingResults = $this->runConcurrentScans($existingFixture['code'], '2026-09-06 15:30:00');
        $this->assertSame(1, collect($existingResults)->where('status', 200)->count());
        $this->assertSame(1, collect($existingResults)->where('status', 429)->count());
        $this->assertSame(1, \App\Models\AttendanceLog::where('enrollment_id', $existingFixture['enrollment']->id)
            ->where('scan_type', 'OUT')
            ->count());
        $this->assertSame(2, \App\Models\AttendanceLog::where('enrollment_id', $existingFixture['enrollment']->id)->count());

        $attendance = QrAttendance::where('enrollment_id', $existingFixture['enrollment']->id)
            ->where('attendance_date', '2026-09-06')
            ->firstOrFail();
        $this->assertNotNull($attendance->time_in_log_id);
        $this->assertNotNull($attendance->time_out_log_id);
        $this->assertSame(1, QrAttendance::where('enrollment_id', $existingFixture['enrollment']->id)
            ->where('attendance_date', '2026-09-06')
            ->count());
    }

    private function runConcurrentScans(string $code, string $time): array
    {
        $children = [];
        $resultFiles = [];

        for ($index = 0; $index < 2; $index++) {
            [$parentSocket, $childSocket] = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, 0);
            $resultFile = tempnam(sys_get_temp_dir(), 'phase1-concurrency-');
            $pid = pcntl_fork();

            if ($pid === -1) {
                $this->fail('Unable to fork a concurrency test process.');
            }

            if ($pid === 0) {
                fclose($parentSocket);
                fread($childSocket, 1);
                fclose($childSocket);

                Carbon::setTestNow(Carbon::parse($time, 'Asia/Manila'));
                config(['cache.stores.redis.driver' => 'array']);
                DB::purge();

                try {
                    $response = app(QrScanService::class)->processScan([
                        'code' => $code,
                        'device_id' => 'concurrency-test-' . $index,
                    ]);
                    file_put_contents($resultFile, json_encode([
                        'status' => $response->getStatusCode(),
                        'body' => $response->getData(true),
                    ]));
                    exit(0);
                } catch (\Throwable $exception) {
                    file_put_contents($resultFile, json_encode([
                        'status' => 599,
                        'error' => $exception->getMessage(),
                    ]));
                    exit(1);
                }
            }

            fclose($childSocket);
            $children[] = [$pid, $parentSocket];
            $resultFiles[] = $resultFile;
        }

        foreach ($children as [$pid, $socket]) {
            fwrite($socket, '1');
            fclose($socket);
        }

        foreach ($children as [$pid]) {
            pcntl_waitpid($pid, $status);
            $this->assertTrue(pcntl_wifexited($status));
            $this->assertSame(0, pcntl_wexitstatus($status));
        }

        return array_map(function (string $resultFile): array {
            $result = json_decode((string) file_get_contents($resultFile), true);
            unlink($resultFile);

            return $result;
        }, $resultFiles);
    }

    private function createFixture(string $code, string $studentNumber): array
    {
        $activeYear = SchoolYear::firstOrCreate(['school_year' => '2026-2027'], ['is_active' => true]);
        $inactiveYear = SchoolYear::firstOrCreate(['school_year' => '2025-2026'], ['is_active' => false]);
        $section = Section::firstOrCreate([
            'name' => 'Concurrency',
            'level' => 'highschool',
            'grade_level' => 7,
            'session_type' => 'morning',
        ], ['status' => 'active']);
        $student = Student::create([
            'lrn' => str_pad((string) Student::count(), 12, '0', STR_PAD_LEFT),
            'first_name' => 'Concurrency',
            'last_name' => 'Student',
            'sex' => 'female',
            'address' => 'Test address',
            'status' => 'active',
            'student_number' => $studentNumber,
        ]);
        Guardian::create([
            'student_id' => $student->id,
            'name' => 'Parent',
            'relationship' => 'guardian',
            'email' => null,
        ]);
        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'section_id' => $section->id,
            'school_year_id' => $activeYear->id,
            'grade_level' => '7',
            'status' => 'active',
        ]);
        Enrollment::create([
            'student_id' => $student->id,
            'section_id' => $section->id,
            'school_year_id' => $inactiveYear->id,
            'grade_level' => '7',
            'status' => 'active',
        ]);
        $student->qrCode()->create(['code' => $code, 'is_active' => true]);
        ScheduleConfig::firstOrCreate([
            'level' => 'hs',
            'session_type' => 'morning',
        ], [
            'in_start' => '07:00',
            'in_end' => '09:00',
            'late_threshold' => '08:00',
            'out_start' => '15:00',
            'out_end' => '17:00',
        ]);

        return compact('code', 'enrollment');
    }
}
