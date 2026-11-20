<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\TeacherAccountCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TeacherCreationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'create users', 'create exams', 'view exams', 'create exams rule', 'view exams rule',
            'edit exams rule', 'delete exams rule', 'take attendances', 'view attendances',
            'create assignments', 'view assignments', 'save marks', 'view users', 'view routines',
            'view syllabi', 'view events', 'view notices',
        ] as $permission) {
            Permission::findOrCreate($permission);
        }

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->admin->givePermissionTo('create users');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function teacherPayload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Tina',
            'last_name' => 'Teacher',
            'email' => 'tina@example.com',
            'password' => 'secret-pass-1',
            'gender' => 'Female',
            'nationality' => 'Nigerian',
            'phone' => '08000000000',
            'address' => '1 School Lane',
            'address2' => 'Block A',
            'city' => 'Lagos',
            'zip' => '100001',
        ], $overrides);
    }

    public function test_creating_a_teacher_emails_their_login_credentials(): void
    {
        Notification::fake();

        $response = $this->actingAs($this->admin)
            ->from(route('teacher.create'))
            ->post(route('teacher.store'), $this->teacherPayload());

        $response->assertRedirect(route('teacher.create'));
        $response->assertSessionHas('status', 'Teacher created and login credentials emailed to tina@example.com.');

        $teacher = User::where('email', 'tina@example.com')->firstOrFail();
        $this->assertSame('teacher', $teacher->role);
        $this->assertTrue(Hash::check('secret-pass-1', $teacher->password));

        Notification::assertSentTo($teacher, TeacherAccountCreated::class, function (TeacherAccountCreated $notification) use ($teacher): bool {
            $mail = $notification->toMail($teacher);

            return $notification->plainPassword === 'secret-pass-1'
                && in_array('**Email:** tina@example.com', $mail->introLines, true)
                && in_array('**Password:** secret-pass-1', $mail->introLines, true)
                && $mail->actionUrl === route('login');
        });
    }

    public function test_a_password_is_generated_when_left_blank(): void
    {
        Notification::fake();

        $this->actingAs($this->admin)
            ->post(route('teacher.store'), $this->teacherPayload(['password' => '']))
            ->assertSessionHasNoErrors();

        $teacher = User::where('email', 'tina@example.com')->firstOrFail();

        Notification::assertSentTo($teacher, TeacherAccountCreated::class, function (TeacherAccountCreated $notification) use ($teacher): bool {
            return strlen($notification->plainPassword) === 12
                && Hash::check($notification->plainPassword, $teacher->password);
        });
    }

    public function test_teacher_is_still_created_when_the_email_fails(): void
    {
        Notification::shouldReceive('send')->andThrow(new \RuntimeException('SMTP down'));

        $response = $this->actingAs($this->admin)
            ->post(route('teacher.store'), $this->teacherPayload());

        $response->assertSessionHas('status', 'Teacher created, but the login credentials email could not be sent. Please share the login details with the teacher manually.');
        $this->assertDatabaseHas('users', ['email' => 'tina@example.com', 'role' => 'teacher']);
    }

    public function test_short_password_is_rejected_and_nothing_is_sent(): void
    {
        Notification::fake();

        $this->actingAs($this->admin)
            ->post(route('teacher.store'), $this->teacherPayload(['password' => 'short']))
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'tina@example.com']);
        Notification::assertNothingSent();
    }

    public function test_user_without_permission_cannot_create_a_teacher(): void
    {
        Notification::fake();
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)
            ->post(route('teacher.store'), $this->teacherPayload())
            ->assertForbidden();

        Notification::assertNothingSent();
    }
}
