<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'student_id',
        'category',
        'session_id',
        'recorded_by',
        'amount',
        'payment_method',
        'gateway',
        'status',
        'reference',
        'paid_for',
        'payment_date',
        'note',
    ];

    public const CATEGORIES = [
        'Tuition',
        'Food / Meals',
        'Party / Celebration',
        'Trip / Excursion',
        'Uniform',
        'Books / Materials',
        'Other',
    ];

    public const STATUS_PENDING = 'pending';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function session()
    {
        return $this->belongsTo(SchoolSession::class, 'session_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
