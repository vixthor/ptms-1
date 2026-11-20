<?php

namespace App\Services;

use App\Models\ParentMessage;
use App\Models\Promotion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Works out which parent phone numbers a broadcast goes to. Siblings share
 * parents, so numbers are de-duplicated and each family gets one message.
 */
class ParentMessagingService
{
    public function __construct(private DashboardService $dashboard) {}

    /**
     * @param  array{status: string, start: Carbon, end: Carbon}|null  $term
     * @return Collection<int, array{phone: string, name: string}>
     */
    public function recipients(int $sessionId, string $audience, ?int $classId, ?array $term): Collection
    {
        $pupils = Promotion::with(['student.parent_info'])
            ->where('session_id', $sessionId)
            ->when($audience === ParentMessage::AUDIENCE_CLASS, fn ($query) => $query->where('class_id', $classId))
            ->when($audience === ParentMessage::AUDIENCE_UNPAID_TUITION, fn ($query) => $query->whereNotIn('student_id', $this->dashboard->tuitionPayerIds($sessionId, $term)))
            ->get()
            ->pluck('student')
            ->filter();

        $families = [];

        foreach ($pupils as $pupil) {
            $parentInfo = $pupil->parent_info;
            $numbers = collect([optional($parentInfo)->father_phone, optional($parentInfo)->mother_phone])
                ->map(fn (?string $phone) => self::normalisePhone($phone))
                ->filter();

            if ($numbers->isEmpty()) {
                $numbers = collect([self::normalisePhone($pupil->phone)])->filter();
            }

            foreach ($numbers->unique() as $phone) {
                $families[$phone][] = $pupil->first_name;
            }
        }

        return collect($families)
            ->map(fn (array $children, string $phone) => [
                'phone' => (string) $phone,
                'name' => 'Parent of '.collect($children)->unique()->join(', ', ' & '),
            ])
            ->values();
    }

    /**
     * Converts a Nigerian number (08031234567, +234 803 123 4567, 8031234567)
     * to 2348031234567. Returns null for anything that isn't a mobile number.
     */
    public static function normalisePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = '234'.substr($digits, 1);
        } elseif (strlen($digits) === 10 && in_array($digits[0], ['7', '8', '9'], true)) {
            $digits = '234'.$digits;
        }

        return preg_match('/^234[789]\d{9}$/', $digits) ? $digits : null;
    }

    public static function whatsappLink(string $phone, string $message): string
    {
        return 'https://wa.me/'.$phone.'?text='.rawurlencode($message);
    }
}
