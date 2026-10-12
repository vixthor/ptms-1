<?php

namespace App\Http\Controllers;

use App\Models\AcademicProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AcademicProgramController extends Controller
{
    public function __construct()
    {
        abort_unless(Auth::check() && Auth::user()->canManageWebsite(), 403);
    }

    public function index()
    {
        $programs = AcademicProgram::orderBy('sort_order')->orderBy('name')->get();

        return view('website_admin.academic_programs.index', ['programs' => $programs]);
    }

    public function create()
    {
        return view('website_admin.academic_programs.create');
    }

    protected function validatePayload(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);
    }

    public function store(Request $request)
    {
        $validate = $this->validatePayload($request);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = uniqid().'.'.$image->getClientOriginalExtension();
            $image->move(public_path().'/upload/website/', $filename);
            $imagePath = 'upload/website/'.$filename;
        }

        AcademicProgram::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name).'-'.uniqid(),
            'description' => $request->description,
            'image' => $imagePath,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('website-admin.academic-programs.index')->with('status', 'Program added successfully.');
    }

    public function edit(AcademicProgram $academicProgram)
    {
        return view('website_admin.academic_programs.edit', ['program' => $academicProgram]);
    }

    public function update(Request $request, AcademicProgram $academicProgram)
    {
        $validate = $this->validatePayload($request);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $imagePath = $academicProgram->image;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = uniqid().'.'.$image->getClientOriginalExtension();
            $image->move(public_path().'/upload/website/', $filename);
            $imagePath = 'upload/website/'.$filename;
        }

        $academicProgram->update([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $imagePath,
            'sort_order' => $request->sort_order ?? 0,
            'published' => $request->boolean('published', true),
        ]);

        return redirect()->route('website-admin.academic-programs.index')->with('status', 'Program updated successfully.');
    }

    public function destroy(AcademicProgram $academicProgram)
    {
        $academicProgram->delete();

        return redirect()->route('website-admin.academic-programs.index')->with('status', 'Program deleted.');
    }
}
