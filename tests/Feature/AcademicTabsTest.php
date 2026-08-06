<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicTabsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.test',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
        ]);

        $this->actingAs($this->admin);
    }

    public function test_academic_page_renders_all_tab_scopes(): void
    {
        $this->seedAcademicData();

        $this->get('/academic')
            ->assertOk()
            ->assertSee('id="enrollment-table-pane"', false)
            ->assertSee('id="section-table-pane"', false)
            ->assertSee('id="subject-table-pane"', false)
            ->assertSee('Unenrolled Students')
            ->assertSee('Section Records')
            ->assertSee('Subject Records');
    }

    public function test_enrollment_search_filters_only_unenrolled_students(): void
    {
        $data = $this->seedAcademicData();

        Enrollment::create([
            'student_id' => $data['enrolledStudent']->id,
            'section_id' => $data['sectionAlpha']->id,
            'school_year_id' => $data['schoolYear']->id,
            'level' => 'elementary',
            'session_type' => 'morning',
            'status' => 'active',
        ]);

        $this->get('/academic?enrollment_search=Zara&section_search=Alpha&subject_search=Science')
            ->assertOk()
            ->assertSee('Zara')
            ->assertDontSee('Bruno')
            ->assertDontSee('Enrolled Student')
            ->assertSee('Alpha')
            ->assertSee('Science');
    }

    public function test_section_filters_use_namespaced_params_and_pagination_links_keep_only_section_params(): void
    {
        $this->seedAcademicData();

        $this->get('/academic?section_search=Alpha&section_status=active&section_grade_level=1&subject_search=Math&enrollment_search=Zara')
            ->assertOk()
            ->assertSee('Alpha')
            ->assertDontSee('Beta')
            ->assertSee('section_search=Alpha', false)
            ->assertSee('section_status=active', false)
            ->assertSee('section_grade_level=1', false)
            ->assertDontSee('section_search=Alpha&amp;subject_search=Math', false)
            ->assertDontSee('section_search=Alpha&amp;enrollment_search=Zara', false);
    }

    public function test_subject_filters_use_namespaced_params_and_pagination_links_keep_only_subject_params(): void
    {
        $this->seedAcademicData();

        $this->get('/academic?subject_search=Science&subject_level=elementary&section_search=Alpha&enrollment_search=Zara')
            ->assertOk()
            ->assertSee('Science')
            ->assertDontSee('Advanced Math')
            ->assertSee('subject_search=Science', false)
            ->assertSee('subject_level=elementary', false)
            ->assertDontSee('subject_search=Science&amp;section_search=Alpha', false)
            ->assertDontSee('subject_search=Science&amp;enrollment_search=Zara', false);
    }

    public function test_single_tab_params_do_not_filter_other_tabs(): void
    {
        $this->seedAcademicData();

        $this->get('/academic?section_search=Alpha')
            ->assertOk()
            ->assertSee('Alpha')
            ->assertDontSee('Beta')
            ->assertSee('Zara')
            ->assertSee('Bruno')
            ->assertSee('Science')
            ->assertSee('Advanced Math');
    }

    private function seedAcademicData(): array
    {
        $schoolYear = SchoolYear::create([
            'school_year' => '2026-2027',
            'is_active' => true,
        ]);

        $teacherUser = User::create([
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'role' => 'teacher',
            'status' => 'active',
            'email' => 'ada@example.test',
            'password' => 'password',
        ]);

        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'status' => 'active',
            'email' => 'ada.lovelace@example.test',
        ]);

        $sectionAlpha = Section::create([
            'name' => 'Alpha',
            'level' => 'elementary',
            'grade_level' => 1,
            'advisor_id' => $teacher->id,
            'capacity' => 30,
            'status' => 'active',
        ]);

        $sectionBeta = Section::create([
            'name' => 'Beta',
            'level' => 'highschool',
            'grade_level' => 7,
            'capacity' => 30,
            'status' => 'inactive',
        ]);

        foreach (range(2, 21) as $index) {
            Section::create([
                'name' => "Alpha {$index}",
                'level' => 'elementary',
                'grade_level' => 1,
                'capacity' => 30,
                'status' => 'active',
            ]);
        }

        $studentZara = Student::create([
            'student_number' => 'STU-2026-1001',
            'lrn' => '100000000001',
            'first_name' => 'Zara',
            'last_name' => 'Unenrolled',
            'sex' => 'female',
            'birthdate' => '2015-01-01',
            'address' => 'Baliwag',
            'status' => 'active',
        ]);

        $studentBruno = Student::create([
            'student_number' => 'STU-2026-1002',
            'lrn' => '100000000002',
            'first_name' => 'Bruno',
            'last_name' => 'Default',
            'sex' => 'male',
            'birthdate' => '2015-01-02',
            'address' => 'Baliwag',
            'status' => 'active',
        ]);

        $enrolledStudent = Student::create([
            'student_number' => 'STU-2026-1003',
            'lrn' => '100000000003',
            'first_name' => 'Enrolled',
            'last_name' => 'Student',
            'sex' => 'male',
            'birthdate' => '2015-01-03',
            'address' => 'Baliwag',
            'status' => 'active',
        ]);

        Subject::create([
            'code' => 'SCI-1',
            'name' => 'Science',
            'level' => 'elementary',
        ]);

        foreach (range(2, 21) as $index) {
            Subject::create([
                'code' => "SCI-{$index}",
                'name' => "Science {$index}",
                'level' => 'elementary',
            ]);
        }

        Subject::create([
            'code' => 'MATH-7',
            'name' => 'Advanced Math',
            'level' => 'hs',
        ]);

        return compact('schoolYear', 'sectionAlpha', 'sectionBeta', 'studentZara', 'studentBruno', 'enrolledStudent');
    }
}
