<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommonEntranceUpdateRequest;
use App\Interfaces\SchoolSessionInterface;
use App\Models\CommonEntranceCandidate;
use App\Models\Promotion;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\CommonEntranceService;
use App\Traits\SchoolSession;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Final-year pupils' Common Entrance registration, scores and placements.
 */
class CommonEntranceController extends Controller
{
    use SchoolSession;

    public function __construct(protected SchoolSessionInterface $schoolSessionRepository) {}

    public function index(Request $request, CommonEntranceService $commonEntrance): View
    {
        $user = $request->user();
        $sessionId = (int) $this->getSchoolCurrentSession();
        $classes = $this->manageableClasses($user, $sessionId);

        abort_unless($user->canManageAcademics() || $classes->isNotEmpty(), 403);

        $finalYearClass = $commonEntrance->finalYearClass($sessionId);
        $requestedClassId = (int) $request->query('class_id');

        $schoolClass = $classes->firstWhere('id', $requestedClassId)
            ?? $classes->firstWhere('id', optional($finalYearClass)->id)
            ?? $classes->first();

        $pupils = $schoolClass
            ? Promotion::with('student')
                ->where('session_id', $sessionId)
                ->where('class_id', $schoolClass->id)
                ->get()
                ->pluck('student')
                ->filter()
                ->sortBy(fn (User $student) => $student->last_name.' '.$student->first_name)
                ->values()
            : collect();

        return view('common_entrance.index', [
            'classes' => $classes,
            'schoolClass' => $schoolClass,
            'pupils' => $pupils,
            'candidates' => CommonEntranceCandidate::where('session_id', $sessionId)
                ->whereIn('student_id', $pupils->pluck('id'))
                ->get()
                ->keyBy('student_id'),
            'summary' => $schoolClass ? $commonEntrance->summary($sessionId, $schoolClass) : null,
        ]);
    }

    public function update(CommonEntranceUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $sessionId = (int) $this->getSchoolCurrentSession();
        $validated = $request->validated();
        $classId = (int) $validated['class_id'];

        abort_unless($this->manageableClasses($user, $sessionId)->contains('id', $classId), 403);

        $pupilIds = Promotion::where('session_id', $sessionId)->where('class_id', $classId)->pluck('student_id');
        $existing = CommonEntranceCandidate::where('session_id', $sessionId)->whereIn('student_id', $pupilIds)->get()->keyBy('student_id');

        foreach ($validated['candidates'] ?? [] as $studentId => $details) {
            if (! $pupilIds->contains((int) $studentId)) {
                continue;
            }

            $values = [
                'registration_number' => filled($details['registration_number'] ?? null) ? trim($details['registration_number']) : null,
                'score' => filled($details['score'] ?? null) ? $details['score'] : null,
                'admitted_school' => filled($details['admitted_school'] ?? null) ? trim($details['admitted_school']) : null,
            ];

            if (! $existing->has((int) $studentId) && collect($values)->filter()->isEmpty()) {
                continue;
            }

            CommonEntranceCandidate::updateOrCreate(
                ['student_id' => (int) $studentId, 'session_id' => $sessionId],
                $values
            );
        }

        return redirect()
            ->route('common-entrance.index', ['class_id' => $classId])
            ->with('status', 'Common Entrance records saved.');
    }

    /**
     * @return Collection<int, SchoolClass>
     */
    private function manageableClasses(User $user, int $sessionId): Collection
    {
        return SchoolClass::where('session_id', $sessionId)
            ->when(! $user->canManageAcademics(), fn ($query) => $query->whereIn('id', $user->classTeacherClassIds($sessionId)))
            ->orderBy('id')
            ->get();
    }
}
