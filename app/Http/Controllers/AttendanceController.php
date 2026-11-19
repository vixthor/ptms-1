<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceStoreRequest;
use App\Interfaces\AcademicSettingInterface;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\SectionInterface;
use App\Interfaces\UserInterface;
use App\Models\Attendance;
use App\Models\Section;
use App\Repositories\AssignedTeacherRepository;
use App\Repositories\AttendanceRepository;
use App\Traits\SchoolSession;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AttendanceController extends Controller
{
    use SchoolSession;

    protected $academicSettingRepository;

    protected $schoolSessionRepository;

    protected $schoolClassRepository;

    protected $sectionRepository;

    protected $userRepository;

    public function __construct(
        UserInterface $userRepository,
        AcademicSettingInterface $academicSettingRepository,
        SchoolSessionInterface $schoolSessionRepository,
        SchoolClassInterface $schoolClassRepository,
        SectionInterface $sectionRepository
    ) {
        // $this->middleware(['can:view attendances']);

        $this->userRepository = $userRepository;
        $this->academicSettingRepository = $academicSettingRepository;
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->sectionRepository = $sectionRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return back();
        // $academic_setting = $this->academicSettingRepository->getAcademicSetting();

        // $current_school_session_id = $this->getSchoolCurrentSession();

        // $classes_and_sections = $this->schoolClassRepository->getClassesAndSections($current_school_session_id);
        // $courseRepository = new CourseRepository();
        // $courses = $courseRepository->getAll($current_school_session_id);

        // $data = [
        //     'academic_setting'      => $academic_setting,
        //     'classes_and_sections'  => $classes_and_sections,
        //     'courses'               => $courses,
        // ];

        // return view('attendances.index', $data);
    }

    /**
     * Attendance is taken once a day for a whole class, only by its class teacher.
     */
    protected function abortUnlessClassTeacher(int $class_id, int $session_id): void
    {
        $classTeacher = (new AssignedTeacherRepository)->getAssignedTeacher($session_id, 0, $class_id, 0, 0);

        abort_unless($classTeacher && $classTeacher->teacher_id === auth()->id(), 403, 'Only the class teacher can take attendance for this class.');
    }

    /**
     * Show today's register for a class.
     *
     * @return Response
     */
    public function create(Request $request)
    {
        if ($request->query('class_id') == null) {
            return abort(404);
        }

        $current_school_session_id = $this->getSchoolCurrentSession();
        $class_id = (int) $request->query('class_id');

        $this->abortUnlessClassTeacher($class_id, $current_school_session_id);

        try {
            $school_class = $this->schoolClassRepository->findById($class_id);

            if (! $school_class) {
                return abort(404);
            }

            $school_section = Section::defaultForClass($school_class);

            $student_list = $this->userRepository->getAllStudents($current_school_session_id, $class_id, $school_section->id);

            $todays_attendance = (new AttendanceRepository)
                ->getClassAttendance($class_id, $current_school_session_id)
                ->keyBy('student_id');

            $data = [
                'current_school_session_id' => $current_school_session_id,
                'student_list' => $student_list,
                'school_class' => $school_class,
                'school_section' => $school_section,
                'todays_attendance' => $todays_attendance,
            ];

            return view('attendances.take', $data);
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    /**
     * Save (or correct) today's register for a class.
     *
     * @return Response
     */
    public function store(AttendanceStoreRequest $request)
    {
        $validated = $request->validated();

        $this->abortUnlessClassTeacher((int) $validated['class_id'], (int) $validated['session_id']);

        try {
            (new AttendanceRepository)->saveAttendance($validated);

            return back()->with('status', "Today's attendance was saved.");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    /**
     * Today's register for a class.
     *
     * @return Response
     */
    public function show(Request $request)
    {
        if ($request->query('class_id') == null) {
            return abort(404);
        }

        $current_school_session_id = $this->getSchoolCurrentSession();

        try {
            $attendances = (new AttendanceRepository)->getClassAttendance($request->query('class_id'), $current_school_session_id);

            return view('attendances.view', ['attendances' => $attendances]);
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function showStudentAttendance($id)
    {
        if (auth()->user()->role == 'student' && auth()->user()->id != $id) {
            return abort(404);
        }
        $current_school_session_id = $this->getSchoolCurrentSession();

        $attendanceRepository = new AttendanceRepository;
        $attendances = $attendanceRepository->getStudentAttendance($current_school_session_id, $id);
        $student = $this->userRepository->findStudent($id);

        $data = [
            'attendances' => $attendances,
            'student' => $student,
        ];

        return view('attendances.attendance', $data);
    }
}
