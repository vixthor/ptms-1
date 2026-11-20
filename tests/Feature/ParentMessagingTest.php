<?php

namespace Tests\Feature;

use App\Models\AssignedTeacher;
use App\Models\ParentMessage;
use App\Models\Payment;
use App\Models\Promotion;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\Section;
use App\Models\StudentParentInfo;
use App\Models\User;
use App\Services\ParentMessagingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ParentMessagingTest extends TestCase
{
    use RefreshDatabase;

    private SchoolSession $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->session = SchoolSession::factory()->create();
        config(['services.termii.api_key' => null, 'services.termii.sender_id' => null]);
    }

    /**
     * @param  array<string, string>|false  $parents  false = no parent details on file
     */
    private function enrol(?Section $section = null, array|false $parents = [], array $student = []): Promotion
    {
        $section ??= Section::factory()->create([
            'class_id' => SchoolClass::factory()->create(['session_id' => $this->session->id])->id,
        ]);

        $promotion = Promotion::factory()->create([
            'student_id' => User::factory()->create(['role' => 'student'] + $student)->id,
            'section_id' => $section->id,
            'class_id' => $section->class_id,
            'session_id' => $this->session->id,
        ]);

        if ($parents !== false) {
            StudentParentInfo::factory()->create(['student_id' => $promotion->student_id] + $parents);
        }

        return $promotion;
    }

    private function send(User $user, array $payload): TestResponse
    {
        return $this->withSession(['browse_session_id' => $this->session->id])
            ->actingAs($user)
            ->post(route('parent-messages.store'), $payload + [
                'audience' => ParentMessage::AUDIENCE_SCHOOL,
                'body' => 'Dear parent, school resumes on Monday. - PTMS',
                'channel' => ParentMessage::CHANNEL_WHATSAPP,
            ]);
    }

    public function test_phone_numbers_are_normalised_to_international_format(): void
    {
        $this->assertSame('2348031234567', ParentMessagingService::normalisePhone('08031234567'));
        $this->assertSame('2348031234567', ParentMessagingService::normalisePhone('+234 803 123 4567'));
        $this->assertSame('2349031234567', ParentMessagingService::normalisePhone('9031234567'));
        $this->assertNull(ParentMessagingService::normalisePhone('01234567'));
        $this->assertNull(ParentMessagingService::normalisePhone(null));
    }

    public function test_admin_prepares_whatsapp_links_for_every_family_once(): void
    {
        $first = $this->enrol(null, ['father_phone' => '08031111111', 'mother_phone' => '08032222222'], ['first_name' => 'Ada']);
        $this->enrol($first->section, ['father_phone' => '08031111111', 'mother_phone' => 'n/a'], ['first_name' => 'Obi']);

        $response = $this->send(User::factory()->create(['role' => 'admin']), []);

        $message = ParentMessage::sole();
        $response->assertRedirect(route('parent-messages.show', $message));
        $this->assertSame(ParentMessage::STATUS_PREPARED, $message->status);
        $this->assertSame(2, $message->recipients_count);
        $this->assertContains(['phone' => '2348031111111', 'name' => 'Parent of Ada & Obi'], $message->recipients);

        $this->withSession(['browse_session_id' => $this->session->id])
            ->get(route('parent-messages.show', $message))
            ->assertOk()
            ->assertSee('Parent of Ada &amp; Obi', false)
            ->assertSee('https://wa.me/2348031111111?text=Dear%20parent%2C', false);
    }

    public function test_pupil_without_parent_details_falls_back_to_their_own_phone(): void
    {
        $this->enrol(null, false, ['phone' => '0805 555 5555', 'first_name' => 'Tobi']);

        $this->send(User::factory()->create(['role' => 'admin']), []);

        $this->assertSame([['phone' => '2348055555555', 'name' => 'Parent of Tobi']], ParentMessage::sole()->recipients);
    }

    public function test_class_audience_only_reaches_that_class(): void
    {
        $mine = $this->enrol(null, ['father_phone' => '08030000001', 'mother_phone' => '']);
        $this->enrol(null, ['father_phone' => '08030000002', 'mother_phone' => '']);

        $this->send(User::factory()->create(['role' => User::ROLE_SECRETARY]), [
            'audience' => ParentMessage::AUDIENCE_CLASS,
            'class_id' => $mine->class_id,
        ]);

        $this->assertSame(['2348030000001'], array_column(ParentMessage::sole()->recipients, 'phone'));
    }

    public function test_unpaid_tuition_audience_skips_families_who_paid(): void
    {
        $paid = $this->enrol(null, ['father_phone' => '08030000001', 'mother_phone' => '']);
        $this->enrol($paid->section, ['father_phone' => '08030000002', 'mother_phone' => '']);
        Payment::factory()->create(['student_id' => $paid->student_id, 'session_id' => $this->session->id, 'category' => 'Tuition']);

        $this->send(User::factory()->create(['role' => 'admin']), ['audience' => ParentMessage::AUDIENCE_UNPAID_TUITION]);

        $this->assertSame(['2348030000002'], array_column(ParentMessage::sole()->recipients, 'phone'));
    }

    public function test_headteacher_cannot_target_unpaid_tuition_families(): void
    {
        $this->enrol();

        $this->send(User::factory()->create(['role' => User::ROLE_HEADTEACHER]), ['audience' => ParentMessage::AUDIENCE_UNPAID_TUITION])
            ->assertForbidden();
    }

    public function test_class_teacher_can_message_only_their_own_class(): void
    {
        $mine = $this->enrol();
        $other = $this->enrol();
        $teacher = User::factory()->create(['role' => 'teacher']);
        AssignedTeacher::factory()->create(['teacher_id' => $teacher->id, 'class_id' => $mine->class_id, 'session_id' => $this->session->id]);

        $this->send($teacher, ['audience' => ParentMessage::AUDIENCE_SCHOOL])->assertForbidden();
        $this->send($teacher, ['audience' => ParentMessage::AUDIENCE_CLASS, 'class_id' => $other->class_id])->assertForbidden();
        $this->send($teacher, ['audience' => ParentMessage::AUDIENCE_CLASS, 'class_id' => $mine->class_id])->assertRedirect();

        $this->assertSame($mine->class_id, ParentMessage::sole()->class_id);
    }

    public function test_students_cannot_message_parents(): void
    {
        $promotion = $this->enrol();

        $this->withSession(['browse_session_id' => $this->session->id])
            ->actingAs($promotion->student)
            ->get(route('parent-messages.create'))
            ->assertForbidden();

        $this->send($promotion->student, [])->assertForbidden();
    }

    public function test_sms_is_unavailable_until_termii_is_configured(): void
    {
        $this->enrol();

        $this->send(User::factory()->create(['role' => 'admin']), ['channel' => ParentMessage::CHANNEL_SMS])
            ->assertSessionHasErrors('channel');

        $this->assertDatabaseCount('parent_messages', 0);
    }

    public function test_sms_is_sent_through_termii_when_configured(): void
    {
        config(['services.termii.api_key' => 'test-key', 'services.termii.sender_id' => 'PTMS']);
        Http::fake(['api.ng.termii.com/*' => Http::response(['code' => 'ok'])]);
        $this->enrol(null, ['father_phone' => '08031111111', 'mother_phone' => '08032222222']);

        $this->send(User::factory()->create(['role' => 'admin']), ['channel' => ParentMessage::CHANNEL_SMS]);

        $this->assertSame(ParentMessage::STATUS_SENT, ParentMessage::sole()->status);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://api.ng.termii.com/api/sms/send/bulk'
            && $request['to'] === ['2348031111111', '2348032222222']
            && $request['from'] === 'PTMS'
            && $request['sms'] === 'Dear parent, school resumes on Monday. - PTMS');
    }

    public function test_failed_sms_is_logged_with_the_gateway_error(): void
    {
        config(['services.termii.api_key' => 'test-key', 'services.termii.sender_id' => 'PTMS']);
        Http::fake(['api.ng.termii.com/*' => Http::response(['message' => 'Insufficient balance'], 400)]);
        $this->enrol();

        $this->send(User::factory()->create(['role' => 'admin']), ['channel' => ParentMessage::CHANNEL_SMS]);

        $message = ParentMessage::sole();
        $this->assertSame(ParentMessage::STATUS_FAILED, $message->status);
        $this->assertStringContainsString('Insufficient balance', $message->error);
    }

    public function test_group_without_any_phone_numbers_is_rejected(): void
    {
        $this->enrol(null, ['father_phone' => 'none', 'mother_phone' => ''], ['phone' => '123']);

        $this->send(User::factory()->create(['role' => 'admin']), [])->assertSessionHasErrors('audience');
        $this->assertDatabaseCount('parent_messages', 0);
    }

    public function test_message_body_is_required_and_limited_to_three_sms_pages(): void
    {
        $this->send(User::factory()->create(['role' => 'admin']), ['body' => str_repeat('a', 481)])
            ->assertSessionHasErrors('body');
    }

    public function test_compose_page_lists_recent_messages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ParentMessage::factory()->create(['session_id' => $this->session->id, 'body' => 'PTA meeting holds on Saturday']);

        $this->withSession(['browse_session_id' => $this->session->id])
            ->actingAs($admin)
            ->get(route('parent-messages.create'))
            ->assertOk()
            ->assertSee('Parents who haven&#039;t paid tuition this term', false)
            ->assertSee('PTA meeting holds on Saturday')
            ->assertSee('not set up yet');
    }

    public function test_teacher_cannot_view_another_senders_message(): void
    {
        $message = ParentMessage::factory()->create(['session_id' => $this->session->id]);

        $this->actingAs(User::factory()->create(['role' => 'teacher']))
            ->get(route('parent-messages.show', $message))
            ->assertForbidden();
    }
}
