<?php

namespace App\Http\Controllers;

use App\Models\AcademicProgram;
use App\Models\AdmissionStep;
use App\Models\FeeItem;
use App\Models\GalleryPhoto;
use App\Models\Notice;
use App\Models\Page;

class PublicSiteController extends Controller
{
    protected function page(string $slug): Page
    {
        return Page::firstOrNew(['slug' => $slug], ['title' => ucfirst($slug)]);
    }

    public function home()
    {
        return view('public.home', [
            'page' => $this->page('home'),
            'programs' => AcademicProgram::where('published', true)->orderBy('sort_order')->take(4)->get(),
            'newsItems' => Notice::where('is_public', true)->orderByDesc('id')->take(3)->get(),
        ]);
    }

    public function about()
    {
        return view('public.about', ['page' => $this->page('about')]);
    }

    public function academics()
    {
        return view('public.academics', [
            'programs' => AcademicProgram::where('published', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function admissions()
    {
        return view('public.admissions', [
            'steps' => AdmissionStep::orderBy('sort_order')->get(),
            'fees' => FeeItem::orderBy('sort_order')->get(),
        ]);
    }

    public function contact()
    {
        return view('public.contact', ['page' => $this->page('contact')]);
    }

    public function news()
    {
        return view('public.news', [
            'newsItems' => Notice::where('is_public', true)->orderByDesc('id')->paginate(6),
        ]);
    }

    public function newsShow(Notice $notice)
    {
        abort_unless($notice->is_public, 404);

        return view('public.news-show', ['notice' => $notice]);
    }

    public function gallery()
    {
        return view('public.gallery', [
            'photos' => GalleryPhoto::orderBy('sort_order')->orderByDesc('id')->get(),
        ]);
    }
}
