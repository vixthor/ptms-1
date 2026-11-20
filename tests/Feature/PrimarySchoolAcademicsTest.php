<?php

namespace Tests\Feature;

use App\Models\AssignedTeacher;
use App\Models\Course;
use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\Section;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PrimarySchoolAcademicsTest extends TestCase
{
    use RefreshDatabase;

    private function adminWith(string ...$permissions): User
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $admin->givePermissionTo($permissions);

        return $admin;
    }

    public function test_creating_a_class_creates_its_default_section(): void
    {
        $session = SchoolSession::factory()->create();
        $admin = $this->adminWith('create classes');

        $this->actingAs($admin)
            ->post(route('class.store'), ['class_name' => 'Primary 1', 'session_id' => $session->id])
            ->assertSessionHas('status');

        $schoolClass = SchoolClass::where('class_name', 'Primary 1')->firstOrFail();

        $this->assertDatabaseHas('sections', [
            'class_id' => $schoolClass->id,
            'session_id' => $session->id,
            'section_name' => Section::DEFAULT_NAME,
        ]);
    }

    public function test_default_section_is_reused_and_lazily_created(): void
    {
        $schoolClass = SchoolClass::factory()->create();

        $first = Section::defaultForClass($schoolClass);
        $second = Section::defaultForClass($schoolClass);

        $this->assertTrue($first->is($second));
        $this->assertSame(1, Section::where('class_id', $schoolClass->id)->count());
    }

    public function test_class_lookup_endpoint_returns_a_section_for_classes_without_one(): void
    {
        $schoolClass = SchoolClass::factory()->create();
        $admin = $this->adminWith();

        $this->actingAs($admin)
            ->getJson(route('get.sections.courses.by.classId', $schoolClass->id))
            ->assertOk()
            ->assertJsonCount(1, 'sections');
    }

    public function test_teacher_is_assigned_to_a_whole_class(): void
    {
        $schoolClass = SchoolClass::factory()->create();
        $teacher = User::factory()->create(['role' => 'teacher']);
        $admin = $this->adminWith('assign teachers');

        $this->actingAs($admin)
            ->post(route('teacher.assign'), [
                'teacher_id' => $teacher->id,
                'class_id' => $schoolClass->id,
                'session_id' => $schoolClass->session_id,
            ])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('assigned_teachers', [
            'teacher_id' => $teacher->id,
            'class_id' => $schoolClass->id,
            'course_id' => null,
            'semester_id' => null,
        ]);
    }

    public function test_reassigning_a_class_replaces_its_class_teacher(): void
    {
        $assignment = AssignedTeacher::factory()->create();
        $newTeacher = User::factory()->create(['role' => 'teacher']);
        $admin = $this->adminWith('assign teachers');

        $this->actingAs($admin)->post(route('teacher.assign'), [
            'teacher_id' => $newTeacher->id,
            'class_id' => $assignment->class_id,
            'session_id' => $assignment->session_id,
        ]);

        $this->assertSame(1, AssignedTeacher::where('class_id', $assignment->class_id)->count());
        $this->assertSame($newTeacher->id, AssignedTeacher::where('class_id', $assignment->class_id)->value('teacher_id'));
    }

    public function test_teacher_assignment_requires_an_existing_class(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $admin = $this->adminWith('assign teachers');

        $this->actingAs($admin)
            ->post(route('teacher.assign'), [
                'teacher_id' => $teacher->id,
                'class_id' => 9999,
                'session_id' => 1,
            ])
            ->assertSessionHasErrors('class_id');

        $this->assertDatabaseCount('assigned_teachers', 0);
    }

    public function test_class_teacher_sees_every_subject_of_their_class(): void
    {
        $assignment = AssignedTeacher::factory()->create();
        $semester = Semester::factory()->create(['session_id' => $assignment->session_id]);
        $otherSemester = Semester::factory()->create(['session_id' => $assignment->session_id]);

        foreach (['Mathematics', 'English'] as $subject) {
            Course::factory()->create([
                'course_name' => $subject,
                'class_id' => $assignment->class_id,
                'semester_id' => $semester->id,
                'session_id' => $assignment->session_id,
            ]);
        }
        Course::factory()->create([
            'course_name' => 'Science',
            'class_id' => $assignment->class_id,
            'semester_id' => $otherSemester->id,
            'session_id' => $assignment->session_id,
        ]);

        $this->actingAs($assignment->teacher)
            ->get(route('course.teacher.list', ['teacher_id' => $assignment->teacher_id, 'semester_id' => $semester->id]))
            ->assertOk()
            ->assertSee('My Subjects')
            ->assertSee('Mathematics')
            ->assertSee('English')
            ->assertDontSee('Science');
    }

    public function test_class_teacher_can_manage_cbt_for_any_subject_in_their_class(): void
    {
        $assignment = AssignedTeacher::factory()->create();
        $course = Course::factory()->create([
            'class_id' => $assignment->class_id,
            'session_id' => $assignment->session_id,
        ]);
        $exam = Exam::factory()->create(['course_id' => $course->id, 'class_id' => $assignment->class_id]);

        $this->actingAs($assignment->teacher)
            ->get(route('cbt.questions.index', ['exam' => $exam->id]))
            ->assertOk();
    }

    public function test_teacher_of_another_class_cannot_manage_cbt(): void
    {
        $assignment = AssignedTeacher::factory()->create();
        $exam = Exam::factory()->create();

        $this->actingAs($assignment->teacher)
            ->get(route('cbt.questions.index', ['exam' => $exam->id]))
            ->assertForbidden();
    }

    public function test_academic_settings_use_primary_school_terms(): void
    {
        SchoolSession::factory()->create();
        $admin = $this->adminWith();

        $this->actingAs($admin)
            ->get(route('academic.setting'))
            ->assertOk()
            ->assertSee('Create Subject')
            ->assertSee('Create Term for Current Session')
            ->assertSee('Assign Class Teacher')
            ->assertDontSee('Create Section')
            ->assertDontSee('Create Course')
            ->assertDontSee('Assign to section');
    }
}
