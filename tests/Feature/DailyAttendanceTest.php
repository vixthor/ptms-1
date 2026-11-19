<?php

namespace Tests\Feature;

use App\Models\AssignedTeacher;
use App\Models\Attendance;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DailyAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private function teacher(): User
    {
        Permission::findOrCreate('take attendances');

        $teacher = User::factory()->create(['role' => 'teacher']);
        $teacher->givePermissionTo('take attendances');

        return $teacher;
    }

    /**
     * @return array{0: Promotion, 1: User}
     */
    private function classWithClassTeacher(): array
    {
        $promotion = Promotion::factory()->create();
        $teacher = $this->teacher();

        AssignedTeacher::factory()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $promotion->class_id,
            'session_id' => $promotion->session_id,
        ]);

        return [$promotion, $teacher];
    }

    private function payload(Promotion $promotion, bool $present = true): array
    {
        return [
            'session_id' => $promotion->session_id,
            'class_id' => $promotion->class_id,
            'section_id' => $promotion->section_id,
            'student_ids' => [$promotion->student_id],
            'status' => $present ? [$promotion->student_id => 'on'] : [],
        ];
    }

    public function test_class_teacher_can_open_todays_register(): void
    {
        [$promotion, $teacher] = $this->classWithClassTeacher();

        $this->actingAs($teacher)
            ->get(route('attendance.create.show', ['class_id' => $promotion->class_id]))
            ->assertOk()
            ->assertSee($promotion->student->first_name)
            ->assertSee('Attendance is taken once, at the start of the school day.');
    }

    public function test_class_teacher_takes_attendance_for_the_whole_class(): void
    {
        [$promotion, $teacher] = $this->classWithClassTeacher();

        $this->actingAs($teacher)
            ->post(route('attendances.store'), $this->payload($promotion))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('attendances', [
            'student_id' => $promotion->student_id,
            'class_id' => $promotion->class_id,
            'course_id' => 0,
            'status' => 'on',
        ]);
    }

    public function test_resubmitting_on_the_same_day_corrects_instead_of_duplicating(): void
    {
        [$promotion, $teacher] = $this->classWithClassTeacher();

        $this->actingAs($teacher)->post(route('attendances.store'), $this->payload($promotion, present: true));
        $this->actingAs($teacher)->post(route('attendances.store'), $this->payload($promotion, present: false));

        $this->assertSame(1, Attendance::where('student_id', $promotion->student_id)->count());
        $this->assertSame('off', Attendance::where('student_id', $promotion->student_id)->value('status'));
    }

    public function test_a_new_day_gets_a_new_register_entry(): void
    {
        [$promotion, $teacher] = $this->classWithClassTeacher();

        Carbon::setTestNow(now()->subDay());
        $this->actingAs($teacher)->post(route('attendances.store'), $this->payload($promotion));
        Carbon::setTestNow();

        $this->actingAs($teacher)->post(route('attendances.store'), $this->payload($promotion));

        $this->assertSame(2, Attendance::where('student_id', $promotion->student_id)->count());
    }

    public function test_marking_every_student_absent_is_allowed(): void
    {
        [$promotion, $teacher] = $this->classWithClassTeacher();

        $this->actingAs($teacher)
            ->post(route('attendances.store'), $this->payload($promotion, present: false))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('attendances', ['student_id' => $promotion->student_id, 'status' => 'off']);
    }

    public function test_teacher_of_another_class_cannot_take_attendance(): void
    {
        [$promotion] = $this->classWithClassTeacher();
        $otherTeacher = $this->teacher();

        $this->actingAs($otherTeacher)
            ->get(route('attendance.create.show', ['class_id' => $promotion->class_id]))
            ->assertForbidden();

        $this->actingAs($otherTeacher)
            ->post(route('attendances.store'), $this->payload($promotion))
            ->assertForbidden();

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_class_without_a_class_teacher_cannot_have_attendance_taken(): void
    {
        $promotion = Promotion::factory()->create();
        $teacher = $this->teacher();

        $this->actingAs($teacher)
            ->post(route('attendances.store'), $this->payload($promotion))
            ->assertForbidden();
    }

    public function test_class_teacher_sees_take_attendance_for_their_class(): void
    {
        [$promotion, $teacher] = $this->classWithClassTeacher();

        $this->actingAs($teacher)
            ->get(route('course.teacher.list', ['teacher_id' => $teacher->id]))
            ->assertOk()
            ->assertSee('My Class')
            ->assertSee("Take Today's Attendance", false)
            ->assertSee($promotion->schoolClass->class_name);
    }
}
