<?php

namespace Database\Seeders;

use App\Models\AssignedTeacher;
use App\Models\Promotion;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\Section;
use App\Models\StudentAcademicInfo;
use App\Models\StudentParentInfo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

/**
 * Local login/test accounts: a class teacher and a student whose parents'
 * details are on file (parents use the student's login). Safe to re-run.
 *
 * php artisan db:seed --class=TestAccountsSeeder
 */
class TestAccountsSeeder extends Seeder
{
    public const PASSWORD = 'password';

    public const TEACHER_EMAIL = 'classteacher@ptms.test';

    public const STUDENT_EMAIL = 'student@ptms.test';

    /** @var list<string> */
    private const TEACHER_PERMISSIONS = [
        'create exams', 'view exams', 'create exams rule', 'view exams rule', 'edit exams rule',
        'delete exams rule', 'take attendances', 'view attendances', 'create assignments',
        'view assignments', 'save marks', 'view users', 'view routines', 'view syllabi',
        'view events', 'view notices',
    ];

    /** @var list<string> */
    private const STUDENT_PERMISSIONS = [
        'view attendances', 'view assignments', 'submit assignments', 'view exams', 'view marks',
        'view users', 'view routines', 'view syllabi', 'view events', 'view notices',
    ];

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('TestAccountsSeeder skipped in production.');

            return;
        }

        $session = SchoolSession::latest('id')->first()
            ?? SchoolSession::create(['session_name' => now()->year.'-'.(now()->year + 1)]);

        $schoolClass = SchoolClass::where('session_id', $session->id)->orderBy('id')->first()
            ?? SchoolClass::create(['class_name' => 'Primary 1', 'session_id' => $session->id]);

        $section = Section::defaultForClass($schoolClass);

        $teacher = User::updateOrCreate(['email' => self::TEACHER_EMAIL], [
            'first_name' => 'Grace',
            'last_name' => 'Teacher',
            'gender' => 'female',
            'nationality' => 'Nigerian',
            'phone' => '08000000001',
            'address' => '1 School Road',
            'city' => 'Lagos',
            'zip' => '100001',
            'role' => 'teacher',
            'password' => Hash::make(self::PASSWORD),
        ]);
        $teacher->syncPermissions($this->permissions(self::TEACHER_PERMISSIONS));

        AssignedTeacher::updateOrCreate(
            ['session_id' => $session->id, 'class_id' => $schoolClass->id],
            ['teacher_id' => $teacher->id],
        );

        $student = User::updateOrCreate(['email' => self::STUDENT_EMAIL], [
            'first_name' => 'Daniel',
            'last_name' => 'Student',
            'gender' => 'male',
            'nationality' => 'Nigerian',
            'phone' => '08000000002',
            'address' => '2 Family Close',
            'city' => 'Lagos',
            'zip' => '100001',
            'birthday' => '2018-03-14',
            'religion' => 'Christianity',
            'blood_type' => 'O+',
            'role' => 'student',
            'password' => Hash::make(self::PASSWORD),
        ]);
        $student->syncPermissions($this->permissions(self::STUDENT_PERMISSIONS));

        StudentParentInfo::updateOrCreate(['student_id' => $student->id], [
            'father_name' => 'Samuel Student',
            'father_phone' => '08000000003',
            'mother_name' => 'Ruth Student',
            'mother_phone' => '08000000004',
            'parent_address' => '2 Family Close, Lagos',
        ]);

        StudentAcademicInfo::updateOrCreate(['student_id' => $student->id], ['board_reg_no' => null]);

        Promotion::updateOrCreate(
            ['student_id' => $student->id, 'session_id' => $session->id],
            [
                'class_id' => $schoolClass->id,
                'section_id' => $section->id,
                'id_card_number' => 'PTMS-TEST-0001',
            ],
        );

        $this->command?->info("Seeded {$teacher->email} (class teacher of {$schoolClass->class_name}) and {$student->email}; password: ".self::PASSWORD);
    }

    /**
     * @param  list<string>  $names
     * @return list<string>
     */
    private function permissions(array $names): array
    {
        foreach ($names as $name) {
            Permission::findOrCreate($name);
        }

        return $names;
    }
}
