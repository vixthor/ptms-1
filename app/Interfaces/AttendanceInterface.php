<?php

namespace App\Interfaces;

interface AttendanceInterface
{
    public function saveAttendance($request);

    public function getClassAttendance($class_id, $session_id);

    public function getStudentAttendance($session_id, $student_id);
}
