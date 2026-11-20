<?php

namespace App\Http\Controllers;

use App\Http\Requests\ParentMessageStoreRequest;
use App\Interfaces\SchoolSessionInterface;
use App\Models\ParentMessage;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\DashboardService;
use App\Services\ParentMessagingService;
use App\Services\TermiiSmsService;
use App\Traits\SchoolSession;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Broadcast SMS / WhatsApp messages to parents: the whole school, one class,
 * or families who haven't paid tuition this term.
 */
class ParentMessageController extends Controller
{
    use SchoolSession;

    public function __construct(protected SchoolSessionInterface $schoolSessionRepository) {}

    public function create(Request $request, TermiiSmsService $sms): View
    {
        $user = $request->user();
        $sessionId = (int) $this->getSchoolCurrentSession();
        abort_unless($this->canMessageParents($user), 403);

        return view('parent_messages.create', [
            'classes' => $this->messageableClasses($user, $sessionId),
            'audiences' => $this->allowedAudiences($user),
            'selectedClassId' => (int) $request->query('class_id'),
            'selectedAudience' => $request->query('audience'),
            'smsConfigured' => $sms->isConfigured(),
            'recentMessages' => ParentMessage::with(['sender', 'schoolClass'])
                ->where('session_id', $sessionId)
                ->when(! $user->canManageStudentsAndTeachers(), fn ($query) => $query->where('sender_id', $user->id))
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }

    public function store(ParentMessageStoreRequest $request, ParentMessagingService $messaging, DashboardService $dashboard, TermiiSmsService $sms): RedirectResponse
    {
        $user = $request->user();
        $sessionId = (int) $this->getSchoolCurrentSession();
        $validated = $request->validated();
        $classId = $validated['audience'] === ParentMessage::AUDIENCE_CLASS ? (int) $validated['class_id'] : null;

        abort_unless(array_key_exists($validated['audience'], $this->allowedAudiences($user)), 403);
        abort_if($classId !== null && ! $this->messageableClasses($user, $sessionId)->contains('id', $classId), 403);

        if ($validated['channel'] === ParentMessage::CHANNEL_SMS && ! $sms->isConfigured()) {
            return back()->withInput()->withErrors(['channel' => 'SMS is not set up yet. Send through WhatsApp instead.']);
        }

        $recipients = $messaging->recipients($sessionId, $validated['audience'], $classId, $dashboard->currentTerm($sessionId));

        if ($recipients->isEmpty()) {
            return back()->withInput()->withErrors(['audience' => 'No parent phone numbers were found for this group.']);
        }

        $message = new ParentMessage([
            'session_id' => $sessionId,
            'sender_id' => $user->id,
            'audience' => $validated['audience'],
            'class_id' => $classId,
            'body' => $validated['body'],
            'channel' => $validated['channel'],
            'recipients' => $recipients->all(),
            'recipients_count' => $recipients->count(),
            'status' => ParentMessage::STATUS_PREPARED,
        ]);

        if ($validated['channel'] === ParentMessage::CHANNEL_SMS) {
            try {
                $sms->sendBulk($recipients->pluck('phone')->all(), $validated['body']);
                $message->status = ParentMessage::STATUS_SENT;
            } catch (RuntimeException $e) {
                $message->status = ParentMessage::STATUS_FAILED;
                $message->error = $e->getMessage();
            }
        }

        $message->save();

        return redirect()->route('parent-messages.show', $message);
    }

    public function show(Request $request, ParentMessage $parentMessage): View
    {
        $user = $request->user();
        abort_unless($user->canManageStudentsAndTeachers() || $parentMessage->sender_id === $user->id, 403);

        return view('parent_messages.show', ['message' => $parentMessage->load(['sender', 'schoolClass'])]);
    }

    private function canMessageParents(User $user): bool
    {
        return $user->canManageStudentsAndTeachers() || $user->role === 'teacher';
    }

    /**
     * @return array<string, string>
     */
    private function allowedAudiences(User $user): array
    {
        if (! $user->canManageStudentsAndTeachers()) {
            return [ParentMessage::AUDIENCE_CLASS => 'Parents in my class'];
        }

        $audiences = [
            ParentMessage::AUDIENCE_SCHOOL => 'All parents',
            ParentMessage::AUDIENCE_CLASS => 'Parents in one class',
        ];

        if ($user->canManagePayments()) {
            $audiences[ParentMessage::AUDIENCE_UNPAID_TUITION] = "Parents who haven't paid tuition this term";
        }

        return $audiences;
    }

    /**
     * @return Collection<int, SchoolClass>
     */
    private function messageableClasses(User $user, int $sessionId): Collection
    {
        return SchoolClass::where('session_id', $sessionId)
            ->when(! $user->canManageStudentsAndTeachers(), fn ($query) => $query->whereIn('id', $user->classTeacherClassIds($sessionId)))
            ->orderBy('id')
            ->get();
    }
}
