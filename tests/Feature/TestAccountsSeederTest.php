<?php

namespace Tests\Feature;

use App\Models\AssignedTeacher;
use App\Models\Promotion;
use App\Models\StudentParentInfo;
use App\Models\User;
use Database\Seeders\TestAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestAccountsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_a_class_teacher_and_a_student_with_parent_details(): void
    {
        $this->seed(TestAccountsSeeder::class);

        $teacher = User::where('email', TestAccountsSeeder::TEACHER_EMAIL)->firstOrFail();
        $student = User::where('email', TestAccountsSeeder::STUDENT_EMAIL)->firstOrFail();
        $promotion = Promotion::where('student_id', $student->id)->firstOrFail();

        $this->assertSame('teacher', $teacher->role);
        $this->assertTrue($teacher->can('take attendances'));
        $this->assertSame('student', $student->role);
        $this->assertTrue(StudentParentInfo::where('student_id', $student->id)->exists());
        $this->assertTrue(AssignedTeacher::where('teacher_id', $teacher->id)->where('class_id', $promotion->class_id)->exists());
    }

    public function test_seeded_accounts_can_log_in(): void
    {
        $this->seed(TestAccountsSeeder::class);

        foreach ([TestAccountsSeeder::TEACHER_EMAIL, TestAccountsSeeder::STUDENT_EMAIL] as $email) {
            $this->post(route('login.process'), ['email' => $email, 'password' => TestAccountsSeeder::PASSWORD]);
            $this->assertAuthenticatedAs(User::where('email', $email)->first());
            auth()->logout();
        }
    }

    public function test_it_can_be_run_twice_without_duplicates(): void
    {
        $this->seed(TestAccountsSeeder::class);
        $this->seed(TestAccountsSeeder::class);

        $this->assertSame(2, User::count());
        $this->assertSame(1, Promotion::count());
        $this->assertSame(1, AssignedTeacher::count());
    }

    public function test_it_does_nothing_in_production(): void
    {
        $this->app['env'] = 'production';

        $this->app->make(TestAccountsSeeder::class)->run();

        $this->assertSame(0, User::count());
    }
}
