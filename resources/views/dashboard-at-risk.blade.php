<div class="card h-100">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-start gap-2">
        <div>
            <i class="bi bi-shield-exclamation me-2" style="color:var(--danger-500);"></i> Pupils at risk
            <div class="dash-meta">Two or more warning signs: attendance under {{ \App\Services\DashboardService::ATTENDANCE_RISK_RATE }}%, average under {{ \App\Services\DashboardService::PASS_MARK }}%{{ $includesFees ? ', tuition unpaid' : '' }}</div>
        </div>
        @if($atRisk['total'] > 0)
            <span class="badge" style="background:var(--danger-100);color:var(--danger-500);">{{ $atRisk['total'] }}</span>
        @endif
    </div>
    <div class="card-body p-0">
        @if($atRisk['pupils']->isEmpty())
            <div class="p-4 text-center text-secondary">
                <i class="bi bi-shield-check d-block mb-2" style="font-size:1.5rem;"></i>
                No pupils at risk
            </div>
        @else
            <ul class="dash-list">
                @foreach($atRisk['pupils'] as $pupil)
                    <li class="flex-wrap">
                        <div>
                            <a href="{{ route('student.profile', $pupil['student']->id) }}" class="fw-semibold text-decoration-none">{{ $pupil['student']->first_name }} {{ $pupil['student']->last_name }}</a>
                            <div class="dash-meta">{{ $pupil['class_name'] }}</div>
                        </div>
                        <div class="d-flex flex-wrap gap-1 justify-content-end">
                            @foreach($pupil['reasons'] as $reason)
                                <span class="badge" style="background:var(--warning-100);color:var(--warning-500);">{{ $reason }}</span>
                            @endforeach
                        </div>
                    </li>
                @endforeach
            </ul>
            @if($atRisk['total'] > $atRisk['pupils']->count())
                <div class="dash-meta px-3 pb-3">and {{ $atRisk['total'] - $atRisk['pupils']->count() }} more</div>
            @endif
        @endif
    </div>
</div>
