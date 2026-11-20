<?php

namespace Tests\Feature;

use App\Models\AssignedTeacher;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Event;
use App\Models\FinalMark;
use App\Models\Payment;
use App\Models\Promotion;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\Section;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private SchoolSession $session;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-20 09:00:00');

        $this->session = SchoolSession::factory()->create();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function dashboardAs(User $user): TestResponse
    {
        return $this->withSession(['browse_session_id' => $this->session->id])
            ->actingAs($user)
            ->get(route('dashboard'));
    }

    private function runningTerm(): Semester
    {
        return Semester::factory()->create([
            'semester_name' => 'First Term',
            'start_date' => '2026-11-02',
            'end_date' => '2026-12-18',
            'session_id' => $this->session->id,
        ]);
    }

    private function enrol(?Section $section = null, array $studentAttributes = []): Promotion
    {
        $section ??= Section::factory()->create([
            'class_id' => SchoolClass::factory()->create([
                'class_name' => 'Primary '.fake()->unique()->numberBetween(1, 99),
                'session_id' => $this->session->id,
            ])->id,
        ]);

        return Promotion::factory()->create([
            'student_id' => User::factory()->create(['role' => 'student'] + $studentAttributes)->id,
            'section_id' => $section->id,
            'class_id' => $section->class_id,
            'session_id' => $this->session->id,
        ]);
    }

    private function markAttendance(Promotion $promotion, string $status, ?Carbon $on = null): void
    {
        $attendance = Attendance::create([
            'student_id' => $promotion->student_id,
            'class_id' => $promotion->class_id,
            'section_id' => $promotion->section_id,
            'session_id' => $this->session->id,
            'course_id' => 0,
            'status' => $status,
        ]);

        if ($on) {
            $attendance->created_at = $on;
            $attendance->save();
        }
    }

    private function pay(Promotion $promotion, float $amount, array $attributes = []): Payment
    {
        return Payment::factory()->create($attributes + [
            'student_id' => $promotion->student_id,
            'session_id' => $this->session->id,
            'category' => 'Tuition',
            'amount' => $amount,
            'payment_date' => '2026-11-10',
        ]);
    }

    public function test_dashboard_shows_the_running_term_week_by_week(): void
    {
        $this->runningTerm();

        $this->dashboardAs(User::factory()->create(['role' => 'admin']))
            ->assertOk()
            ->assertSee('First Term')
            ->assertSee('Week 3 of 7')
            ->assertSee('28 days left');
    }

    public function test_dashboard_shows_the_next_term_between_terms(): void
    {
        Semester::factory()->create([
            'semester_name' => 'Second Term',
            'start_date' => '2027-01-11',
            'end_date' => '2027-04-02',
            'session_id' => $this->session->id,
        ]);

        $this->dashboardAs(User::factory()->create(['role' => 'admin']))
            ->assertSee('Second Term')
            ->assertSee('starts 11 Jan 2027')
            ->assertDontSee('days left');
    }

    public function test_dashboard_prompts_to_set_up_terms_when_none_exist(): void
    {
        $this->dashboardAs(User::factory()->create(['role' => 'admin']))
            ->assertOk()
            ->assertSee('No terms set up for this session')
            ->assertSee(route('academic.setting'));
    }

    public function test_fee_summary_totals_successful_payments_in_the_running_term(): void
    {
        $this->runningTerm();
        $paid = $this->enrol();
        $unpaid = $this->enrol();

        $this->pay($paid, 50000);
        $this->pay($paid, 2500, ['category' => 'Uniform', 'payment_date' => '2026-11-20']);
        $this->pay($paid, 9999, ['payment_date' => '2026-09-15']);
        $this->pay($unpaid, 7000, ['status' => Payment::STATUS_PENDING]);

        $this->dashboardAs(User::factory()->create(['role' => 'admin']))
            ->assertOk()
            ->assertSee('Fees &middot; First Term', false)
            ->assertSeeInOrder(['Collected', '₦52,500.00', '2 payments'])
            ->assertSeeInOrder(['Collected today', '₦2,500.00'])
            ->assertSeeInOrder(['Tuition not paid', '1', 'of 2 pupils'])
            ->assertSeeInOrder(['Pending online', '1', '₦7,000.00 awaiting confirmation'])
            ->assertSeeInOrder(['Pupils yet to pay tuition', $unpaid->student->first_name])
            ->assertDontSee('₦62,499.00');
    }

    public function test_fee_summary_covers_the_whole_session_when_no_term_is_running(): void
    {
        $promotion = $this->enrol();
        $this->pay($promotion, 1000, ['payment_date' => '2026-03-01']);

        $this->dashboardAs(User::factory()->create(['role' => 'admin']))
            ->assertSee('Fees &middot; This session', false)
            ->assertSee('Every pupil has paid tuition.')
            ->assertSeeInOrder(['Collected', '₦1,000.00']);
    }

    public function test_secretary_sees_fees_and_headteacher_does_not(): void
    {
        $this->dashboardAs(User::factory()->create(['role' => User::ROLE_SECRETARY]))
            ->assertSee('Record payment');

        $this->dashboardAs(User::factory()->create(['role' => User::ROLE_HEADTEACHER]))
            ->assertSee('Attendance today')
            ->assertDontSee('Record payment');
    }

    public function test_proprietor_sees_the_school_overview_and_fees(): void
    {
        $this->dashboardAs(User::factory()->create(['role' => User::ROLE_PROPRIETOR]))
            ->assertSee('Attendance today')
            ->assertSee('Record payment');
    }

    public function test_attendance_overview_shows_which_registers_are_taken(): void
    {
        $taken = $this->enrol();
        $this->markAttendance($taken, 'on');
        $this->markAttendance($this->enrol($taken->section), 'off');
        $this->markAttendance($this->enrol($taken->section), 'on');

        $notTaken = $this->enrol();

        $this->dashboardAs(User::factory()->create(['role' => 'admin']))
            ->assertOk()
            ->assertSeeInOrder(['Present today', '67%'])
            ->assertSee('1 register not taken')
            ->assertSeeInOrder([$taken->schoolClass->class_name, '2 present', '1 absent'])
            ->assertSeeInOrder([$notTaken->schoolClass->class_name, 'Not taken']);
    }

    public function test_pupils_with_repeated_recent_absences_are_flagged(): void
    {
        $frequentAbsentee = $this->enrol(null, ['first_name' => 'Chidinma']);
        $occasionalAbsentee = $this->enrol($frequentAbsentee->section, ['first_name' => 'Babatunde']);

        foreach ([1, 2, 3] as $daysAgo) {
            $this->markAttendance($frequentAbsentee, 'off', now()->subDays($daysAgo));
        }

        $this->markAttendance($occasionalAbsentee, 'off', now()->subDays(1));
        $this->markAttendance($occasionalAbsentee, 'off', now()->subDays(2));
        $this->markAttendance($occasionalAbsentee, 'off', now()->subDays(30));

        $response = $this->dashboardAs(User::factory()->create(['role' => 'admin']))
            ->assertSeeInOrder(['Pupils missing school', 'Chidinma', '3 days']);

        $this->assertSame(1, substr_count($response->getContent(), route('student.attendance', $frequentAbsentee->student_id)));
        $this->assertStringNotContainsString(route('student.attendance', $occasionalAbsentee->student_id), $response->getContent());
    }

    public function test_class_teacher_sees_their_class_and_can_take_the_register(): void
    {
        $promotion = $this->enrol();
        $teacher = User::factory()->create(['role' => 'teacher']);
        AssignedTeacher::factory()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $promotion->class_id,
            'session_id' => $this->session->id,
        ]);

        $this->dashboardAs($teacher)
            ->assertOk()
            ->assertSee('My class today')
            ->assertSee($promotion->schoolClass->class_name)
            ->assertSee("Take today's attendance", false)
            ->assertSee(route('attendance.create.show', ['class_id' => $promotion->class_id]), false)
            ->assertDontSee('Record payment')
            ->assertDontSee('Attendance today');

        $this->markAttendance($promotion, 'off');

        $this->dashboardAs($teacher)
            ->assertSee('Update register')
            ->assertSee('0 present, 1 absent');
    }

    public function test_teacher_without_a_class_is_told_so(): void
    {
        $this->dashboardAs(User::factory()->create(['role' => 'teacher']))
            ->assertSee('You are not class teacher of any class this session');
    }

    public function test_student_sees_neither_school_overview_nor_fees(): void
    {
        $promotion = $this->enrol();

        $this->dashboardAs($promotion->student)
            ->assertOk()
            ->assertDontSee('Attendance today')
            ->assertDontSee('Record payment')
            ->assertDontSee('My class today');
    }

    public function test_gender_split_always_adds_up_to_one_hundred_percent(): void
    {
        $promotion = $this->enrol(null, ['gender' => 'Male']);
        $this->enrol($promotion->section, ['gender' => 'Female']);
        $this->enrol($promotion->section, ['gender' => 'Female']);

        $this->dashboardAs(User::factory()->create(['role' => 'admin']))
            ->assertSee('33% Boys')
            ->assertSee('67% Girls');
    }

    public function test_upcoming_events_are_listed_and_past_ones_are_not(): void
    {
        Event::create(['title' => 'Inter-house sports', 'start' => '2026-11-27 09:00:00', 'end' => '2026-11-27 15:00:00', 'session_id' => $this->session->id]);
        Event::create(['title' => 'Open day', 'start' => '2026-10-01 09:00:00', 'end' => '2026-10-01 12:00:00', 'session_id' => $this->session->id]);

        $this->dashboardAs(User::factory()->create(['role' => 'admin']))
            ->assertSeeInOrder(['Coming up', 'Inter-house sports'])
            ->assertDontSee('Open day');
    }

    private function subject(Promotion $promotion, Semester $term): Course
    {
        return Course::factory()->create([
            'class_id' => $promotion->class_id,
            'semester_id' => $term->id,
            'session_id' => $this->session->id,
        ]);
    }

    private function score(Promotion $promotion, Course $course, float $marks): void
    {
        FinalMark::factory()->create([
            'student_id' => $promotion->student_id,
            'course_id' => $course->id,
            'section_id' => $promotion->section_id,
            'final_marks' => $marks,
            'calculated_marks' => $marks,
        ]);
    }

    private function reportCard(Promotion $promotion, Semester $term, bool $commented, bool $signed): void
    {
        ReportCard::factory()->create([
            'student_id' => $promotion->student_id,
            'class_id' => $promotion->class_id,
            'section_id' => $promotion->section_id,
            'semester_id' => $term->id,
            'session_id' => $this->session->id,
            'class_teacher_comment' => $commented ? 'Hardworking pupil.' : null,
            'headteacher_signature' => $signed ? 'upload/signature/head.png' : null,
        ]);
    }

    /**
     * Five school days this term, with the given number of them absent.
     */
    private function attendanceRecord(Promotion $promotion, int $daysAbsent): void
    {
        foreach (range(0, 4) as $daysAgo) {
            $this->markAttendance($promotion, $daysAgo < $daysAbsent ? 'off' : 'on', now()->subDays($daysAgo));
        }
    }

    private function classTeacherOf(Promotion $promotion): User
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        AssignedTeacher::factory()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $promotion->class_id,
            'session_id' => $this->session->id,
        ]);

        return $teacher;
    }

    public function test_results_tracker_shows_each_step_for_the_running_term(): void
    {
        $term = $this->runningTerm();
        $first = $this->enrol();
        $second = $this->enrol($first->section);
        $maths = $this->subject($first, $term);
        $this->subject($first, $term);

        $this->score($first, $maths, 70);
        $this->score($second, $maths, 55);
        $this->reportCard($first, $term, commented: true, signed: true);
        $this->reportCard($second, $term, commented: false, signed: false);

        $this->dashboardAs(User::factory()->create(['role' => User::ROLE_HEADTEACHER]))
            ->assertOk()
            ->assertSee('Results &middot; First Term', false)
            ->assertSee(route('report.card.mass-sign.form', ['semester_id' => $term->id]), false)
            ->assertSeeInOrder([$first->schoolClass->class_name, 'Scores', '1/2', 'Comments', '1/2', 'Signed', '1/2'])
            ->assertDontSee('>Ready<', false);
    }

    public function test_class_is_ready_once_every_subject_is_scored_and_every_report_signed(): void
    {
        $term = $this->runningTerm();
        $promotion = $this->enrol();
        $this->score($promotion, $this->subject($promotion, $term), 64);
        $this->reportCard($promotion, $term, commented: true, signed: true);

        $this->dashboardAs(User::factory()->create(['role' => 'admin']))
            ->assertSeeInOrder([$promotion->schoolClass->class_name, 'Ready', 'Scores', '1/1']);
    }

    public function test_class_without_subjects_is_flagged_in_the_results_tracker(): void
    {
        $this->runningTerm();
        $promotion = $this->enrol();

        $this->dashboardAs(User::factory()->create(['role' => 'admin']))
            ->assertSeeInOrder([$promotion->schoolClass->class_name, 'No subjects set up']);
    }

    public function test_pupil_with_two_or_more_warning_signs_is_at_risk(): void
    {
        $term = $this->runningTerm();
        $struggling = $this->enrol(null, ['first_name' => 'Ifeoma']);
        $onlyLowScores = $this->enrol($struggling->section, ['first_name' => 'Kelechi']);
        $course = $this->subject($struggling, $term);

        $this->attendanceRecord($struggling, daysAbsent: 2);
        $this->score($struggling, $course, 30);
        $this->score($onlyLowScores, $course, 25);
        $this->pay($onlyLowScores, 40000);

        $response = $this->dashboardAs(User::factory()->create(['role' => 'admin']))
            ->assertSeeInOrder(['Pupils at risk', 'Ifeoma', 'Attendance 60%', 'First Term average 30%', 'Tuition unpaid']);

        $this->assertStringNotContainsString('First Term average 25%', $response->getContent());
    }

    public function test_headteacher_risk_list_ignores_fees(): void
    {
        $term = $this->runningTerm();
        $promotion = $this->enrol(null, ['first_name' => 'Oluwaseun']);
        $this->score($promotion, $this->subject($promotion, $term), 20);

        $this->dashboardAs(User::factory()->create(['role' => 'admin']))
            ->assertSeeInOrder(['Pupils at risk', 'Oluwaseun', 'Tuition unpaid']);

        $this->dashboardAs(User::factory()->create(['role' => User::ROLE_HEADTEACHER]))
            ->assertSee('Pupils at risk')
            ->assertSee('No pupils at risk')
            ->assertDontSee('Tuition unpaid');
    }

    public function test_attendance_needs_enough_marked_days_before_it_counts_as_a_warning_sign(): void
    {
        $term = $this->runningTerm();
        $promotion = $this->enrol(null, ['first_name' => 'Amaka']);
        $this->score($promotion, $this->subject($promotion, $term), 20);
        $this->pay($promotion, 40000);

        $this->markAttendance($promotion, 'off', now()->subDay());
        $this->markAttendance($promotion, 'off');

        $this->dashboardAs(User::factory()->create(['role' => 'admin']))
            ->assertSee('No pupils at risk');
    }

    public function test_secretary_sees_neither_risk_list_nor_results_tracker(): void
    {
        $this->runningTerm();
        $this->enrol();

        $this->dashboardAs(User::factory()->create(['role' => User::ROLE_SECRETARY]))
            ->assertOk()
            ->assertDontSee('Pupils at risk')
            ->assertDontSee('Sign reports');
    }

    public function test_class_teacher_sees_results_and_risk_for_their_own_class_only(): void
    {
        $term = $this->runningTerm();
        $mine = $this->enrol(null, ['first_name' => 'Chukwuemeka']);
        $other = $this->enrol(null, ['first_name' => 'Folasade']);
        $teacher = $this->classTeacherOf($mine);

        foreach ([$mine, $other] as $promotion) {
            $course = $this->subject($promotion, $term);
            $this->score($promotion, $course, 30);
            $this->attendanceRecord($promotion, daysAbsent: 3);
        }

        $response = $this->dashboardAs($teacher)
            ->assertOk()
            ->assertSeeInOrder(['Pupils at risk', 'Chukwuemeka', 'Attendance 40%'])
            ->assertSee('Results &middot; First Term', false)
            ->assertDontSee('Tuition unpaid')
            ->assertDontSee('Sign reports');

        $this->assertStringNotContainsString('Folasade', $response->getContent());
        $this->assertStringNotContainsString($other->schoolClass->class_name, $response->getContent());
    }

    public function test_student_dashboard_shows_class_attendance_fees_and_latest_result(): void
    {
        $term = $this->runningTerm();
        $promotion = $this->enrol();
        $teacher = $this->classTeacherOf($promotion);
        $this->attendanceRecord($promotion, daysAbsent: 1);
        $this->pay($promotion, 45000, ['paid_for' => 'First Term school fees']);
        $this->pay($promotion, 3000, ['status' => Payment::STATUS_PENDING]);
        $course = $this->subject($promotion, $term);
        $this->score($promotion, $course, 72);
        $this->score($promotion, $this->subject($promotion, $term), 88);
        $this->reportCard($promotion, $term, commented: true, signed: true);

        $this->dashboardAs($promotion->student)
            ->assertOk()
            ->assertSeeInOrder(['My class', $promotion->schoolClass->class_name, 'Class teacher: '.$teacher->first_name])
            ->assertSeeInOrder(['Attendance this term', '80%', 'Present 4 of 5 days'])
            ->assertSeeInOrder(['Fees paid this session', '₦45,000.00', '1 payment awaiting confirmation'])
            ->assertSeeInOrder(['Latest result', '80%', 'First Term average', '2 subjects'])
            ->assertSee('First Term school fees')
            ->assertSee('Signed by the headteacher')
            ->assertSee(route('report.card.show', ['id' => $promotion->student_id, 'semester_id' => $term->id]), false)
            ->assertSee(route('student.payments.create'))
            ->assertDontSee('Pupils at risk');
    }

    public function test_student_without_results_is_told_they_are_not_out_yet(): void
    {
        $promotion = $this->enrol();

        $this->dashboardAs($promotion->student)
            ->assertSee('No results yet')
            ->assertSee('Your report card will appear here once results are out.')
            ->assertSee('No payments this session yet');
    }
}
