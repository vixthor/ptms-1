@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3"><i class="bi bi-clipboard-data"></i> School Census</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">School Census</li>
                        </ol>
                    </nav>

                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
                        <p class="text-secondary mb-0">Enrolment by class, sex and age as at {{ $report['as_at']->format('j F Y') }}, ready for the Annual School Census return.</p>
                        <a href="{{ route('reports.census.download') }}" class="btn btn-primary"><i class="bi bi-download"></i> Download CSV</a>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header bg-transparent">Pupils</div>
                        <div class="card-body p-0">
                            @if($report['classes']->isEmpty())
                                <div class="p-4 text-center text-secondary">No pupils enrolled in this session</div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0 align-middle text-center" style="font-size:.875rem;">
                                        <thead>
                                            <tr>
                                                <th class="text-start ps-3">Class</th>
                                                <th>Boys</th>
                                                <th>Girls</th>
                                                <th>Total</th>
                                                @foreach(\App\Services\SchoolCensusService::AGE_COLUMNS as $age)
                                                    <th class="text-secondary fw-normal">{{ $age }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($report['classes'] as $row)
                                                <tr>
                                                    <td class="text-start ps-3">{{ $row['class_name'] }}</td>
                                                    <td>{{ $row['boys'] }}</td>
                                                    <td>{{ $row['girls'] }}</td>
                                                    <td class="fw-semibold">{{ $row['total'] }}</td>
                                                    @foreach($row['ages'] as $count)
                                                        <td class="{{ $count === 0 ? 'text-secondary' : '' }}">{{ $count }}</td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="fw-semibold">
                                                <td class="text-start ps-3">All classes</td>
                                                <td>{{ $report['totals']['boys'] }}</td>
                                                <td>{{ $report['totals']['girls'] }}</td>
                                                <td>{{ $report['totals']['total'] }}</td>
                                                @foreach($report['totals']['ages'] as $count)
                                                    <td>{{ $count }}</td>
                                                @endforeach
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($report['totals']['ages']['Unknown'] > 0)
                        <p class="dash-meta"><i class="bi bi-info-circle me-1"></i> {{ $report['totals']['ages']['Unknown'] }} {{ Str::plural('pupil', $report['totals']['ages']['Unknown']) }} {{ $report['totals']['ages']['Unknown'] === 1 ? 'has' : 'have' }} no date of birth on file. Add it on the pupil's record so they're counted by age.</p>
                    @endif

                    <div class="card mb-4" style="max-width:420px;">
                        <div class="card-header bg-transparent">Teachers</div>
                        <div class="card-body d-flex justify-content-between text-center">
                            <div><div class="dash-label">Male</div><div class="dash-value">{{ $report['teachers']['male'] }}</div></div>
                            <div><div class="dash-label">Female</div><div class="dash-value">{{ $report['teachers']['female'] }}</div></div>
                            <div><div class="dash-label">Total</div><div class="dash-value">{{ $report['teachers']['total'] }}</div></div>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
