<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeacherAssignRequest;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\SemesterInterface;
use App\Models\AssignedTeacher;
use App\Repositories\AssignedTeacherRepository;
use App\Traits\SchoolSession;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AssignedTeacherController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;

    protected $semesterRepository;

    /**
     * Create a new Controller instance
     *
     * @return void
     */
    public function __construct(SchoolSessionInterface $schoolSessionRepository,
        SemesterInterface $semesterRepository)
    {
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->semesterRepository = $semesterRepository;
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
     * Display a listing of the resource.
     *
     * @param  CourseStoreRequest  $request
     * @return Response
     */
    public function getTeacherCourses(Request $request)
    {
        $teacher_id = $request->query('teacher_id');
        $semester_id = $request->query('semester_id');

        if ($teacher_id == null) {
            abort(404);
        }

        $current_school_session_id = $this->getSchoolCurrentSession();

        $semesters = $this->semesterRepository->getAll($current_school_session_id);

        $assignedTeacherRepository = new AssignedTeacherRepository;

        if ($semester_id == null) {
            $courses = [];
        } else {
            $courses = $assignedTeacherRepository->getTeacherCourses($current_school_session_id, $teacher_id, $semester_id);
        }

        $class_assignments = AssignedTeacher::with('schoolClass')
            ->where('session_id', $current_school_session_id)
            ->where('teacher_id', $teacher_id)
            ->get()
            ->filter(fn (AssignedTeacher $assignment) => $assignment->schoolClass !== null);

        $data = [
            'class_assignments' => $class_assignments,
            'courses' => $courses,
            'semesters' => $semesters,
            'selected_semester_id' => $semester_id,
        ];

        return view('courses.teacher', $data);
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
    public function store(TeacherAssignRequest $request)
    {
        try {
            $assignedTeacherRepository = new AssignedTeacherRepository;
            $assignedTeacherRepository->assign($request->validated());

            return back()->with('status', 'Class teacher assigned successfully!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }
}
