<?php

namespace App\Http\Controllers;

use App\Models\AdmissionStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdmissionStepController extends Controller
{
    public function __construct()
    {
        abort_unless(Auth::check() && Auth::user()->canManageWebsite(), 403);
    }

    public function index()
    {
        $steps = AdmissionStep::orderBy('sort_order')->get();

        return view('website_admin.admission_steps.index', ['steps' => $steps]);
    }

    protected function validatePayload(Request $request)
    {
        return Validator::make($request->all(), [
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
        ]);
    }

    public function store(Request $request)
    {
        $validate = $this->validatePayload($request);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        AdmissionStep::create([
            'title' => $request->title,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('website-admin.admission-steps.index')->with('status', 'Step added successfully.');
    }

    public function update(Request $request, AdmissionStep $admissionStep)
    {
        $validate = $this->validatePayload($request);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $admissionStep->update($validate->validated());

        return redirect()->route('website-admin.admission-steps.index')->with('status', 'Step updated successfully.');
    }

    public function destroy(AdmissionStep $admissionStep)
    {
        $admissionStep->delete();

        return redirect()->route('website-admin.admission-steps.index')->with('status', 'Step deleted.');
    }
}
