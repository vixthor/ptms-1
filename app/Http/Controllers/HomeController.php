<?php

namespace App\Http\Controllers;

use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\UserInterface;
use App\Repositories\NoticeRepository;
use App\Repositories\PromotionRepository;
use App\Services\CommonEntranceService;
use App\Services\DashboardService;
use App\Traits\SchoolSession;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;

    protected $schoolClassRepository;

    protected $userRepository;

    public function __construct(UserInterface $userRepository, SchoolSessionInterface $schoolSessionRepository, SchoolClassInterface $schoolClassRepository)
    {
        $this->userRepository = $userRepository;
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository = $schoolClassRepository;
    }

    /**
     * Role-aware dashboard: school-wide numbers for office staff, fee
     * collection for whoever handles payments, and "my class today" for
     * class teachers.
     */
    public function index(DashboardService $dashboard, CommonEntranceService $commonEntrance): View
    {
        $user = Auth::user();
        $current_school_session_id = (int) $this->getSchoolCurrentSession();

        $term = $dashboard->currentTerm($current_school_session_id);
        $resultsTerm = $dashboard->resultsTerm($current_school_session_id);

        $data = [
            'term' => $term,
            'resultsTerm' => $resultsTerm,
            'resultProgress' => collect(),
            'atRisk' => null,
            'upcomingEvents' => $dashboard->upcomingEvents($current_school_session_id),
            'notices' => (new NoticeRepository)->getAll($current_school_session_id),
            'showsSchoolOverview' => $user->canManageStudentsAndTeachers(),
            'showsFees' => $user->canManagePayments(),
            'teacherClasses' => collect(),
            'commonEntrance' => null,
        ];

        if ($user->canManageAcademics()) {
            $data['commonEntrance'] = $commonEntrance->summary($current_school_session_id);
            $data['atRisk'] = $dashboard->pupilsAtRisk($current_school_session_id, $term, $user->canManagePayments());

            if ($resultsTerm) {
                $data['resultProgress'] = $dashboard->resultProgress($current_school_session_id, $resultsTerm);
            }
        }

        if ($data['showsSchoolOverview']) {
            $studentCount = $this->userRepository->getAllStudentsBySessionCount($current_school_session_id);

            $data += [
                'classCount' => $this->schoolClassRepository->getAllBySession($current_school_session_id)->count(),
                'studentCount' => $studentCount,
                'teacherCount' => $this->userRepository->getAllTeachers()->count(),
                'maleStudentsBySession' => (new PromotionRepository)->getMaleStudentsBySessionCount($current_school_session_id),
                'attendance' => $dashboard->attendanceToday($current_school_session_id),
                'absentees' => $dashboard->frequentAbsentees($current_school_session_id),
            ];
        }

        if ($data['showsFees']) {
            $data['fees'] = $dashboard->feeSummary($current_school_session_id, $term);
        }

        if ($user->role === 'teacher') {
            $data['teacherClasses'] = $dashboard->teacherClasses($current_school_session_id, $user->id);
            $myClassIds = $data['teacherClasses']->pluck('class_id');

            $data['absentees'] = $myClassIds
                ->flatMap(fn (int $classId) => $dashboard->frequentAbsentees($current_school_session_id, $classId))
                ->values();

            if ($myClassIds->isNotEmpty()) {
                $data['atRisk'] = $dashboard->pupilsAtRisk($current_school_session_id, $term, false, $myClassIds);

                if ($resultsTerm) {
                    $data['resultProgress'] = $dashboard->resultProgress($current_school_session_id, $resultsTerm, $myClassIds);
                }
            }
        }

        if ($user->role === 'student') {
            $data['student'] = $dashboard->studentOverview($current_school_session_id, $user, $term);
        }

        return view('dashboard', $data);
    }
}
