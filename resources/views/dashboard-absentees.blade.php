<div class="card h-100">
    <div class="card-header bg-transparent">
        <i class="bi bi-exclamation-triangle me-2" style="color:var(--warning-500);"></i> Pupils missing school
        <div class="dash-meta">{{ \App\Services\DashboardService::ABSENCE_THRESHOLD }}+ absences in the last {{ \App\Services\DashboardService::ABSENCE_WINDOW_DAYS }} days</div>
    </div>
    <div class="card-body p-0">
        @if($absentees->isEmpty())
            <div class="p-4 text-center text-secondary">
                <i class="bi bi-emoji-smile d-block mb-2" style="font-size:1.5rem;"></i>
                No pupils flagged
            </div>
        @else
            <ul class="dash-list">
                @foreach($absentees as $absentee)
                    <li>
                        <div>
                            <a href="{{ route('student.attendance', $absentee['student']->id) }}" class="fw-semibold text-decoration-none">{{ $absentee['student']->first_name }} {{ $absentee['student']->last_name }}</a>
                            <div class="dash-meta">{{ $absentee['class_name'] }}</div>
                        </div>
                        <span class="badge" style="background:var(--danger-100);color:var(--danger-500);">{{ $absentee['absences'] }} days</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
