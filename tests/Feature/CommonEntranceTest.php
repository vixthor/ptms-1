<?php

namespace Tests\Feature;

use App\Models\AssignedTeacher;
use App\Models\CommonEntranceCandidate;
use App\Models\Promotion;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommonEntranceTest extends TestCase
{
    use RefreshDatabase;

    private SchoolSession $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->session = SchoolSession::factory()->create();
    }

    private function classNamed(string $name): Section
    {
        return Section::factory()->create([
            'class_id' => SchoolClass::factory()->create(['class_name' => $name, 'session_id' => $this->session->id])->id,
        ]);
    }

    private function enrol(Section $section, string $firstName = 'Pupil'): Promotion
    {
        return Promotion::factory()->create([
            'student_id' => User::factory()->create(['role' => 'student', 'first_name' => $firstName])->id,
            'section_id' => $section->id,
            'class_id' => $section->class_id,
            'session_id' => $this->session->id,
        ]);
    }

    private function as(User $user): static
    {
        return $this->withSession(['browse_session_id' => $this->session->id])->actingAs($user);
    }

    public function test_tracker_opens_on_the_primary_six_class_by_default(): void
    {
        $this->enrol($this->classNamed('Primary 5'), 'Kemi');
        $primarySix = $this->classNamed('Primary 6');
        $this->classNamed('Nursery 1');
        $this->enrol($primarySix, 'Tunde');

        $this->as(User::factory()->create(['role' => User::ROLE_HEADTEACHER]))
            ->get(route('common-entrance.index'))
            ->assertOk()
            ->assertSee('Tunde')
            ->assertDontSee('Kemi');
    }

    public function test_saving_records_registration_score_and_placement(): void
    {
        $section = $this->classNamed('Primary 6');
        $registered = $this->enrol($section, 'Tunde');
        $untouched = $this->enrol($section, 'Ngozi');

        $this->as(User::factory()->create(['role' => 'admin']))
            ->post(route('common-entrance.update'), [
                'class_id' => $section->class_id,
                'candidates' => [
                    $registered->student_id => ['registration_number' => ' NCEE/2027/0042 ', 'score' => '187.5', 'admitted_school' => 'FGC Ijanikin'],
                    $untouched->student_id => ['registration_number' => '', 'score' => '', 'admitted_school' => ''],
                ],
            ])
            ->assertRedirect(route('common-entrance.index', ['class_id' => $section->class_id]))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('common_entrance_candidates', [
            'student_id' => $registered->student_id,
            'session_id' => $this->session->id,
            'registration_number' => 'NCEE/2027/0042',
            'score' => 187.5,
            'admitted_school' => 'FGC Ijanikin',
        ]);
        $this->assertDatabaseMissing('common_entrance_candidates', ['student_id' => $untouched->student_id]);

        $this->as(User::factory()->create(['role' => 'admin']))
            ->get(route('common-entrance.index'))
            ->assertSeeInOrder(['Registered', '1', '/ 2', 'Results in', '1', 'Secondary school found', '1']);
    }

    public function test_clearing_a_saved_record_keeps_the_row_but_empties_it(): void
    {
        $section = $this->classNamed('Primary 6');
        $promotion = $this->enrol($section);
        CommonEntranceCandidate::factory()->placed()->create(['student_id' => $promotion->student_id, 'session_id' => $this->session->id]);

        $this->as(User::factory()->create(['role' => 'admin']))
            ->post(route('common-entrance.update'), [
                'class_id' => $section->class_id,
                'candidates' => [$promotion->student_id => ['registration_number' => '', 'score' => '', 'admitted_school' => '']],
            ]);

        $this->assertDatabaseHas('common_entrance_candidates', [
            'student_id' => $promotion->student_id,
            'registration_number' => null,
            'score' => null,
            'admitted_school' => null,
        ]);
    }

    public function test_pupils_outside_the_class_are_ignored(): void
    {
        $section = $this->classNamed('Primary 6');
        $outsider = $this->enrol($this->classNamed('Primary 5'));

        $this->as(User::factory()->create(['role' => 'admin']))
            ->post(route('common-entrance.update'), [
                'class_id' => $section->class_id,
                'candidates' => [$outsider->student_id => ['registration_number' => 'NCEE/1']],
            ]);

        $this->assertDatabaseCount('common_entrance_candidates', 0);
    }

    public function test_score_must_be_a_sensible_number(): void
    {
        $section = $this->classNamed('Primary 6');
        $promotion = $this->enrol($section);

        $this->as(User::factory()->create(['role' => 'admin']))
            ->post(route('common-entrance.update'), [
                'class_id' => $section->class_id,
                'candidates' => [$promotion->student_id => ['score' => 'two hundred']],
            ])
            ->assertSessionHasErrors('candidates.'.$promotion->student_id.'.score');
    }

    public function test_class_teacher_manages_only_their_own_class(): void
    {
        $mine = $this->classNamed('Primary 6');
        $other = $this->classNamed('Primary 5');
        $pupil = $this->enrol($mine);
        $teacher = User::factory()->create(['role' => 'teacher']);
        AssignedTeacher::factory()->create(['teacher_id' => $teacher->id, 'class_id' => $mine->class_id, 'session_id' => $this->session->id]);

        $this->as($teacher)->get(route('common-entrance.index'))->assertOk();

        $this->as($teacher)
            ->post(route('common-entrance.update'), ['class_id' => $other->class_id, 'candidates' => []])
            ->assertForbidden();

        $this->as($teacher)
            ->post(route('common-entrance.update'), ['class_id' => $mine->class_id, 'candidates' => [$pupil->student_id => ['registration_number' => 'NCEE/7']]])
            ->assertRedirect();

        $this->assertDatabaseHas('common_entrance_candidates', ['student_id' => $pupil->student_id, 'registration_number' => 'NCEE/7']);
    }

    public function test_teacher_without_a_class_and_students_are_refused(): void
    {
        $this->classNamed('Primary 6');

        $this->as(User::factory()->create(['role' => 'teacher']))->get(route('common-entrance.index'))->assertForbidden();
        $this->as(User::factory()->create(['role' => 'student']))->get(route('common-entrance.index'))->assertForbidden();
        $this->as(User::factory()->create(['role' => User::ROLE_SECRETARY]))->get(route('common-entrance.index'))->assertForbidden();
    }

    public function test_dashboard_shows_common_entrance_progress_and_shortcuts(): void
    {
        $section = $this->classNamed('Primary 6');
        $promotion = $this->enrol($section);
        $this->enrol($section);
        CommonEntranceCandidate::factory()->create(['student_id' => $promotion->student_id, 'session_id' => $this->session->id]);

        $this->as(User::factory()->create(['role' => 'admin']))
            ->get(route('dashboard'))
            ->assertSee('Message parents')
            ->assertSee(route('reports.census'))
            ->assertSee('Common Entrance &middot; Primary 6', false)
            ->assertSee('1 of 2 registered');
    }
}
