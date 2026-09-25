<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Promotion;
use App\Models\SchoolSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_record_a_payment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $session = SchoolSession::factory()->create();
        $promotion = Promotion::factory()->create(['session_id' => $session->id]);

        $response = $this->actingAs($admin)->post(route('payments.store'), [
            'student_id' => $promotion->student_id,
            'category' => 'Tuition',
            'session_id' => $session->id,
            'amount' => 150.50,
            'payment_method' => 'Cash',
            'paid_for' => 'Term 1 Tuition',
            'payment_date' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('payments.list'));
        $this->assertDatabaseHas('payments', [
            'student_id' => $promotion->student_id,
            'amount' => 150.50,
            'category' => 'Tuition',
            'status' => Payment::STATUS_SUCCESS,
            'gateway' => 'manual',
        ]);
    }

    public function test_recording_a_payment_requires_valid_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('payments.store'), [
            'category' => 'Tuition',
        ]);

        $response->assertSessionHasErrors(['student_id', 'amount', 'payment_method', 'paid_for', 'payment_date']);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_payments_list_only_totals_successful_payments(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $session = SchoolSession::factory()->create();

        Payment::factory()->create(['session_id' => $session->id, 'amount' => 100, 'status' => Payment::STATUS_SUCCESS]);
        Payment::factory()->create(['session_id' => $session->id, 'amount' => 999, 'status' => Payment::STATUS_PENDING]);

        $response = $this->withSession(['browse_session_id' => $session->id])
            ->actingAs($admin)
            ->get(route('payments.list'));

        $response->assertOk();
        // Total Collected must only sum successful payments, even though
        // pending ones still appear as rows in the ledger table.
        $response->assertSeeInOrder(['Total Collected', '100.00']);
    }

    public function test_teacher_cannot_access_payments(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)->get(route('payments.list'))->assertForbidden();
        $this->actingAs($teacher)->get(route('payments.create'))->assertForbidden();
    }

    public function test_secretary_can_manage_payments(): void
    {
        $secretary = User::factory()->create(['role' => User::ROLE_SECRETARY]);

        $this->actingAs($secretary)->get(route('payments.list'))->assertOk();
    }

    public function test_headteacher_cannot_manage_payments(): void
    {
        $headteacher = User::factory()->create(['role' => User::ROLE_HEADTEACHER]);

        $response = $this->actingAs($headteacher)->get(route('payments.list'));

        $response->assertForbidden();
    }
}
