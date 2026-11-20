<?php

namespace App\Http\Controllers;

use App\Http\Requests\SemesterStoreRequest;
use App\Interfaces\SemesterInterface;
use Illuminate\Http\Response;

class SemesterController extends Controller
{
    protected $semesterRepository;

    public function __construct(SemesterInterface $semesterRepository)
    {
        $this->semesterRepository = $semesterRepository;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(SemesterStoreRequest $request)
    {
        try {
            $this->semesterRepository->create($request->validated());

            return back()->with('status', 'Term creation was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }
}
