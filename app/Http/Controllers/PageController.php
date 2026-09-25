<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller
{
    /**
     * These are the only pages editable through this screen — each one is a
     * fixed slug on the public site, not a freely-creatable page.
     */
    public const MANAGED_SLUGS = ['home', 'about', 'contact'];

    public function __construct()
    {
        abort_unless(Auth::check() && Auth::user()->canManageWebsite(), 403);
    }

    public function index()
    {
        $pages = collect(self::MANAGED_SLUGS)->map(
            fn ($slug) => Page::firstOrCreate(['slug' => $slug], ['title' => ucfirst($slug)])
        );

        return view('website_admin.pages.index', ['pages' => $pages]);
    }

    public function edit(string $slug)
    {
        abort_unless(in_array($slug, self::MANAGED_SLUGS), 404);

        $page = Page::firstOrCreate(['slug' => $slug], ['title' => ucfirst($slug)]);

        return view('website_admin.pages.edit', ['page' => $page]);
    }

    public function update(Request $request, string $slug)
    {
        abort_unless(in_array($slug, self::MANAGED_SLUGS), 404);

        $validate = Validator::make($request->all(), [
            'title' => ['required', 'string'],
            'hero_title' => ['nullable', 'string'],
            'hero_subtitle' => ['nullable', 'string'],
            'body' => ['nullable', 'string'],
            'meta_description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $page = Page::firstOrCreate(['slug' => $slug], ['title' => ucfirst($slug)]);

        $heroImage = $page->hero_image;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = uniqid().'.'.$image->getClientOriginalExtension();
            $image->move(public_path().'/upload/website/', $filename);
            $heroImage = 'upload/website/'.$filename;
        }

        $page->update([
            'title' => $request->title,
            'hero_title' => $request->hero_title,
            'hero_subtitle' => $request->hero_subtitle,
            'hero_image' => $heroImage,
            'body' => $request->body,
            'meta_description' => $request->meta_description,
            'published' => $request->boolean('published', true),
        ]);

        return redirect()->route('website-admin.pages.index')->with('status', 'Page updated successfully.');
    }
}
