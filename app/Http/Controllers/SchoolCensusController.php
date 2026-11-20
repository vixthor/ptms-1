<?php

namespace App\Http\Controllers;

use App\Interfaces\SchoolSessionInterface;
use App\Services\SchoolCensusService;
use App\Traits\SchoolSession;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Enrolment by class, sex and age for the Annual School Census return.
 */
class SchoolCensusController extends Controller
{
    use SchoolSession;

    public function __construct(protected SchoolSessionInterface $schoolSessionRepository) {}

    public function index(Request $request, SchoolCensusService $census): View
    {
        abort_unless($request->user()->canManageStudentsAndTeachers(), 403);

        return view('reports.census', ['report' => $census->report((int) $this->getSchoolCurrentSession())]);
    }

    public function download(Request $request, SchoolCensusService $census): StreamedResponse
    {
        abort_unless($request->user()->canManageStudentsAndTeachers(), 403);

        $report = $census->report((int) $this->getSchoolCurrentSession());
        $rows = $census->csvRows($report);

        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'w');

            foreach ($rows as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        }, 'school-census-'.$report['as_at']->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }
}
