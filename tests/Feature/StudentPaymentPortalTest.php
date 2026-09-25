<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\SchoolSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPaymentPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_view_their_own_payment_history(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);

        Payment::factory()->create(['student_id' => $student->id, 'amount' => 42, 'status' => Payment::STATUS_SUCCESS]);
        Payment::factory()->create(['student_id' => $otherStudent->id, 'amount' => 999, 'status' => Payment::STATUS_SUCCESS]);

        $response = $this->actingAs($student)->get(route('student.payments.list'));

        $response->assertOk();
        $response->assertSee('42.00');
        $response->assertDontSee('999.00');
    }

    public function test_non_students_cannot_access_the_student_payment_portal(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($admin)->get(route('student.payments.list'))->assertForbidden();
        $this->actingAs($teacher)->get(route('student.payments.list'))->assertForbidden();
    }

    public function test_initializing_a_payment_without_paystack_configured_creates_no_record(): void
    {
        config(['services.paystack.secret_key' => null]);

        $student = User::factory()->create(['role' => 'student']);
        SchoolSession::factory()->create();

        $response = $this->actingAs($student)->post(route('student.payments.initialize'), [
            'category' => 'Tuition',
            'amount' => 100,
            'paid_for' => 'Term 1 Tuition',
        ]);

        $response->assertRedirect(route('student.payments.create'));
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_initializing_a_payment_requires_valid_data(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->post(route('student.payments.initialize'), []);

        $response->assertSessionHasErrors(['category', 'amount', 'paid_for']);
    }

    public function test_callback_rejects_a_reference_that_does_not_belong_to_the_student(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);

        $payment = Payment::factory()->create([
            'student_id' => $otherStudent->id,
            'status' => Payment::STATUS_PENDING,
            'reference' => 'ptms_someone_elses_reference',
        ]);

        $response = $this->actingAs($student)->get(route('student.payments.callback', [
            'reference' => 'ptms_someone_elses_reference',
        ]));

        $response->assertRedirect(route('student.payments.list'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => Payment::STATUS_PENDING,
        ]);
    }
}
