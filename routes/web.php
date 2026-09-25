<?php

use App\Http\Controllers\AcademicProgramController;
use App\Http\Controllers\AcademicSettingController;
use App\Http\Controllers\AdmissionStepController;
use App\Http\Controllers\AssignedTeacherController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CbtQuestionController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamRuleController;
use App\Http\Controllers\FeeItemController;
use App\Http\Controllers\GalleryPhotoController;
use App\Http\Controllers\GradeRuleController;
use App\Http\Controllers\GradingSystemController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarkController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\ReportCardController;
use App\Http\Controllers\RoutineController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\SchoolSessionController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\SiteSettingController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StudentPaymentController;
use App\Http\Controllers\SyllabusController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public marketing site — no auth required.
Route::get('/', [PublicSiteController::class, 'home'])->name('public.home');
Route::get('/about', [PublicSiteController::class, 'about'])->name('public.about');
Route::get('/academics', [PublicSiteController::class, 'academics'])->name('public.academics');
Route::get('/admissions', [PublicSiteController::class, 'admissions'])->name('public.admissions');
Route::get('/contact', [PublicSiteController::class, 'contact'])->name('public.contact');
Route::get('/news', [PublicSiteController::class, 'news'])->name('public.news');
Route::get('/news/{notice}', [PublicSiteController::class, 'newsShow'])->name('public.news.show');
Route::get('/gallery', [PublicSiteController::class, 'gallery'])->name('public.gallery');

Route::group(['middlware' => 'guest'], function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'process'])->name('login.process');
});
Route::group(['middleware' => 'auth'], function () {
    Route::get('/home', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/change-password', [AuthController::class, 'updatePasword'])->name('password.edit');
    Route::post('/change-password', [AuthController::class, 'processUpdatePasword'])->name('password.update');

    // academic setting
    Route::get('/academics/settings', [AcademicSettingController::class, 'index'])->name('academic.setting');
    Route::post('attandence/update', [AcademicSettingController::class, 'updateAttendanceType'])->name('attatndence.update');
    Route::post('final-marks-submission-status/update', [AcademicSettingController::class, 'updateFinalMarksSubmissionStatus'])->name('final.marks.submission.status.update');

    // session
    Route::post('session/store', [SchoolSessionController::class, 'store'])->name('session.store');
    Route::post('session/browse', [SchoolSessionController::class, 'browse'])->name('session.browse');

    // semester
    Route::post('semester/store', [SemesterController::class, 'store'])->name('semester.store');

    // class
    Route::get('/classes', [SchoolClassController::class, 'index'])->name('class.list');
    Route::post('class/store', [SchoolClassController::class, 'store'])->name('class.store');
    Route::get('/class/edit/{id}', [SchoolClassController::class, 'edit'])->name('class.edit');
    Route::post('class/update', [SchoolClassController::class, 'update'])->name('class.update');

    // section
    Route::post('section/create', [SectionController::class, 'store'])->name('section.create');
    Route::post('section/store', [SectionController::class, 'store'])->name('section.store');
    Route::get('/section/edit/{id}', [SectionController::class, 'edit'])->name('section.edit');
    Route::post('section/update', [SectionController::class, 'update'])->name('section.update');
    Route::get('/section/{id}', [SectionController::class, 'getByClassId'])->name('get.sections.courses.by.classId');

    // Courses
    Route::post('course/store', [CourseController::class, 'store'])->name('course.store');
    Route::post('course/update', [CourseController::class, 'update'])->name('course.update');
    Route::get('course/edit/{id}', [CourseController::class, 'edit'])->name('course.edit');

    // teacher
    Route::get('teacher/add', [AcademicSettingController::class, 'create'])->name('teacher.create');
    Route::post('teacher/store', [UserController::class, 'storeTeacher'])->name('teacher.store');
    Route::post('teacher/assign', [AssignedTeacherController::class, 'store'])->name('teacher.assign');
    Route::get('/teachers', [UserController::class, 'getTeacherList'])->name('teacher.list');
    Route::get('teacher/profile/{id}', [UserController::class, 'showTeacherProfile'])->name('teacher.profile');
    Route::get('/teachers/{id}', [UserController::class, 'editTeacher'])->name('teacher.edit');
    Route::post('teacher/update', [UserController::class, 'updateTeacher'])->name('teacher.update');

    // student
    Route::get('/students/add', [UserController::class, 'createStudent'])->name('student.create');
    Route::post('student/store', [UserController::class, 'storeStudent'])->name('student.store');
    Route::get('/students', [UserController::class, 'getStudentList'])->name('student.list');
    Route::get('/students/{id}', [UserController::class, 'editStudent'])->name('student.edit');
    Route::post('student/update', [UserController::class, 'updateStudent'])->name('student.update');
    Route::get('/students/profile/{id}', [UserController::class, 'showStudentProfile'])->name('student.profile');
    Route::get('/students/attendance/{id}', [AttendanceController::class, 'showStudentAttendance'])->name('student.attendance');
    Route::get('/attendances/take', [AttendanceController::class, 'create'])->name('attendance.create.show');
    Route::post('/attendances/store', [AttendanceController::class, 'store'])->name('attendances.store');
    Route::get('/attendances/view', [AttendanceController::class, 'show'])->name('attendance.list.show');

    // exams
    Route::get('/exams', [ExamController::class, 'index'])->name('exam.list');

    Route::get('/exams/add', [ExamController::class, 'create'])->name('exam.create');
    Route::post('/exams/store', [ExamController::class, 'store'])->name('exam.store');

    // exam rules
    Route::get('/exams/rules', [ExamRuleController::class, 'index'])->name('exam.rule.list');
    Route::get('/exams/rules/add', [ExamRuleController::class, 'create'])->name('exam.rule.create');
    Route::post('/exams/rules/store', [ExamRuleController::class, 'store'])->name('exam.rule.store');
    Route::get('/exams/rules/edit', [ExamRuleController::class, 'edit'])->name('exam.rule.edit');
    Route::post('/exams/rules/update', [ExamRuleController::class, 'update'])->name('exam.rule.update');

    // grade
    Route::get('/exams/grade/create', [GradingSystemController::class, 'create'])->name('exam.grade.system.create');
    Route::post('/exams/grade/store', [GradingSystemController::class, 'store'])->name('exam.grade.system.store');
    Route::get('/exams/grade', [GradingSystemController::class, 'index'])->name('exam.grade.system.list');
    Route::get('/exams/grade/rules/add', [GradeRuleController::class, 'create'])->name('exam.grade.system.rule.create');
    Route::post('/exams/grade/rule/store', [GradeRuleController::class, 'store'])->name('exam.grade.system.rule.store');
    Route::get('/exams/grade/rules', [GradeRuleController::class, 'index'])->name('exam.grade.system.rule.list');
    Route::post('/exams/grade/rule/delete', [GradeRuleController::class, 'destroy'])->name('exam.grade.system.rule.delete');

    // mark
    Route::get('/marks', [MarkController::class, 'showCourseMark'])->name('course.mark');
    Route::get('/marks/create', [MarkController::class, 'create'])->name('course.mark.create');
    Route::post('/marks/store', [MarkController::class, 'store'])->name('course.mark.store');
    Route::get('/marks/results', [MarkController::class, 'index'])->name('course.mark.list.show');
    Route::get('/marks/final/submit', [MarkController::class, 'showFinalMark'])->name('course.final.mark.submit.show');
    Route::post('/marks/final/store', [MarkController::class, 'storeFinalMark'])->name('course.final.mark.submit.store');

    // notice
    Route::get('/notice/create', [NoticeController::class, 'create'])->name('notice.create');
    Route::post('/notice/store', [NoticeController::class, 'store'])->name('notice.store');

    // Calendar events
    Route::get('calendar-event', [EventController::class, 'index'])->name('events.list');
    Route::post('calendar-crud-ajax', [EventController::class, 'calendarEvents'])->name('events.crud');

    // syllabus
    Route::get('/syllabuses', [SyllabusController::class, 'index'])->name('syllabus.list');
    Route::get('/syllabus/add', [SyllabusController::class, 'create'])->name('syllabus.create');
    Route::post('/syllabus/store', [SyllabusController::class, 'store'])->name('syllabus.store');

    // Routines
    Route::get('/routines', [RoutineController::class, 'show'])->name('routine.list');
    Route::get('/routine/add', [RoutineController::class, 'create'])->name('routine.create');
    Route::post('/routine/store', [RoutineController::class, 'store'])->name('routine.store');

    // promotion
    Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions.list');
    Route::get('/promotions/promote', [PromotionController::class, 'create'])->name('promotions.create');
    Route::post('/promotions/promote', [PromotionController::class, 'store'])->name('promotions.store');

    // course teacher
    Route::get('courses/teachers', [AssignedTeacherController::class, 'getTeacherCourses'])->name('course.teacher.list');
    Route::get('courses/student/{student_id}', [CourseController::class, 'getStudentCourses'])->name('course.student.list');

    // Assignment
    Route::get('courses/assignments', [AssignmentController::class, 'getCourseAssignments'])->name('assignment.list');
    Route::get('courses/assignments/create', [AssignmentController::class, 'create'])->name('assignment.create');
    Route::post('courses/assignments/create', [AssignmentController::class, 'store'])->name('assignment.store');

    // Payments
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.list');
    Route::get('/payments/add', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments/store', [PaymentController::class, 'store'])->name('payments.store');

    // Student self-serve payments (Paystack)
    Route::get('/my-payments', [StudentPaymentController::class, 'index'])->name('student.payments.list');
    Route::get('/my-payments/pay', [StudentPaymentController::class, 'create'])->name('student.payments.create');
    Route::post('/my-payments/initialize', [StudentPaymentController::class, 'initialize'])->name('student.payments.initialize');
    Route::get('/my-payments/callback', [StudentPaymentController::class, 'callback'])->name('student.payments.callback');

    // Staff
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.list');
    Route::get('/staff/add', [StaffController::class, 'create'])->name('staff.create');
    Route::post('/staff/store', [StaffController::class, 'store'])->name('staff.store');
    Route::get('/staff/{id}', [StaffController::class, 'edit'])->name('staff.edit');
    Route::post('/staff/update', [StaffController::class, 'update'])->name('staff.update');

    // Report card
    Route::get('/students/{id}/report-card', [ReportCardController::class, 'show'])->name('report.card.show');
    Route::get('/students/{id}/report-card/download', [ReportCardController::class, 'download'])->name('report.card.download');
    Route::get('/students/{id}/report-card/edit', [ReportCardController::class, 'editDetails'])->name('report.card.edit');
    Route::post('/students/{id}/report-card', [ReportCardController::class, 'updateDetails'])->name('report.card.update');
    Route::get('/report-cards/mass-sign', [ReportCardController::class, 'massSignForm'])->name('report.card.mass-sign.form');
    Route::post('/report-cards/mass-sign', [ReportCardController::class, 'massSign'])->name('report.card.mass-sign');

    // CBT questions
    Route::get('/exams/{exam}/cbt/questions', [CbtQuestionController::class, 'index'])->name('cbt.questions.index');
    Route::get('/exams/{exam}/cbt/questions/add', [CbtQuestionController::class, 'create'])->name('cbt.questions.create');
    Route::post('/exams/{exam}/cbt/questions/store', [CbtQuestionController::class, 'store'])->name('cbt.questions.store');
    Route::get('/exams/{exam}/cbt/questions/{question}/edit', [CbtQuestionController::class, 'edit'])->name('cbt.questions.edit');
    Route::post('/exams/{exam}/cbt/questions/{question}/update', [CbtQuestionController::class, 'update'])->name('cbt.questions.update');
    Route::post('/exams/{exam}/cbt/questions/{question}/delete', [CbtQuestionController::class, 'destroy'])->name('cbt.questions.destroy');

    // Website admin (CMS)
    Route::get('/website-admin/pages', [PageController::class, 'index'])->name('website-admin.pages.index');
    Route::get('/website-admin/pages/{slug}/edit', [PageController::class, 'edit'])->name('website-admin.pages.edit');
    Route::post('/website-admin/pages/{slug}/update', [PageController::class, 'update'])->name('website-admin.pages.update');

    Route::get('/website-admin/academic-programs', [AcademicProgramController::class, 'index'])->name('website-admin.academic-programs.index');
    Route::get('/website-admin/academic-programs/add', [AcademicProgramController::class, 'create'])->name('website-admin.academic-programs.create');
    Route::post('/website-admin/academic-programs/store', [AcademicProgramController::class, 'store'])->name('website-admin.academic-programs.store');
    Route::get('/website-admin/academic-programs/{academicProgram}/edit', [AcademicProgramController::class, 'edit'])->name('website-admin.academic-programs.edit');
    Route::post('/website-admin/academic-programs/{academicProgram}/update', [AcademicProgramController::class, 'update'])->name('website-admin.academic-programs.update');
    Route::post('/website-admin/academic-programs/{academicProgram}/delete', [AcademicProgramController::class, 'destroy'])->name('website-admin.academic-programs.destroy');

    Route::get('/website-admin/admission-steps', [AdmissionStepController::class, 'index'])->name('website-admin.admission-steps.index');
    Route::post('/website-admin/admission-steps/store', [AdmissionStepController::class, 'store'])->name('website-admin.admission-steps.store');
    Route::post('/website-admin/admission-steps/{admissionStep}/update', [AdmissionStepController::class, 'update'])->name('website-admin.admission-steps.update');
    Route::post('/website-admin/admission-steps/{admissionStep}/delete', [AdmissionStepController::class, 'destroy'])->name('website-admin.admission-steps.destroy');

    Route::get('/website-admin/fee-items', [FeeItemController::class, 'index'])->name('website-admin.fee-items.index');
    Route::post('/website-admin/fee-items/store', [FeeItemController::class, 'store'])->name('website-admin.fee-items.store');
    Route::post('/website-admin/fee-items/{feeItem}/update', [FeeItemController::class, 'update'])->name('website-admin.fee-items.update');
    Route::post('/website-admin/fee-items/{feeItem}/delete', [FeeItemController::class, 'destroy'])->name('website-admin.fee-items.destroy');

    Route::get('/website-admin/gallery-photos', [GalleryPhotoController::class, 'index'])->name('website-admin.gallery-photos.index');
    Route::post('/website-admin/gallery-photos/store', [GalleryPhotoController::class, 'store'])->name('website-admin.gallery-photos.store');
    Route::post('/website-admin/gallery-photos/{galleryPhoto}/delete', [GalleryPhotoController::class, 'destroy'])->name('website-admin.gallery-photos.destroy');

    Route::get('/website-admin/settings', [SiteSettingController::class, 'edit'])->name('website-admin.settings.edit');
    Route::post('/website-admin/settings/update', [SiteSettingController::class, 'update'])->name('website-admin.settings.update');
});
