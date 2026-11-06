<?php

namespace Tests\Feature;

use App\Models\ExtracurricularActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ExtracurricularActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_the_activities_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ExtracurricularActivity::factory()->create(['name' => 'Chess Club']);

        $this->actingAs($admin)
            ->get(route('website-admin.extracurricular-activities.index'))
            ->assertOk()
            ->assertSee('Chess Club');
    }

    public function test_admin_can_create_an_activity(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('website-admin.extracurricular-activities.store'), [
            'name' => 'Chess Club',
            'description' => 'Strategy and patience.',
            'sort_order' => 3,
            'published' => '1',
        ])->assertRedirect(route('website-admin.extracurricular-activities.index'));

        $this->assertDatabaseHas('extracurricular_activities', [
            'name' => 'Chess Club',
            'sort_order' => 3,
            'published' => true,
        ]);
    }

    public function test_activity_created_with_published_unchecked_is_hidden(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('website-admin.extracurricular-activities.store'), [
            'name' => 'Debate',
            'published' => '0',
        ]);

        $this->assertDatabaseHas('extracurricular_activities', ['name' => 'Debate', 'published' => false]);
    }

    public function test_activity_requires_a_name_and_a_valid_image(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('website-admin.extracurricular-activities.store'), [
                'name' => '',
                'image' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
            ])
            ->assertSessionHasErrors(['name', 'image']);

        $this->assertDatabaseCount('extracurricular_activities', 0);
    }

    public function test_admin_can_update_an_activity_and_keep_its_image(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $activity = ExtracurricularActivity::factory()->create(['image' => 'upload/website/old.jpg']);

        $this->actingAs($admin)->post(route('website-admin.extracurricular-activities.update', ['extracurricularActivity' => $activity->id]), [
            'name' => 'Renamed Activity',
            'description' => 'Updated text',
            'sort_order' => 1,
            'published' => '0',
        ])->assertRedirect(route('website-admin.extracurricular-activities.index'));

        $activity->refresh();
        $this->assertSame('Renamed Activity', $activity->name);
        $this->assertSame('upload/website/old.jpg', $activity->image);
        $this->assertFalse($activity->published);
    }

    public function test_admin_can_delete_an_activity(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $activity = ExtracurricularActivity::factory()->create();

        $this->actingAs($admin)
            ->post(route('website-admin.extracurricular-activities.destroy', ['extracurricularActivity' => $activity->id]))
            ->assertRedirect(route('website-admin.extracurricular-activities.index'));

        $this->assertDatabaseMissing('extracurricular_activities', ['id' => $activity->id]);
    }

    public function test_teacher_cannot_manage_activities(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)->get(route('website-admin.extracurricular-activities.index'))->assertForbidden();
        $this->actingAs($teacher)
            ->post(route('website-admin.extracurricular-activities.store'), ['name' => 'Sneaky'])
            ->assertForbidden();

        $this->assertDatabaseCount('extracurricular_activities', 0);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('website-admin.extracurricular-activities.index'))->assertRedirect(route('login'));
    }

    public function test_academics_page_shows_only_published_activities_in_order(): void
    {
        ExtracurricularActivity::factory()->create(['name' => 'Second Activity', 'sort_order' => 2]);
        ExtracurricularActivity::factory()->create(['name' => 'First Activity', 'sort_order' => 1]);
        ExtracurricularActivity::factory()->hidden()->create(['name' => 'Hidden Activity']);

        $this->get(route('public.academics'))
            ->assertOk()
            ->assertSee('Extra-curricular activities')
            ->assertSeeInOrder(['First Activity', 'Second Activity'])
            ->assertDontSee('Hidden Activity');
    }

    public function test_academics_page_hides_activities_section_when_none_are_published(): void
    {
        $this->get(route('public.academics'))
            ->assertOk()
            ->assertDontSee('Extra-curricular activities');
    }
}
