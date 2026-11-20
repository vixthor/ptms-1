@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3"><i class="bi bi-mortarboard"></i> Common Entrance</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Common Entrance</li>
                        </ol>
                    </nav>
                    @include('session-messages')

                    @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    @if(! $schoolClass)
                        <p class="text-secondary">No classes in this session yet.</p>
                    @else
                        <form method="GET" action="{{ route('common-entrance.index') }}" class="d-flex flex-wrap align-items-end gap-2 mb-3">
                            <div>
                                <label for="class_id" class="form-label mb-1" style="font-size:.8125rem;">Final-year class</label>
                                <select class="form-select form-select-sm" id="class_id" name="class_id" onchange="this.form.submit()">
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" @selected($class->id === $schoolClass->id)>{{ $class->class_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </form>

                        <div class="row g-3 mb-3 dashboard">
                            @foreach([['Pupils', $summary['pupils'], 'bi-people', 'brand'], ['Registered', $summary['registered'], 'bi-card-checklist', 'info'], ['Results in', $summary['scored'], 'bi-123', 'warning'], ['Secondary school found', $summary['placed'], 'bi-building-check', 'success']] as [$label, $value, $icon, $tone])
                                <div class="col-6 col-xl-3">
                                    <div class="card h-100">
                                        <div class="card-body d-flex align-items-center gap-3">
                                            <span class="dash-icon" style="background:var(--{{ $tone }}-{{ $tone === 'brand' ? '50' : '100' }});color:var(--{{ $tone }}-500);"><i class="bi {{ $icon }}"></i></span>
                                            <div>
                                                <div class="dash-label">{{ $label }}</div>
                                                <div class="dash-value">{{ $value }}@if($label !== 'Pupils')<span class="dash-meta fw-normal"> / {{ $summary['pupils'] }}</span>@endif</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($pupils->isEmpty())
                            <p class="text-secondary">No pupils in {{ $schoolClass->class_name }} this session.</p>
                        @else
                            <form action="{{ route('common-entrance.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="class_id" value="{{ $schoolClass->id }}">
                                <div class="card mb-3">
                                    <div class="table-responsive">
                                        <table class="table mb-0 align-middle" style="font-size:.875rem;">
                                            <thead>
                                                <tr>
                                                    <th class="ps-3">Pupil</th>
                                                    <th style="min-width:160px;">Exam number</th>
                                                    <th style="min-width:100px;">Score</th>
                                                    <th style="min-width:220px;">Secondary school offered</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($pupils as $pupil)
                                                    @php $candidate = $candidates->get($pupil->id); @endphp
                                                    <tr>
                                                        <td class="ps-3">
                                                            <div class="fw-semibold">{{ $pupil->first_name }} {{ $pupil->last_name }}</div>
                                                            @if($candidate && $candidate->isRegistered())
                                                                <span class="dash-meta"><i class="bi bi-check2-circle" style="color:var(--success-500);"></i> Registered</span>
                                                            @else
                                                                <span class="dash-meta">Not registered</span>
                                                            @endif
                                                        </td>
                                                        <td><input type="text" class="form-control form-control-sm" name="candidates[{{ $pupil->id }}][registration_number]" value="{{ old('candidates.'.$pupil->id.'.registration_number', optional($candidate)->registration_number) }}" aria-label="Exam number for {{ $pupil->first_name }}"></td>
                                                        <td><input type="number" step="0.01" min="0" max="999" class="form-control form-control-sm" name="candidates[{{ $pupil->id }}][score]" value="{{ old('candidates.'.$pupil->id.'.score', optional($candidate)->score) }}" aria-label="Score for {{ $pupil->first_name }}"></td>
                                                        <td><input type="text" class="form-control form-control-sm" name="candidates[{{ $pupil->id }}][admitted_school]" value="{{ old('candidates.'.$pupil->id.'.admitted_school', optional($candidate)->admitted_school) }}" placeholder="e.g. FGC Ijanikin" aria-label="Secondary school for {{ $pupil->first_name }}"></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary mb-4"><i class="bi bi-check2"></i> Save</button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
