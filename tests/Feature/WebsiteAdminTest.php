<?php

namespace Tests\Feature;

use App\Models\AcademicProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_edit_a_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('website-admin.pages.update', ['slug' => 'about']), [
            'title' => 'About Us',
            'hero_title' => 'Our story',
            'body' => '<p>Placeholder body</p>',
        ]);

        $response->assertRedirect(route('website-admin.pages.index'));
        $this->assertDatabaseHas('pages', [
            'slug' => 'about',
            'title' => 'About Us',
            'hero_title' => 'Our story',
        ]);
    }

    public function test_editing_an_unknown_slug_404s(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('website-admin.pages.edit', ['slug' => 'not-a-real-page']))->assertNotFound();
    }

    public function test_admin_can_create_and_delete_an_academic_program(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('website-admin.academic-programs.store'), [
            'name' => 'Reception',
            'description' => 'A placeholder program.',
        ])->assertRedirect(route('website-admin.academic-programs.index'));

        $program = AcademicProgram::where('name', 'Reception')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('website-admin.academic-programs.destroy', ['academicProgram' => $program->id]))
            ->assertRedirect(route('website-admin.academic-programs.index'));

        $this->assertDatabaseMissing('academic_programs', ['id' => $program->id]);
    }

    public function test_teacher_cannot_access_website_admin(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)->get(route('website-admin.pages.index'))->assertForbidden();
        $this->actingAs($teacher)->get(route('website-admin.academic-programs.index'))->assertForbidden();
        $this->actingAs($teacher)->get(route('website-admin.settings.edit'))->assertForbidden();
    }

    public function test_headteacher_cannot_manage_the_website(): void
    {
        $headteacher = User::factory()->create(['role' => User::ROLE_HEADTEACHER]);

        $this->actingAs($headteacher)->get(route('website-admin.pages.index'))->assertForbidden();
    }

    public function test_proprietor_can_manage_the_website(): void
    {
        $proprietor = User::factory()->create(['role' => User::ROLE_PROPRIETOR]);

        $this->actingAs($proprietor)->get(route('website-admin.pages.index'))->assertOk();
    }

    public function test_admin_can_update_site_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('website-admin.settings.update'), [
            'site_name' => 'Test School',
            'phone' => '0123456789',
        ]);

        $response->assertRedirect(route('website-admin.settings.edit'));
        $this->assertDatabaseHas('site_settings', ['key' => 'site_name', 'value' => 'Test School']);
        $this->assertDatabaseHas('site_settings', ['key' => 'phone', 'value' => '0123456789']);
    }
}
