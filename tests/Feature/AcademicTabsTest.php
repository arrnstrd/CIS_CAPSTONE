<?php

namespace Tests\Feature;

use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Tests\TestCase;

class AcademicTabsTest extends TestCase
{

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

    public function test_academic_page_renders_section_subject_and_assignment_tabs(): void
    {
        $this->seedAcademicData();

        $this->get('/academic')
            ->assertOk()
            ->assertSee('id="section-table-pane"', false)
            ->assertSee('id="subject-table-pane"', false)
            ->assertSee('id="assignment-table-pane"', false)
            ->assertSee('Section Records')
            ->assertSee('Subject Records')
            ->assertSee('Teaching Assignment Records');
    }

    public function test_section_filters_use_namespaced_params_and_pagination_links_keep_only_section_params(): void
    {
        $this->seedAcademicData();

        $this->get('/academic?section_search=Alpha&section_status=active&section_grade_level=1&subject_search=Math&assignment_search=Ada')
            ->assertOk()
            ->assertSee('Alpha')
            ->assertDontSee('Beta')
            ->assertSee('section_search=Alpha', false)
            ->assertSee('section_status=active', false)
            ->assertSee('section_grade_level=1', false)
            ->assertDontSee('section_search=Alpha&amp;subject_search=Math', false)
            ->assertDontSee('section_search=Alpha&amp;assignment_search=Ada', false);
    }

    public function test_subject_filters_use_namespaced_params_and_pagination_links_keep_only_subject_params(): void
    {
        $this->seedAcademicData();

        $this->get('/academic?subject_search=Science&subject_level=elementary&section_search=Alpha&assignment_search=Ada')
            ->assertOk()
            ->assertSee('Science')
            ->assertSee('subject_search=Science', false)
            ->assertSee('subject_level=elementary', false)
            ->assertDontSee('subject_search=Science&amp;section_search=Alpha', false)
            ->assertDontSee('subject_search=Science&amp;assignment_search=Ada', false);
    }

    public function test_single_tab_params_do_not_filter_other_tabs(): void
    {
        $this->seedAcademicData();

        $this->get('/academic?section_search=Alpha')
            ->assertOk()
            ->assertSee('Alpha')
            ->assertDontSee('Beta')
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

        $teacher = Teacher::firstOrCreate(
            ['user_id' => $teacherUser->id],
            ['status' => 'active']
        );

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

        $subjectScience = Subject::create([
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

        $subjectMath = Subject::create([
            'code' => 'MATH-7',
            'name' => 'Advanced Math',
            'level' => 'hs',
        ]);

        $assignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subjectScience->id,
            'section_id' => $sectionAlpha->id,
            'school_year_id' => $schoolYear->id,
            'status' => 'active',
        ]);

        return compact('schoolYear', 'teacher', 'sectionAlpha', 'sectionBeta', 'subjectScience', 'subjectMath', 'assignment');
    }
}
