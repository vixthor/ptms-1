<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\PasswordResetByAdmin;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordManagementTest extends TestCase
{
    use RefreshDatabase;

    // Self-service change password

    public function test_user_can_change_their_own_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password-1')]);

        $this->actingAs($user)
            ->from(route('password.edit'))
            ->post(route('password.update'), [
                'old_password' => 'old-password-1',
                'new_password' => 'new-password-1',
                'new_password_confirmation' => 'new-password-1',
            ])
            ->assertRedirect(route('password.edit'))
            ->assertSessionHas('status', 'Password changed successfully.');

        $this->assertTrue(Hash::check('new-password-1', $user->fresh()->password));
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password-1')]);

        $this->actingAs($user)
            ->post(route('password.update'), [
                'old_password' => 'not-my-password',
                'new_password' => 'new-password-1',
                'new_password_confirmation' => 'new-password-1',
            ])
            ->assertSessionHasErrors('old_password')
            ->assertSessionMissing('status');

        $this->assertTrue(Hash::check('old-password-1', $user->fresh()->password));
    }

    public function test_new_password_must_be_confirmed_and_long_enough(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password-1')]);

        $this->actingAs($user)
            ->post(route('password.update'), [
                'old_password' => 'old-password-1',
                'new_password' => 'short',
                'new_password_confirmation' => 'different',
            ])
            ->assertSessionHasErrors('new_password');

        $this->assertTrue(Hash::check('old-password-1', $user->fresh()->password));
    }

    public function test_guest_cannot_change_password(): void
    {
        $this->post(route('password.update'), [])->assertRedirect(route('login'));
    }

    // Admin reset of another user's password

    public function test_admin_can_reset_a_teachers_password_and_it_is_emailed(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($admin)
            ->post(route('users.password.update', $teacher), [
                'password' => 'brand-new-pass',
                'password_confirmation' => 'brand-new-pass',
            ])
            ->assertSessionHas('status', 'Password reset and new login details emailed to '.$teacher->email.'.');

        $this->assertTrue(Hash::check('brand-new-pass', $teacher->fresh()->password));
        Notification::assertSentTo($teacher, PasswordResetByAdmin::class, fn (PasswordResetByAdmin $notification): bool => $notification->plainPassword === 'brand-new-pass');
    }

    public function test_admin_reset_generates_a_password_when_left_blank(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($admin)
            ->post(route('users.password.update', $student), ['password' => '', 'password_confirmation' => ''])
            ->assertSessionHasNoErrors();

        Notification::assertSentTo($student, PasswordResetByAdmin::class, function (PasswordResetByAdmin $notification) use ($student): bool {
            return strlen($notification->plainPassword) === 12
                && Hash::check($notification->plainPassword, $student->fresh()->password);
        });
    }

    public function test_admin_sees_new_password_when_the_email_fails(): void
    {
        Notification::shouldReceive('send')->andThrow(new \RuntimeException('SMTP down'));
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($admin)
            ->post(route('users.password.update', $teacher), [
                'password' => 'brand-new-pass',
                'password_confirmation' => 'brand-new-pass',
            ])
            ->assertSessionHas('error', 'Password was reset, but the email could not be sent. New password: brand-new-pass');

        $this->assertTrue(Hash::check('brand-new-pass', $teacher->fresh()->password));
    }

    public function test_secretary_can_reset_teachers_but_not_staff(): void
    {
        Notification::fake();
        $secretary = User::factory()->create(['role' => User::ROLE_SECRETARY]);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $headteacher = User::factory()->create(['role' => User::ROLE_HEADTEACHER]);

        $this->actingAs($secretary)
            ->post(route('users.password.update', $teacher), [])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        $this->actingAs($secretary)
            ->post(route('users.password.update', $headteacher), [])
            ->assertForbidden();
    }

    public function test_reset_card_is_shown_on_the_teacher_and_staff_edit_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $headteacher = User::factory()->create(['role' => User::ROLE_HEADTEACHER]);

        $this->actingAs($admin)
            ->get(route('teacher.edit', $teacher->id))
            ->assertOk()
            ->assertSee(route('users.password.update', $teacher));

        $this->actingAs($admin)
            ->get(route('staff.edit', $headteacher->id))
            ->assertOk()
            ->assertSee(route('users.password.update', $headteacher));
    }

    public function test_teacher_cannot_reset_another_users_password(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($teacher)
            ->post(route('users.password.update', $student), [])
            ->assertForbidden();
    }

    public function test_admin_cannot_reset_their_own_password_through_admin_reset(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('users.password.update', $admin), [])
            ->assertForbidden();
    }

    public function test_mismatched_admin_reset_password_is_rejected(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher', 'password' => Hash::make('original-pass')]);

        $this->actingAs($admin)
            ->post(route('users.password.update', $teacher), [
                'password' => 'brand-new-pass',
                'password_confirmation' => 'something-else',
            ])
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('original-pass', $teacher->fresh()->password));
        Notification::assertNothingSent();
    }

    // Forgot password

    public function test_forgot_password_page_renders_and_is_linked_from_login(): void
    {
        $this->get(route('password.request'))->assertOk()->assertSee('Send reset link');
        $this->get(route('login'))->assertOk()->assertSee(route('password.request'));
    }

    public function test_reset_link_is_emailed_to_existing_user(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_unknown_email_gets_the_same_response_and_no_email(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => 'nobody@example.com'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'If an account exists for that email, a password reset link has been sent.');

        Notification::assertNothingSent();
    }

    public function test_user_can_reset_password_with_a_valid_token(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user): bool {
            $this->get(route('password.reset', ['token' => $notification->token, 'email' => $user->email]))
                ->assertOk()
                ->assertSee($user->email);

            $this->post(route('password.store'), [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'reset-pass-123',
                'password_confirmation' => 'reset-pass-123',
            ])->assertRedirect(route('login'))->assertSessionHas('status');

            return true;
        });

        $this->assertTrue(Hash::check('reset-pass-123', $user->fresh()->password));
    }

    public function test_invalid_token_does_not_reset_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('original-pass')]);

        $this->post(route('password.store'), [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'reset-pass-123',
            'password_confirmation' => 'reset-pass-123',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('original-pass', $user->fresh()->password));
    }

    public function test_reset_link_requests_are_throttled_per_user(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email]);
        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHasErrors('email');

        $this->assertSame(Password::RESET_THROTTLED, Password::sendResetLink(['email' => $user->email]));
    }
}
