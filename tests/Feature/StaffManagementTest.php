<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Staff designations are granted Spatie permissions on creation;
        // those permission rows must exist before syncPermissions() can
        // attach them (mirrors database/seeders/PermissionSeeder.php).
        foreach ([
            'create school sessions', 'update browse by session', 'create semesters', 'edit semesters',
            'assign teachers', 'create courses', 'view courses', 'edit courses',
            'create classes', 'view classes', 'edit classes',
            'create sections', 'view sections', 'edit sections',
            'create exams', 'view exams', 'create exams rule', 'edit exams rule', 'delete exams rule', 'view exams rule',
            'create routines', 'view routines', 'edit routines', 'delete routines',
            'view marks', 'view academic settings', 'update marks submission window',
            'create users', 'edit users', 'view users', 'promote students',
            'update attendances type', 'view attendances', 'take attendances',
            'create grading systems', 'view grading systems', 'edit grading systems', 'delete grading systems',
            'create grading systems rule', 'view grading systems rule', 'edit grading systems rule', 'delete grading systems rule',
            'create notices', 'view notices', 'edit notices', 'delete notices',
            'create events', 'view events', 'edit events', 'delete events',
            'create syllabi', 'view syllabi', 'edit syllabi', 'delete syllabi',
            'view assignments',
        ] as $permission) {
            Permission::findOrCreate($permission);
        }
    }

    private function staffPayload(array $overrides = []): array
    {
        return array_merge([
            'designation' => 'headteacher',
            'first_name' => 'Helen',
            'last_name' => 'Head',
            'email' => 'helen@example.com',
            'password' => 'password123',
            'gender' => 'Female',
            'nationality' => 'British',
            'phone' => '07000000001',
            'address' => '1 School Lane',
            'city' => 'London',
            'zip' => '00000',
        ], $overrides);
    }

    public function test_admin_can_create_a_headteacher(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('staff.store'), $this->staffPayload());

        $response->assertRedirect(route('staff.list'));
        $this->assertDatabaseHas('users', [
            'email' => 'helen@example.com',
            'role' => 'headteacher',
        ]);
    }

    public function test_creating_staff_assigns_designation_specific_permissions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('staff.store'), $this->staffPayload([
            'designation' => 'secretary',
            'email' => 'sandra@example.com',
        ]));

        $secretary = User::where('email', 'sandra@example.com')->firstOrFail();

        $this->assertTrue($secretary->hasPermissionTo('create notices'));
        $this->assertTrue($secretary->hasPermissionTo('view users'));
        $this->assertFalse($secretary->hasPermissionTo('view academic settings'));
        $this->assertFalse($secretary->hasPermissionTo('create grading systems'));
    }

    public function test_teacher_cannot_manage_staff(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)->get(route('staff.list'))->assertForbidden();
        $this->actingAs($teacher)->post(route('staff.store'), $this->staffPayload())->assertForbidden();
    }

    public function test_headteacher_cannot_manage_staff(): void
    {
        $headteacher = User::factory()->create(['role' => User::ROLE_HEADTEACHER]);

        $this->actingAs($headteacher)->get(route('staff.list'))->assertForbidden();
    }

    public function test_proprietor_can_manage_staff(): void
    {
        $proprietor = User::factory()->create(['role' => User::ROLE_PROPRIETOR]);

        $this->actingAs($proprietor)->get(route('staff.list'))->assertOk();
    }

    public function test_creating_staff_requires_valid_designation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('staff.store'), $this->staffPayload([
            'designation' => 'not-a-real-role',
        ]));

        $response->assertSessionHasErrors('designation');
        $this->assertDatabaseMissing('users', ['email' => 'helen@example.com']);
    }
}
