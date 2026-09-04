<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherStudentManagementAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;
    private Teacher $teacher;
    private SchoolYear $schoolYear;
    private Section $mySection;
    private Section $otherSection;
    private Student $myStudent;
    private Student $otherStudent;
    private Enrollment $myEnrollment;
    private Enrollment $otherEnrollment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->schoolYear = SchoolYear::create([
            'school_year' => '2026-2027',
            'is_active' => true,
        ]);

        $this->teacherUser = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'status' => 'active',
        ]);
        $this->teacher = Teacher::firstOrCreate(
            ['user_id' => $this->teacherUser->id],
            ['status' => 'active']
        );

        $this->mySection = Section::create([
            'name' => 'Diamond',
            'level' => 'highschool',
            'grade_level' => 7,
            'session_type' => 'morning',
            'status' => 'active',
            'advisor_id' => $this->teacher->id,
        ]);

        $this->otherSection = Section::create([
            'name' => 'Emerald',
            'level' => 'highschool',
            'grade_level' => 7,
            'session_type' => 'morning',
            'status' => 'active',
        ]);

        $subject = Subject::create([
            'name' => 'Mathematics 7',
            'code' => 'MATH-7',
            'level' => 'hs',
        ]);

        TeachingAssignment::create([
            'teacher_id' => $this->teacher->id,
            'section_id' => $this->mySection->id,
            'subject_id' => $subject->id,
            'school_year_id' => $this->schoolYear->id,
            'status' => 'active',
        ]);

        $this->myStudent = Student::create([
            'lrn' => '123456789012',
            'first_name' => 'Alice',
            'last_name' => 'Assigned',
            'sex' => 'female',
            'address' => '123 Test St',
            'age' => 12,
            'status' => 'active',
        ]);
        Guardian::create([
            'student_id' => $this->myStudent->id,
            'name' => 'Parent Alice',
            'relationship' => 'mother',
            'email' => 'parent.alice@example.com',
        ]);
        $this->myEnrollment = Enrollment::create([
            'student_id' => $this->myStudent->id,
            'section_id' => $this->mySection->id,
            'school_year_id' => $this->schoolYear->id,
            'grade_level' => 7,
            'status' => 'active',
        ]);

        $this->otherStudent = Student::create([
            'lrn' => '987654321098',
            'first_name' => 'Bob',
            'last_name' => 'Unassigned',
            'sex' => 'male',
            'address' => '456 Other St',
            'age' => 12,
            'status' => 'active',
        ]);
        Guardian::create([
            'student_id' => $this->otherStudent->id,
            'name' => 'Parent Bob',
            'relationship' => 'father',
            'email' => 'parent.bob@example.com',
        ]);
        $this->otherEnrollment = Enrollment::create([
            'student_id' => $this->otherStudent->id,
            'section_id' => $this->otherSection->id,
            'school_year_id' => $this->schoolYear->id,
            'grade_level' => 7,
            'status' => 'active',
        ]);
    }

    public function test_teacher_can_access_student_management_index(): void
    {
        $response = $this->actingAs($this->teacherUser)
            ->get(route('teacher.student-management'));

        $response->assertStatus(200);
        $response->assertSee('Diamond');
        $response->assertDontSee('Emerald');
    }

    public function test_teacher_can_view_students_in_assigned_section(): void
    {
        $response = $this->actingAs($this->teacherUser)
            ->get(route('teacher.student-management', ['section_id' => $this->mySection->id]));

        $response->assertStatus(200);
        $response->assertSee('Alice');
        $response->assertDontSee('Bob');
    }

    public function test_teacher_cannot_view_unassigned_section(): void
    {
        $response = $this->actingAs($this->teacherUser)
            ->get(route('teacher.student-management', ['section_id' => $this->otherSection->id]));

        $response->assertStatus(403);
    }

    public function test_teacher_visiting_admin_student_management_is_redirected(): void
    {
        $response = $this->actingAs($this->teacherUser)
            ->get(route('student-management.index'));

        $response->assertRedirect(route('teacher.student-management'));
    }

    public function test_teacher_visiting_admin_grade_route_is_redirected(): void
    {
        $response = $this->actingAs($this->teacherUser)
            ->get(route('student-management.grade', 7));

        $response->assertRedirect(route('teacher.student-management'));
    }

    public function test_teacher_visiting_admin_section_route_for_own_section_is_redirected_to_teacher_view(): void
    {
        $response = $this->actingAs($this->teacherUser)
            ->get(route('student-management.section', ['grade' => 7, 'section' => $this->mySection->id]));

        $response->assertRedirect(route('teacher.student-management', ['section_id' => $this->mySection->id]));
    }

    public function test_teacher_visiting_admin_section_route_for_unassigned_section_is_forbidden(): void
    {
        $response = $this->actingAs($this->teacherUser)
            ->get(route('student-management.section', ['grade' => 7, 'section' => $this->otherSection->id]));

        $response->assertStatus(403);
    }

    public function test_teacher_can_view_assigned_student_profile(): void
    {
        $response = $this->actingAs($this->teacherUser)
            ->get(route('teacher.student-profile', $this->myStudent->id));

        $response->assertStatus(200);
        $response->assertSee('Alice');

        $responseGeneral = $this->actingAs($this->teacherUser)
            ->get(route('student.profile', $this->myStudent->id));

        $responseGeneral->assertStatus(200);
        $responseGeneral->assertSee('Alice');
    }

    public function test_teacher_cannot_view_unassigned_student_profile(): void
    {
        $response = $this->actingAs($this->teacherUser)
            ->get(route('teacher.student-profile', $this->otherStudent->id));

        $response->assertStatus(403);

        $responseGeneral = $this->actingAs($this->teacherUser)
            ->get(route('student.profile', $this->otherStudent->id));

        $responseGeneral->assertStatus(403);
    }

    public function test_teacher_cannot_fetch_unassigned_student_summary(): void
    {
        $response = $this->actingAs($this->teacherUser)
            ->get(route('teacher.student-profile.summary', $this->otherStudent->id));

        $response->assertStatus(403);
    }

    public function test_teacher_cannot_view_unassigned_student_in_grading_system(): void
    {
        $response = $this->actingAs($this->teacherUser)
            ->get(route('teacher.grading-system.student-profile.show', $this->otherEnrollment->id));

        $response->assertStatus(403);
    }

    public function test_teacher_can_add_student_to_assigned_section(): void
    {
        $response = $this->actingAs($this->teacherUser)->postJson(route('student.store'), [
            'lrn' => '112233445566',
            'first_name' => 'Charlie',
            'last_name' => 'NewStudent',
            'sex' => 'male',
            'address' => '789 Test Rd',
            'age' => 12,
            'status' => 'active',
            'name' => 'Parent Charlie',
            'relationship' => 'guardian',
            'email' => 'parent.charlie@example.com',
            'school_year_id' => $this->schoolYear->id,
            'grade_level' => 7,
            'section_id' => $this->mySection->id,
            'enrollment_status' => 'active',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('students', ['lrn' => '112233445566']);
    }

    public function test_teacher_cannot_add_student_to_unassigned_section(): void
    {
        $response = $this->actingAs($this->teacherUser)->postJson(route('student.store'), [
            'lrn' => '998877665544',
            'first_name' => 'David',
            'last_name' => 'BlockedStudent',
            'sex' => 'male',
            'address' => '999 Blocked Rd',
            'age' => 12,
            'status' => 'active',
            'name' => 'Parent David',
            'relationship' => 'guardian',
            'email' => 'parent.david@example.com',
            'school_year_id' => $this->schoolYear->id,
            'grade_level' => 7,
            'section_id' => $this->otherSection->id,
            'enrollment_status' => 'active',
        ]);

        $response->assertStatus(403);
    }

    public function test_teacher_cannot_update_or_delete_student_profile_directly(): void
    {
        $responseUpdate = $this->actingAs($this->teacherUser)
            ->putJson(route('students.update', $this->myStudent->id), [
                'lrn' => $this->myStudent->lrn,
                'first_name' => 'Alice Modified',
                'last_name' => 'Assigned',
                'sex' => 'female',
                'address' => '123 Test St',
                'age' => 12,
                'status' => 'active',
                'name' => 'Parent Alice',
                'relationship' => 'mother',
                'email' => 'parent.alice@example.com',
                'school_year_id' => $this->schoolYear->id,
                'grade_level' => 7,
                'section_id' => $this->mySection->id,
                'enrollment_status' => 'active',
            ]);

        $responseUpdate->assertStatus(403);

        $responseDelete = $this->actingAs($this->teacherUser)
            ->deleteJson(route('students.destroy', $this->myStudent->id));

        $responseDelete->assertStatus(403);

        $responseUpdateInfo = $this->actingAs($this->teacherUser)
            ->putJson(route('student.profile.update-info', $this->myStudent->id), [
                'first_name' => 'Alice Modified',
                'last_name' => 'Assigned',
                'sex' => 'female',
            ]);

        $responseUpdateInfo->assertStatus(403);

        $responseUpdateGuardian = $this->actingAs($this->teacherUser)
            ->putJson(route('student.profile.update-guardian', $this->myStudent->id), [
                'name' => 'Parent Alice Modified',
                'relationship' => 'mother',
            ]);

        $responseUpdateGuardian->assertStatus(403);
    }
}
