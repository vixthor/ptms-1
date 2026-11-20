<?php

namespace App\Repositories;

use App\Interfaces\CourseInterface;
use App\Models\Course;

class CourseRepository implements CourseInterface
{
    public function create($request)
    {
        try {
            Course::create($request);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create Subject. '.$e->getMessage());
        }
    }

    public function getAll($session_id)
    {
        return Course::where('session_id', $session_id)->get();
    }

    public function getByClassId($class_id)
    {
        return Course::where('class_id', $class_id)->get();
    }

    public function findById($course_id)
    {
        return Course::find($course_id);
    }

    public function update($request)
    {
        try {
            Course::find($request->course_id)->update([
                'course_name' => $request->course_name,
                'course_type' => $request->course_type,
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to update Subject. '.$e->getMessage());
        }
    }
}
