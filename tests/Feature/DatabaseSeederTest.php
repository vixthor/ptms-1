<?php

namespace Tests\Feature;

use App\Models\AcademicSetting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_the_test_user_and_academic_setting_outside_production(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $this->assertNotNull(AcademicSetting::find(1));
    }

    public function test_it_skips_the_factory_test_user_in_production(): void
    {
        $this->app['env'] = 'production';

        Model::unguarded(fn () => $this->app->make(DatabaseSeeder::class)->run());

        $this->assertSame(0, User::count());
        $this->assertNotNull(AcademicSetting::find(1));
    }
}
