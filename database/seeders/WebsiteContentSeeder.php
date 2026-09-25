<?php

namespace Database\Seeders;

use App\Models\AcademicProgram;
use App\Models\AdmissionStep;
use App\Models\FeeItem;
use App\Models\GalleryPhoto;
use App\Models\Page;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WebsiteContentSeeder extends Seeder
{
    /**
     * Placeholder content for the public marketing site, so it's viewable
     * immediately after install. Meant to be replaced through the Website
     * admin screens, not hand-edited here.
     */
    public function run(): void
    {
        Page::updateOrCreate(['slug' => 'home'], [
            'title' => 'Home',
            'hero_title' => "Where every child's journey begins with curiosity.",
            'hero_subtitle' => 'A warm, structured learning environment from Creche through Primary — built around how children actually grow.',
            'body' => '<p>We believe learning happens best in a place that feels safe, curious, and a little bit fun. Our teachers, classrooms, and curriculum are all designed around that simple idea.</p>',
            'meta_description' => 'A warm, structured learning environment from Creche through Primary.',
            'published' => true,
        ]);

        Page::updateOrCreate(['slug' => 'about'], [
            'title' => 'About',
            'hero_title' => 'Our story, our mission, our people.',
            'hero_subtitle' => 'Getting to know the community behind the school.',
            'body' => '<p>Founded with a simple mission: give every child a place to grow academically, socially, and emotionally. Our staff bring years of early-years and primary teaching experience, and our classrooms are built to be explored, not just sat in.</p><p>We keep class sizes small and our door open — parents are part of this community, not just visitors to it.</p>',
            'meta_description' => 'Our story, mission, and the people behind the school.',
            'published' => true,
        ]);

        Page::updateOrCreate(['slug' => 'contact'], [
            'title' => 'Contact',
            'hero_title' => "We'd love to hear from you.",
            'hero_subtitle' => 'Questions about admissions, a tour, or anything else — reach out any time.',
            'body' => '<p>Our office is open Monday to Friday. Book a visit and we\'ll walk you through the classrooms, meet the teachers, and answer any questions you have.</p>',
            'meta_description' => 'Get in touch with our school.',
            'published' => true,
        ]);

        $programs = [
            ['name' => 'Creche', 'description' => 'A safe, nurturing space for our youngest learners to explore, play, and take their first steps toward independence.'],
            ['name' => 'Nursery', 'description' => 'Hands-on exploration across language, numeracy, and creative play, building the foundations for structured learning.'],
            ['name' => 'Playgroup', 'description' => 'A gentle introduction to group learning — sharing, listening, and working alongside other children.'],
            ['name' => 'Primary', 'description' => 'A full academic curriculum across core subjects, paired with clubs, sports, and creative activities.'],
        ];
        foreach ($programs as $i => $program) {
            AcademicProgram::updateOrCreate(
                ['slug' => Str::slug($program['name'])],
                array_merge($program, ['sort_order' => $i, 'published' => true])
            );
        }

        $steps = [
            ['title' => 'Submit an inquiry', 'description' => 'Fill out our contact form or give us a call to let us know you\'re interested.'],
            ['title' => 'Book a tour', 'description' => 'Visit the school, meet the teachers, and see the classrooms in person.'],
            ['title' => 'Complete your application', 'description' => 'Fill in the enrollment form and submit any required documents.'],
            ['title' => 'Welcome to the school', 'description' => 'Once accepted, we\'ll help you get settled in ahead of your child\'s first day.'],
        ];
        foreach ($steps as $i => $step) {
            AdmissionStep::updateOrCreate(
                ['title' => $step['title']],
                array_merge($step, ['sort_order' => $i])
            );
        }

        $fees = [
            ['program_name' => 'Creche', 'amount' => 120, 'frequency' => 'Termly'],
            ['program_name' => 'Nursery', 'amount' => 150, 'frequency' => 'Termly'],
            ['program_name' => 'Playgroup', 'amount' => 150, 'frequency' => 'Termly'],
            ['program_name' => 'Primary', 'amount' => 220, 'frequency' => 'Termly', 'note' => 'Includes books and uniform'],
        ];
        foreach ($fees as $i => $fee) {
            FeeItem::updateOrCreate(
                ['program_name' => $fee['program_name'], 'frequency' => $fee['frequency']],
                array_merge($fee, ['sort_order' => $i])
            );
        }

        $photos = [
            ['image' => 'sikhi/sikhi1.jpeg', 'caption' => 'Students in the classroom', 'category' => 'Classroom'],
            ['image' => 'sikhi/sikhi2.jpg', 'caption' => 'Campus life', 'category' => 'Campus'],
            ['image' => 'sikhi/sikhi.avif', 'caption' => 'School community', 'category' => 'Events'],
        ];
        foreach ($photos as $i => $photo) {
            GalleryPhoto::firstOrCreate(
                ['image' => $photo['image']],
                array_merge($photo, ['sort_order' => $i])
            );
        }

        $settings = [
            'site_name' => 'PTMS',
            'tagline' => 'Excellence at its peak',
            'enrollment_count' => '350',
            'staff_count' => '40',
            'clubs_count' => '8',
            'subjects_count' => '12',
            'phone' => '+44 7000 000000',
            'whatsapp' => '+44 7000 000000',
            'email' => 'info@ptms.uk',
            'address' => '1 School Lane, London',
            'facebook_url' => 'https://facebook.com',
            'twitter_url' => 'https://twitter.com',
            'instagram_url' => 'https://instagram.com',
        ];
        foreach ($settings as $key => $value) {
            SiteSetting::set($key, $value);
        }
    }
}
