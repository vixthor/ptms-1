<?php

namespace Tests\Feature;

use App\Models\AcademicProgram;
use App\Models\AdmissionStep;
use App\Models\GalleryPhoto;
use App\Models\Notice;
use App\Models\Page;
use App\Models\SchoolSession;
use App\Models\SiteSetting;
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

    public function test_home_page_shows_published_programs_and_design_sections(): void
    {
        AcademicProgram::create(['name' => 'Creche', 'slug' => 'creche', 'description' => 'A distinctive creche blurb', 'sort_order' => 0, 'published' => true]);
        AcademicProgram::create(['name' => 'Hidden Program', 'slug' => 'hidden', 'description' => 'Not published', 'sort_order' => 1, 'published' => false]);

        $response = $this->get(route('public.home'));

        $response->assertOk();
        $response->assertSee('A distinctive creche blurb');
        $response->assertDontSee('Hidden Program');
        $response->assertSeeInOrder(['Learning Begins With Us', 'Start application', 'View fees', 'Meet our teachers', 'Give your child a great start']);
        $response->assertSee(route('public.admissions').'#fees', false);
        $response->assertSee('Parent login');
    }

    public function test_admissions_page_lists_steps_and_has_fees_anchor(): void
    {
        AdmissionStep::create(['title' => 'A distinctive first step', 'description' => 'Say hello', 'sort_order' => 0]);

        $response = $this->get(route('public.admissions'));

        $response->assertOk();
        $response->assertSeeInOrder(['The process', 'A distinctive first step', 'Fees &amp; term dates'], false);
        $response->assertSee('id="fees"', false);
    }

    public function test_gallery_links_each_photo_to_its_full_image_for_the_lightbox(): void
    {
        GalleryPhoto::create(['image' => 'uploads/sports-day.jpg', 'caption' => 'Sports day', 'sort_order' => 0]);

        $response = $this->get(route('public.gallery'));

        $response->assertOk();
        $response->assertSee('href="'.asset('uploads/sports-day.jpg').'"', false);
        $response->assertSee('data-caption="Sports day"', false);
        $response->assertSee('id="galleryLightbox"', false);
    }

    public function test_gallery_without_photos_shows_empty_state_and_no_lightbox(): void
    {
        $response = $this->get(route('public.gallery'));

        $response->assertOk();
        $response->assertSee('Photos will be added here soon.');
        $response->assertDontSee('id="galleryLightbox"', false);
    }

    public function test_mobile_drawer_is_an_accessible_dialog_with_every_nav_link(): void
    {
        $response = $this->get(route('public.about'));

        $response->assertOk();
        $response->assertSee('aria-controls="siteDrawer"', false);
        $response->assertSee('aria-expanded="false"', false);
        $response->assertSee('<dialog class="site-drawer" id="siteDrawer"', false);
        $response->assertSee('aria-current="page"', false);
        $response->assertSee('href="#main"', false);

        $drawer = str($response->getContent())->between('id="siteDrawer"', '</dialog>');
        foreach (['public.home', 'public.about', 'public.contact', 'public.academics', 'public.gallery'] as $route) {
            $this->assertStringContainsString('href="'.route($route).'"', (string) $drawer);
        }
    }

    public function test_home_page_shows_statistics_counter_for_configured_values(): void
    {
        SiteSetting::set('enrollment_count', '350');
        SiteSetting::set('staff_count', '40');

        $response = $this->get(route('public.home'));

        $response->assertOk();
        $response->assertSee('data-count-to="350"', false);
        $response->assertSee('data-count-to="40"', false);
        $response->assertSeeInOrder(['Students', 'Qualified staff']);
        $response->assertDontSee('Subjects offered');
    }

    public function test_home_page_hides_statistics_when_none_are_set(): void
    {
        $this->get(route('public.home'))
            ->assertOk()
            ->assertDontSee('data-count-to="', false)
            ->assertDontSee('School in numbers');
    }

    public function test_contact_button_offers_whatsapp_and_phone_when_both_are_set(): void
    {
        SiteSetting::set('whatsapp', '+234 801 234 5678');
        SiteSetting::set('phone', '+234 (0) 802 000 1111');

        $response = $this->get(route('public.about'));

        $response->assertOk();
        $response->assertSee('id="contactFab"', false);
        $response->assertSee('WhatsApp us');
        $response->assertSee('href="tel:+23408020001111"', false);
    }

    public function test_contact_button_links_directly_when_only_phone_is_set(): void
    {
        SiteSetting::set('phone', '0802 000 1111');

        $response = $this->get(route('public.home'));

        $response->assertOk();
        $response->assertSee('contact-fab-single', false);
        $response->assertSee('href="tel:08020001111"', false);
        $response->assertDontSee('id="contactFab"', false);
    }

    public function test_contact_button_is_hidden_without_phone_or_whatsapp(): void
    {
        $this->get(route('public.home'))
            ->assertOk()
            ->assertDontSee('contact-fab-toggle', false);
    }

    public function test_footer_only_lists_social_links_that_are_configured(): void
    {
        SiteSetting::set('whatsapp', '+234 801 234 5678');

        $response = $this->get(route('public.home'));

        $response->assertOk();
        $response->assertSee('https://wa.me/2348012345678', false);
        $response->assertDontSee('>Facebook</a>', false);
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
