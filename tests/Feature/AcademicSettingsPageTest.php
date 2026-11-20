<?php

namespace Tests\Feature;

use App\Models\AcademicSetting;
use App\Models\AssignedTeacher;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicSettingsPageTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /**
     * @param  array{marks_submission_status?: string}  $attributes
     */
    private function academicSetting(array $attributes = []): AcademicSetting
    {
        return AcademicSetting::forceCreate(array_merge(['id' => 1], $attributes));
    }

    public function test_page_is_split_into_tabs(): void
    {
        SchoolSession::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('academic.setting'))
            ->assertOk()
            ->assertSeeInOrder(['Session &amp; Terms', 'Classes &amp; Subjects', 'Teachers', 'Policies'], false);
    }

    public function test_marks_submission_switch_reflects_open_status(): void
    {
        SchoolSession::factory()->create();
        $this->academicSetting(['marks_submission_status' => 'on']);

        $this->actingAs($this->admin())
            ->get(route('academic.setting'))
            ->assertSee('id="marks_submission_status_check" checked', false)
            ->assertSee('Open');
    }

    public function test_marks_submission_switch_reflects_closed_status(): void
    {
        SchoolSession::factory()->create();
        $this->academicSetting(['marks_submission_status' => 'off']);

        $this->actingAs($this->admin())
            ->get(route('academic.setting'))
            ->assertDontSee('id="marks_submission_status_check" checked', false)
            ->assertSee('Closed');
    }

    public function test_existing_terms_and_class_teachers_are_listed(): void
    {
        $session = SchoolSession::factory()->create();
        Semester::factory()->create(['session_id' => $session->id, 'semester_name' => 'Second Term']);
        $assignment = AssignedTeacher::factory()->create([
            'class_id' => SchoolClass::factory()->create(['session_id' => $session->id, 'class_name' => 'Primary 4'])->id,
        ]);
        SchoolClass::factory()->create(['session_id' => $session->id, 'class_name' => 'Primary 5']);

        $this->actingAs($this->admin())
            ->get(route('academic.setting'))
            ->assertSee('Second Term')
            ->assertSee('Primary 4')
            ->assertSee($assignment->teacher->first_name)
            ->assertSee('Primary 5')
            ->assertSee('Not assigned');
    }

    public function test_subject_form_asks_for_a_term_first_when_none_exist(): void
    {
        $session = SchoolSession::factory()->create();
        SchoolClass::factory()->create(['session_id' => $session->id]);

        $this->actingAs($this->admin())
            ->get(route('academic.setting'))
            ->assertSee('Create at least one term before adding subjects.')
            ->assertDontSee('name="course_name"', false);
    }

    public function test_browsing_a_previous_session_is_read_only(): void
    {
        $previous = SchoolSession::factory()->create(['session_name' => '2024 - 2025', 'created_at' => now()->subYear()]);
        SchoolSession::factory()->create(['session_name' => '2025 - 2026']);

        $this->actingAs($this->admin())
            ->withSession(['browse_session_id' => $previous->id, 'browse_session_name' => $previous->session_name])
            ->get(route('academic.setting'))
            ->assertSee('(read-only)')
            ->assertSee('Switch back to current')
            ->assertDontSee('name="semester_name"', false)
            ->assertDontSee('name="class_name"', false)
            ->assertSee('<fieldset disabled', false);
    }
}
