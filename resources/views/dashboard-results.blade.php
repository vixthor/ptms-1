<div class="card h-100">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-start gap-2">
        <div>
            <i class="bi bi-journal-check me-2" style="color:var(--brand-500);"></i> Results &middot; {{ $resultsTerm->semester_name }}
            <div class="dash-meta">Scores submitted &rarr; teacher comments &rarr; headteacher signature</div>
        </div>
        @if(Auth::user()->canManageAcademics())
            <a href="{{ route('report.card.mass-sign.form', ['semester_id' => $resultsTerm->id]) }}" class="btn btn-sm btn-outline-primary text-nowrap"><i class="bi bi-pen"></i> Sign reports</a>
        @endif
    </div>
    <div class="card-body p-0">
        @if($resultProgress->isEmpty())
            <div class="p-4 text-center text-secondary">
                <i class="bi bi-journal d-block mb-2" style="font-size:1.5rem;"></i>
                No classes with pupils this session
            </div>
        @else
            <ul class="dash-list">
                @foreach($resultProgress as $class)
                    @php
                        $steps = [
                            ['label' => 'Scores', 'done' => $class['subjects_scored'], 'of' => $class['subjects'], 'unit' => 'subjects'],
                            ['label' => 'Comments', 'done' => $class['commented'], 'of' => $class['pupils'], 'unit' => 'pupils'],
                            ['label' => 'Signed', 'done' => $class['signed'], 'of' => $class['pupils'], 'unit' => 'pupils'],
                        ];
                    @endphp
                    <li class="d-block">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold" style="color:var(--ink-900);">{{ $class['class_name'] }}</span>
                            @if($class['complete'])
                                <span class="badge" style="background:var(--success-100);color:var(--success-500);">Ready</span>
                            @elseif($class['subjects'] === 0)
                                <span class="badge" style="background:var(--ink-100);color:var(--ink-500);">No subjects set up</span>
                            @endif
                        </div>
                        <div class="row g-2">
                            @foreach($steps as $step)
                                @php $isDone = $step['of'] > 0 && $step['done'] >= $step['of']; @endphp
                                <div class="col-4">
                                    <div class="dash-meta d-flex justify-content-between">
                                        <span>{{ $step['label'] }}</span>
                                        <span class="fw-semibold" style="color:{{ $isDone ? 'var(--success-500)' : 'var(--ink-700)' }};">{{ $step['done'] }}/{{ $step['of'] }}</span>
                                    </div>
                                    <div class="dash-bar mt-1" title="{{ $step['done'] }} of {{ $step['of'] }} {{ $step['unit'] }}">
                                        <span style="width: {{ $step['of'] > 0 ? min(100, round($step['done'] / $step['of'] * 100)) : 0 }}%;{{ $isDone ? 'background:var(--success-500);' : '' }}"></span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
