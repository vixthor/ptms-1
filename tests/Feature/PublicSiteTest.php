<?php

namespace Tests\Feature;

use App\Models\Notice;
use App\Models\Page;
use App\Models\SchoolSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_load_without_authentication(): void
    {
        $this->get(route('public.home'))->assertOk();
        $this->get(route('public.about'))->assertOk();
        $this->get(route('public.academics'))->assertOk();
        $this->get(route('public.admissions'))->assertOk();
        $this->get(route('public.contact'))->assertOk();
        $this->get(route('public.news'))->assertOk();
        $this->get(route('public.gallery'))->assertOk();
    }

    public function test_login_form_is_now_at_slash_login(): void
    {
        $this->get('/login')->assertOk()->assertSee('Sign in');
    }

    public function test_news_page_only_shows_public_notices(): void
    {
        $session = SchoolSession::factory()->create();

        $public = Notice::create(['notice' => 'This is a public announcement', 'session_id' => $session->id, 'is_public' => true]);
        Notice::create(['notice' => 'This is an internal-only notice', 'session_id' => $session->id, 'is_public' => false]);

        $response = $this->get(route('public.news'));

        $response->assertOk();
        $response->assertSee('This is a public announcement');
        $response->assertDontSee('This is an internal-only notice');
    }

    public function test_viewing_a_non_public_notice_directly_404s(): void
    {
        $session = SchoolSession::factory()->create();
        $notice = Notice::create(['notice' => 'Internal only', 'session_id' => $session->id, 'is_public' => false]);

        $this->get(route('public.news.show', ['notice' => $notice->id]))->assertNotFound();
    }

    public function test_viewing_a_public_notice_works(): void
    {
        $session = SchoolSession::factory()->create();
        $notice = Notice::create(['notice' => 'Come to our open day', 'session_id' => $session->id, 'is_public' => true]);

        $this->get(route('public.news.show', ['notice' => $notice->id]))
            ->assertOk()
            ->assertSee('Come to our open day');
    }

    public function test_admin_edited_page_content_appears_on_the_public_site(): void
    {
        Page::updateOrCreate(['slug' => 'about'], [
            'title' => 'About',
            'hero_title' => 'A very distinctive test heading',
            'body' => '<p>Distinctive body text for the test.</p>',
        ]);

        $response = $this->get(route('public.about'));

        $response->assertOk();
        $response->assertSee('A very distinctive test heading');
        $response->assertSee('Distinctive body text for the test.', false);
    }
}
