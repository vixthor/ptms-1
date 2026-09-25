<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'gender',
        'nationality',
        'phone',
        'address',
        'address2',
        'city',
        'zip',
        'photo',
        'birthday',
        'religion',
        'blood_type',
        'role',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the parent_info.
     */
    public function parent_info()
    {
        return $this->hasOne(StudentParentInfo::class, 'student_id', 'id');
    }

    /**
     * Get the academic_info.
     */
    public function academic_info()
    {
        return $this->hasOne(StudentAcademicInfo::class, 'student_id', 'id');
    }

    /**
     * Get the marks.
     */
    public function marks()
    {
        return $this->hasMany(Mark::class, 'student_id', 'id');
    }

    /**
     * Staff designations, distinct from the base 'admin' role. Each is its
     * own `role` value, stored the same way 'teacher'/'student' are.
     */
    public const ROLE_PROPRIETOR = 'proprietor';

    public const ROLE_HEADTEACHER = 'headteacher';

    public const ROLE_SECRETARY = 'secretary';

    public const STAFF_DESIGNATIONS = [
        self::ROLE_HEADTEACHER,
        self::ROLE_PROPRIETOR,
        self::ROLE_SECRETARY,
    ];

    /**
     * Proprietor (owner) has full parity with Admin.
     */
    public function isAdminLevel(): bool
    {
        return in_array($this->role, ['admin', self::ROLE_PROPRIETOR]);
    }

    /**
     * Academic administration: classes, sections, courses, exams, grading,
     * promotions, notices/events/syllabus/routines, academic settings.
     */
    public function canManageAcademics(): bool
    {
        return $this->isAdminLevel() || $this->role === self::ROLE_HEADTEACHER;
    }

    /**
     * Clerical/administrative duties: student & teacher records, notices,
     * attendance, fee collection.
     */
    public function canManageClerical(): bool
    {
        return $this->isAdminLevel() || $this->role === self::ROLE_SECRETARY;
    }

    public function canManageStudentsAndTeachers(): bool
    {
        return $this->canManageAcademics() || $this->canManageClerical();
    }

    public function canManagePayments(): bool
    {
        return $this->isAdminLevel() || $this->role === self::ROLE_SECRETARY;
    }

    /**
     * Staff (HR) management stays exclusive to ownership level.
     */
    public function canManageStaff(): bool
    {
        return $this->isAdminLevel();
    }

    /**
     * Public website content management stays exclusive to ownership level.
     */
    public function canManageWebsite(): bool
    {
        return $this->isAdminLevel();
    }
}
