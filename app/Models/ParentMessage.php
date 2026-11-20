<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentMessage extends Model
{
    use HasFactory;

    public const AUDIENCE_SCHOOL = 'school';

    public const AUDIENCE_CLASS = 'class';

    public const AUDIENCE_UNPAID_TUITION = 'unpaid_tuition';

    public const CHANNEL_SMS = 'sms';

    public const CHANNEL_WHATSAPP = 'whatsapp';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    /**
     * WhatsApp messages are prepared as click-to-chat links the sender opens one by one.
     */
    public const STATUS_PREPARED = 'prepared';

    protected $fillable = [
        'session_id',
        'sender_id',
        'audience',
        'class_id',
        'body',
        'channel',
        'recipients',
        'recipients_count',
        'status',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'recipients' => 'array',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function audienceLabel(): string
    {
        return match ($this->audience) {
            self::AUDIENCE_SCHOOL => 'Whole school',
            self::AUDIENCE_UNPAID_TUITION => 'Tuition not paid',
            default => optional($this->schoolClass)->class_name ?? 'A class',
        };
    }
}
