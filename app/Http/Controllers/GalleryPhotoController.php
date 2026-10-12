<?php

namespace App\Http\Controllers;

use App\Models\GalleryPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GalleryPhotoController extends Controller
{
    public function __construct()
    {
        abort_unless(Auth::check() && Auth::user()->canManageWebsite(), 403);
    }

    public function index()
    {
        $photos = GalleryPhoto::orderBy('sort_order')->orderByDesc('id')->get();

        return view('website_admin.gallery_photos.index', ['photos' => $photos]);
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'image' => ['required', 'image', 'max:4096'],
            'caption' => ['nullable', 'string'],
            'category' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $image = $request->file('image');
        $filename = uniqid().'.'.$image->getClientOriginalExtension();
        $image->move(public_path().'/upload/website/gallery/', $filename);

        GalleryPhoto::create([
            'image' => 'upload/website/gallery/'.$filename,
            'caption' => $request->caption,
            'category' => $request->category,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('website-admin.gallery-photos.index')->with('status', 'Photo added successfully.');
    }

    public function destroy(GalleryPhoto $galleryPhoto)
    {
        $galleryPhoto->delete();

        return redirect()->route('website-admin.gallery-photos.index')->with('status', 'Photo deleted.');
    }
}
