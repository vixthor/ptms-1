<?php

namespace App\Http\Controllers;

use App\Http\Requests\SectionStoreRequest;
use App\Interfaces\CourseInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\SectionInterface;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Traits\SchoolSession;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SectionController extends Controller
{
    use SchoolSession;

    protected $schoolSectionRepository;

    protected $schoolSessionRepository;

    protected $courseRepository;

    /**
     * Create a new Controller instance
     *
     * @return void
     */
    public function __construct(SchoolSessionInterface $schoolSessionRepository, SectionInterface $schoolSectionRepository, CourseInterface $courseRepository)
    {
        $this->schoolSectionRepository = $schoolSectionRepository;
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->courseRepository = $courseRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(SectionStoreRequest $request)
    {
        try {
            $this->schoolSectionRepository->create($request->validated());

            return back()->with('status', 'Section creation was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function getByClassId($id)
    {
        $schoolClass = SchoolClass::find($id);

        if ($schoolClass) {
            Section::defaultForClass($schoolClass);
        }

        $sections = $this->schoolSectionRepository->getAllByClassId($id);
        $courses = $this->courseRepository->getByClassId($id);

        return response()->json(['sections' => $sections, 'courses' => $courses]);
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Section $section)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit($section_id)
    {
        $current_school_session_id = $this->getSchoolCurrentSession();

        $section = $this->schoolSectionRepository->findById($section_id);

        $data = [
            'current_school_session_id' => $current_school_session_id,
            'section_id' => $section_id,
            'section' => $section,
        ];

        return view('sections.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request)
    {
        try {
            $this->schoolSectionRepository->update($request);

            return back()->with('status', 'Section edit was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(Section $section)
    {
        //
    }
}
