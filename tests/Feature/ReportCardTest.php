<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\FinalMark;
use App\Models\GradeRule;
use App\Models\GradingSystem;
use App\Models\Promotion;
use App\Models\ReportCard;
use App\Models\Section;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ReportCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_view_their_own_report_card_with_grades(): void
    {
        $promotion = Promotion::factory()->create();
        $student = $promotion->student;
        $semester = Semester::factory()->create(['session_id' => $promotion->session_id]);

        $course = Course::factory()->create([
            'class_id' => $promotion->class_id,
            'semester_id' => $semester->id,
            'session_id' => $promotion->session_id,
        ]);

        $gradingSystem = GradingSystem::factory()->create([
            'session_id' => $promotion->session_id,
            'semester_id' => $semester->id,
            'class_id' => $promotion->class_id,
        ]);

        GradeRule::factory()->create([
            'grading_system_id' => $gradingSystem->id,
            'session_id' => $promotion->session_id,
            'grade' => 'A',
            'point' => 4,
            'start_at' => 80,
            'end_at' => 100,
        ]);

        FinalMark::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'semester_id' => $semester->id,
            'session_id' => $promotion->session_id,
            'class_id' => $promotion->class_id,
            'section_id' => $promotion->section_id,
            'calculated_marks' => 90,
            'final_marks' => 90,
        ]);

        $response = $this->actingAs($student)->get(route('report.card.show', [
            'id' => $student->id,
            'semester_id' => $semester->id,
        ]));

        $response->assertOk();
        $response->assertSee($course->course_name);
        $response->assertSee('90');
        $response->assertSee('A');
    }

    public function test_student_cannot_view_another_students_report_card(): void
    {
        $studentA = User::factory()->create(['role' => 'student']);
        $studentB = User::factory()->create(['role' => 'student']);

        $this->actingAs($studentA)
            ->get(route('report.card.show', ['id' => $studentB->id]))
            ->assertForbidden();
    }

    public function test_staff_with_academic_access_can_view_any_students_report_card(): void
    {
        $headteacher = User::factory()->create(['role' => User::ROLE_HEADTEACHER]);
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($headteacher)
            ->get(route('report.card.show', ['id' => $student->id]))
            ->assertOk();
    }

    public function test_secretary_cannot_view_report_cards(): void
    {
        $secretary = User::factory()->create(['role' => User::ROLE_SECRETARY]);
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($secretary)
            ->get(route('report.card.show', ['id' => $student->id]))
            ->assertForbidden();
    }

    public function test_report_card_handles_a_student_with_no_marks_yet(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('report.card.show', ['id' => $student->id]));

        $response->assertOk();
        $response->assertSee('No final marks have been submitted');
    }

    public function test_student_can_download_their_own_report_card_as_pdf(): void
    {
        $promotion = Promotion::factory()->create();
        $student = $promotion->student;
        $semester = Semester::factory()->create(['session_id' => $promotion->session_id]);

        $course = Course::factory()->create([
            'class_id' => $promotion->class_id,
            'semester_id' => $semester->id,
            'session_id' => $promotion->session_id,
        ]);

        FinalMark::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'semester_id' => $semester->id,
            'session_id' => $promotion->session_id,
            'class_id' => $promotion->class_id,
            'section_id' => $promotion->section_id,
            'calculated_marks' => 90,
            'final_marks' => 90,
        ]);

        $response = $this->actingAs($student)->get(route('report.card.download', [
            'id' => $student->id,
            'semester_id' => $semester->id,
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_student_cannot_download_another_students_report_card(): void
    {
        $studentA = User::factory()->create(['role' => 'student']);
        $studentB = User::factory()->create(['role' => 'student']);

        $this->actingAs($studentA)
            ->get(route('report.card.download', ['id' => $studentB->id]))
            ->assertForbidden();
    }

    public function test_report_card_shows_class_average_and_position_among_classmates(): void
    {
        $section = Section::factory()->create();
        $semester = Semester::factory()->create(['session_id' => $section->session_id]);

        $course = Course::factory()->create([
            'class_id' => $section->class_id,
            'semester_id' => $semester->id,
            'session_id' => $section->session_id,
        ]);

        $topPromotion = Promotion::factory()->create([
            'section_id' => $section->id,
            'class_id' => $section->class_id,
            'session_id' => $section->session_id,
        ]);
        $bottomPromotion = Promotion::factory()->create([
            'section_id' => $section->id,
            'class_id' => $section->class_id,
            'session_id' => $section->session_id,
        ]);

        FinalMark::factory()->create([
            'student_id' => $topPromotion->student_id,
            'course_id' => $course->id,
            'semester_id' => $semester->id,
            'session_id' => $section->session_id,
            'class_id' => $section->class_id,
            'section_id' => $section->id,
            'calculated_marks' => 90,
            'final_marks' => 90,
        ]);
        FinalMark::factory()->create([
            'student_id' => $bottomPromotion->student_id,
            'course_id' => $course->id,
            'semester_id' => $semester->id,
            'session_id' => $section->session_id,
            'class_id' => $section->class_id,
            'section_id' => $section->id,
            'calculated_marks' => 60,
            'final_marks' => 60,
        ]);

        $response = $this->actingAs($topPromotion->student)->get(route('report.card.show', [
            'id' => $topPromotion->student_id,
            'semester_id' => $semester->id,
        ]));

        $response->assertOk();
        $response->assertSee('1st');
        $response->assertSee('75'); // class average of 90 and 60
    }

    public function test_only_academic_staff_can_edit_report_card_details(): void
    {
        $promotion = Promotion::factory()->create();
        $student = $promotion->student;
        $otherStudent = User::factory()->create(['role' => 'student']);

        $this->actingAs($otherStudent)
            ->get(route('report.card.edit', ['id' => $student->id]))
            ->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('report.card.edit', ['id' => $student->id]))
            ->assertOk();
    }

    public function test_academic_staff_can_update_report_card_details(): void
    {
        $promotion = Promotion::factory()->create();
        $student = $promotion->student;
        $semester = Semester::factory()->create(['session_id' => $promotion->session_id]);
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('report.card.update', ['id' => $student->id]), [
            'semester_id' => $semester->id,
            'house' => 'Blue',
            'weight' => '25kg',
            'height' => '110cm',
            'class_teacher_comment' => 'Doing great!',
        ]);

        $response->assertRedirect(route('report.card.show', ['id' => $student->id, 'semester_id' => $semester->id]));

        $this->assertDatabaseHas('report_cards', [
            'student_id' => $student->id,
            'semester_id' => $semester->id,
            'house' => 'Blue',
            'weight' => '25kg',
            'height' => '110cm',
            'class_teacher_comment' => 'Doing great!',
        ]);

        $this->actingAs($student)
            ->get(route('report.card.show', ['id' => $student->id, 'semester_id' => $semester->id]))
            ->assertSee('Blue')
            ->assertSee('Doing great!');
    }

    public function test_only_academic_staff_can_access_mass_sign(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)
            ->get(route('report.card.mass-sign.form'))
            ->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('report.card.mass-sign.form'))
            ->assertOk();
    }

    public function test_admin_can_mass_sign_report_cards_for_a_whole_class_section(): void
    {
        $section = Section::factory()->create();
        $semester = Semester::factory()->create(['session_id' => $section->session_id]);

        $promotionA = Promotion::factory()->create([
            'section_id' => $section->id,
            'class_id' => $section->class_id,
            'session_id' => $section->session_id,
        ]);
        $promotionB = Promotion::factory()->create([
            'section_id' => $section->id,
            'class_id' => $section->class_id,
            'session_id' => $section->session_id,
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('report.card.mass-sign'), [
            'semester_id' => $semester->id,
            'class_id' => $section->class_id,
            'section_id' => $section->id,
            'student_ids' => [$promotionA->student_id, $promotionB->student_id],
            'headteacher_comment' => 'A wonderful term overall.',
            'headteacher_signature' => UploadedFile::fake()->image('signature.png'),
        ]);

        $response->assertRedirect(route('report.card.mass-sign.form', [
            'semester_id' => $semester->id,
            'class_id' => $section->class_id,
            'section_id' => $section->id,
        ]));

        foreach ([$promotionA->student_id, $promotionB->student_id] as $studentId) {
            $this->assertDatabaseHas('report_cards', [
                'student_id' => $studentId,
                'semester_id' => $semester->id,
                'headteacher_comment' => 'A wonderful term overall.',
            ]);
        }

        $signature = ReportCard::where('student_id', $promotionA->student_id)->first()->headteacher_signature;
        $this->assertNotEmpty($signature);
        $this->assertFileExists(public_path($signature));
    }

    public function test_mass_sign_does_not_overwrite_comment_when_left_blank(): void
    {
        $section = Section::factory()->create();
        $semester = Semester::factory()->create(['session_id' => $section->session_id]);
        $promotion = Promotion::factory()->create([
            'section_id' => $section->id,
            'class_id' => $section->class_id,
            'session_id' => $section->session_id,
        ]);

        ReportCard::factory()->create([
            'student_id' => $promotion->student_id,
            'class_id' => $section->class_id,
            'section_id' => $section->id,
            'semester_id' => $semester->id,
            'session_id' => $section->session_id,
            'headteacher_comment' => 'Existing comment',
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('report.card.mass-sign'), [
            'semester_id' => $semester->id,
            'class_id' => $section->class_id,
            'section_id' => $section->id,
            'student_ids' => [$promotion->student_id],
            'headteacher_signature' => UploadedFile::fake()->image('signature.png'),
        ]);

        $this->assertDatabaseHas('report_cards', [
            'student_id' => $promotion->student_id,
            'semester_id' => $semester->id,
            'headteacher_comment' => 'Existing comment',
        ]);
    }
}
