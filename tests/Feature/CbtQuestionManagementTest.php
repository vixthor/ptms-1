<?php

namespace Tests\Feature;

use App\Models\AssignedTeacher;
use App\Models\CbtQuestion;
use App\Models\Course;
use App\Models\Exam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CbtQuestionManagementTest extends TestCase
{
    use RefreshDatabase;

    private function questionPayload(array $overrides = []): array
    {
        return array_merge([
            'question' => 'Gregor Mendel is regarded as the father of ______',
            'option_a' => 'genetics',
            'option_b' => 'variation',
            'option_c' => 'mycology',
            'option_d' => 'natural selection',
            'correct_option' => 'a',
            'marks' => 1,
        ], $overrides);
    }

    public function test_assigned_teacher_can_add_a_question(): void
    {
        $course = Course::factory()->create();
        $exam = Exam::factory()->create(['course_id' => $course->id, 'class_id' => $course->class_id]);
        $teacher = User::factory()->create(['role' => 'teacher']);

        AssignedTeacher::factory()->create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'class_id' => $course->class_id,
            'session_id' => $course->session_id,
            'semester_id' => $course->semester_id,
        ]);

        $response = $this->actingAs($teacher)->post(route('cbt.questions.store', ['exam' => $exam->id]), $this->questionPayload());

        $response->assertRedirect(route('cbt.questions.index', ['exam' => $exam->id]));
        $this->assertDatabaseHas('cbt_questions', [
            'exam_id' => $exam->id,
            'correct_option' => 'a',
            'created_by' => $teacher->id,
        ]);
    }

    public function test_unassigned_teacher_cannot_add_a_question(): void
    {
        $exam = Exam::factory()->create();
        $teacher = User::factory()->create(['role' => 'teacher']);

        $response = $this->actingAs($teacher)->post(route('cbt.questions.store', ['exam' => $exam->id]), $this->questionPayload());

        $response->assertForbidden();
        $this->assertDatabaseCount('cbt_questions', 0);
    }

    public function test_headteacher_can_manage_questions_on_any_exam(): void
    {
        $exam = Exam::factory()->create();
        $headteacher = User::factory()->create(['role' => User::ROLE_HEADTEACHER]);

        $this->actingAs($headteacher)->get(route('cbt.questions.index', ['exam' => $exam->id]))->assertOk();
        $this->actingAs($headteacher)->post(route('cbt.questions.store', ['exam' => $exam->id]), $this->questionPayload())
            ->assertRedirect(route('cbt.questions.index', ['exam' => $exam->id]));
    }

    public function test_correct_option_must_reference_a_filled_option(): void
    {
        $exam = Exam::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('cbt.questions.store', ['exam' => $exam->id]), $this->questionPayload([
            'correct_option' => 'd',
            'option_d' => '',
        ]));

        $response->assertSessionHasErrors('correct_option');
        $this->assertDatabaseCount('cbt_questions', 0);
    }

    public function test_question_list_shows_the_correct_answer(): void
    {
        $exam = Exam::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        CbtQuestion::factory()->create([
            'exam_id' => $exam->id,
            'question' => 'What is 2 + 2?',
            'option_a' => '3',
            'option_b' => '4',
            'correct_option' => 'b',
        ]);

        $response = $this->actingAs($admin)->get(route('cbt.questions.index', ['exam' => $exam->id]));

        $response->assertOk();
        $response->assertSee('What is 2 + 2?');
        $response->assertSee('B. 4');
    }

    public function test_admin_can_edit_a_question(): void
    {
        $exam = Exam::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        $question = CbtQuestion::factory()->create([
            'exam_id' => $exam->id,
            'option_a' => 'genetics',
            'option_b' => 'variation',
            'correct_option' => 'a',
        ]);

        $this->actingAs($admin)
            ->get(route('cbt.questions.edit', ['exam' => $exam->id, 'question' => $question->id]))
            ->assertOk()
            ->assertSee('genetics');

        $response = $this->actingAs($admin)->post(route('cbt.questions.update', [
            'exam' => $exam->id,
            'question' => $question->id,
        ]), $this->questionPayload(['correct_option' => 'b']));

        $response->assertRedirect(route('cbt.questions.index', ['exam' => $exam->id]));
        $this->assertDatabaseHas('cbt_questions', [
            'id' => $question->id,
            'correct_option' => 'b',
        ]);
    }

    public function test_deleting_a_question_removes_it(): void
    {
        $exam = Exam::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $question = CbtQuestion::factory()->create(['exam_id' => $exam->id]);

        $response = $this->actingAs($admin)->post(route('cbt.questions.destroy', [
            'exam' => $exam->id,
            'question' => $question->id,
        ]));

        $response->assertRedirect(route('cbt.questions.index', ['exam' => $exam->id]));
        $this->assertDatabaseMissing('cbt_questions', ['id' => $question->id]);
    }

    public function test_student_cannot_manage_questions(): void
    {
        $exam = Exam::factory()->create();
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->get(route('cbt.questions.index', ['exam' => $exam->id]))->assertForbidden();
    }
}
