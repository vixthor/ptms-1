<?php

namespace App\Http\Controllers;

use App\Models\ExtracurricularActivity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ExtracurricularActivityController extends Controller
{
    public function __construct()
    {
        abort_unless(Auth::check() && Auth::user()->canManageWebsite(), 403);
    }

    public function index(): View
    {
        $activities = ExtracurricularActivity::orderBy('sort_order')->orderBy('name')->get();

        return view('website_admin.extracurricular_activities.index', ['activities' => $activities]);
    }

    public function create(): View
    {
        return view('website_admin.extracurricular_activities.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validate = $this->validatePayload($request);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        ExtracurricularActivity::create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $this->storeImage($request),
            'sort_order' => $request->sort_order ?? 0,
            'published' => $request->boolean('published', true),
        ]);

        return redirect()->route('website-admin.extracurricular-activities.index')->with('status', 'Activity added successfully.');
    }

    public function edit(ExtracurricularActivity $extracurricularActivity): View
    {
        return view('website_admin.extracurricular_activities.edit', ['activity' => $extracurricularActivity]);
    }

    public function update(Request $request, ExtracurricularActivity $extracurricularActivity): RedirectResponse
    {
        $validate = $this->validatePayload($request);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $extracurricularActivity->update([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $this->storeImage($request) ?? $extracurricularActivity->image,
            'sort_order' => $request->sort_order ?? 0,
            'published' => $request->boolean('published'),
        ]);

        return redirect()->route('website-admin.extracurricular-activities.index')->with('status', 'Activity updated successfully.');
    }

    public function destroy(ExtracurricularActivity $extracurricularActivity): RedirectResponse
    {
        $extracurricularActivity->delete();

        return redirect()->route('website-admin.extracurricular-activities.index')->with('status', 'Activity deleted.');
    }

    protected function validatePayload(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'published' => ['nullable', 'boolean'],
        ]);
    }

    /**
     * Move an uploaded photo into the public website upload folder.
     */
    protected function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $image = $request->file('image');
        $filename = uniqid().'.'.$image->getClientOriginalExtension();
        $image->move(public_path('upload/website'), $filename);

        return 'upload/website/'.$filename;
    }
}
