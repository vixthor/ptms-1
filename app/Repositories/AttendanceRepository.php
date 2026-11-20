<?php

namespace App\Repositories;

use App\Interfaces\AttendanceInterface;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Attendance is taken once per school day for a whole class by its class
 * teacher; re-submitting on the same day corrects that day's register.
 */
class AttendanceRepository implements AttendanceInterface
{
    /**
     * @param  array{class_id: int, section_id: int, session_id: int, student_ids: array<int, int>, status?: array<int, string>}  $request
     */
    public function saveAttendance($request)
    {
        try {
            DB::transaction(function () use ($request) {
                foreach ($request['student_ids'] as $student_id) {
                    $status = isset($request['status'][$student_id]) ? 'on' : 'off';

                    $todaysRecord = Attendance::where('student_id', $student_id)
                        ->where('class_id', $request['class_id'])
                        ->where('session_id', $request['session_id'])
                        ->whereDate('created_at', Carbon::today())
                        ->first();

                    if ($todaysRecord) {
                        $todaysRecord->update(['status' => $status]);

                        continue;
                    }

                    Attendance::create([
                        'status' => $status,
                        'class_id' => $request['class_id'],
                        'student_id' => $student_id,
                        'section_id' => $request['section_id'],
                        'course_id' => 0,
                        'session_id' => $request['session_id'],
                    ]);
                }
            });
        } catch (\Exception $e) {
            throw new \Exception('Failed to save attendance. '.$e->getMessage());
        }
    }

    /**
     * Today's register for a class.
     */
    public function getClassAttendance($class_id, $session_id)
    {
        try {
            return Attendance::with('student')
                ->where('class_id', $class_id)
                ->where('session_id', $session_id)
                ->whereDate('created_at', '=', Carbon::today())
                ->get();
        } catch (\Exception $e) {
            throw new \Exception('Failed to get attendances. '.$e->getMessage());
        }
    }

    public function getStudentAttendance($session_id, $student_id)
    {
        try {
            return Attendance::with(['section', 'course', 'schoolClass'])
                ->where('student_id', $student_id)
                ->where('session_id', $session_id)
                ->get();
        } catch (\Exception $e) {
            throw new \Exception('Failed to get attendances. '.$e->getMessage());
        }
    }
}
