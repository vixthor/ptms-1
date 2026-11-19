<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseStoreRequest;
use App\Interfaces\CourseInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Models\Course;
use App\Repositories\PromotionRepository;
use App\Traits\SchoolSession;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CourseController extends Controller
{
    use SchoolSession;

    protected $schoolCourseRepository;

    protected $schoolSessionRepository;

    /**
     * Create a new Controller instance
     *
     * @return void
     */
    public function __construct(SchoolSessionInterface $schoolSessionRepository, CourseInterface $schoolCourseRepository)
    {
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolCourseRepository = $schoolCourseRepository;
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
    public function store(CourseStoreRequest $request)
    {
        try {
            $this->schoolCourseRepository->create($request->validated());

            return back()->with('status', 'Subject creation was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function getStudentCourses($student_id)
    {
        $current_school_session_id = $this->getSchoolCurrentSession();
        $promotionRepository = new PromotionRepository;
        $class_info = $promotionRepository->getPromotionInfoById($current_school_session_id, $student_id);
        $courses = $this->schoolCourseRepository->getByClassId($class_info->class_id);

        $data = [
            'class_info' => $class_info,
            'courses' => $courses,
        ];

        return view('courses.student', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit($course_id)
    {
        $current_school_session_id = $this->getSchoolCurrentSession();

        $course = $this->schoolCourseRepository->findById($course_id);

        $data = [
            'current_school_session_id' => $current_school_session_id,
            'course' => $course,
            'course_id' => $course_id,
        ];

        return view('courses.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request)
    {
        try {
            $this->schoolCourseRepository->update($request);

            return back()->with('status', 'Subject update was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(Course $course)
    {
        //
    }
}
