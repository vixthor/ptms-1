@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-4">
                <div class="col ps-4">
                    <div class="mb-3">
                        <h4 class="mb-1">Dashboard</h4>
                        <p class="text-secondary mb-0" style="font-size:.875rem;">Here's what's happening across the school today.</p>
                    </div>

                    @if(Auth::user()->role=="admin")
                    <div class="row g-3 dashboard">
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-3" style="width:46px;height:46px;background:var(--brand-50);color:var(--brand-600);font-size:1.25rem;">
                                        <i class="bi bi-person-lines-fill"></i>
                                    </span>
                                    <div>
                                        <div class="text-secondary" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.04em;font-weight:700;">Total Students</div>
                                        <div style="font-family:var(--font-display);font-size:1.5rem;font-weight:700;color:var(--ink-900);">{{$studentCount}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-3" style="width:46px;height:46px;background:var(--accent-100);color:var(--accent-500);font-size:1.25rem;">
                                        <i class="bi bi-person-workspace"></i>
                                    </span>
                                    <div>
                                        <div class="text-secondary" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.04em;font-weight:700;">Total Teachers</div>
                                        <div style="font-family:var(--font-display);font-size:1.5rem;font-weight:700;color:var(--ink-900);">{{$teacherCount}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-3" style="width:46px;height:46px;background:var(--info-100);color:var(--info-500);font-size:1.25rem;">
                                        <i class="bi bi-diagram-3"></i>
                                    </span>
                                    <div>
                                        <div class="text-secondary" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.04em;font-weight:700;">Total Classes</div>
                                        <div style="font-family:var(--font-display);font-size:1.5rem;font-weight:700;color:var(--ink-900);">{{ $classCount }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                        @if($studentCount > 0)
                        <div class="card mt-3">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="fw-semibold" style="font-size:.875rem;">Student Gender Split</span>
                                    <div class="d-flex align-items-center gap-3" style="font-size:.8125rem;">
                                        <span class="d-flex align-items-center gap-1 text-secondary"><span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:#0678c8;"></span>Male</span>
                                        <span class="d-flex align-items-center gap-1 text-secondary"><span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:#49a4fe;"></span>Female</span>
                                    </div>
                                </div>
                            @php
                            $maleStudentPercentage = round(($maleStudentsBySession/$studentCount), 2) * 100;
                            $maleStudentPercentageStyle = "style='background-color: #0678c8; width: $maleStudentPercentage%'";

                            $femaleStudentPercentage = round((($studentCount - $maleStudentsBySession)/$studentCount), 2) * 100;
                            $femaleStudentPercentageStyle = "style='background-color: #49a4fe; width: $femaleStudentPercentage%'";
                            @endphp
                                <div class="progress" style="height:10px;border-radius:999px;overflow:hidden;">
                                    <div class="progress-bar" role="progressbar" {!!$maleStudentPercentageStyle!!} aria-valuenow="{{$maleStudentPercentage}}" aria-valuemin="0" aria-valuemax="100"></div>
                                    <div class="progress-bar" role="progressbar" {!!$femaleStudentPercentageStyle!!} aria-valuenow="{{$femaleStudentPercentage}}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-1 text-secondary" style="font-size:.75rem;">
                                    <span>{{$maleStudentPercentage}}% Male</span>
                                    <span>{{$femaleStudentPercentage}}% Female</span>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endif

                    <div class="row g-3 align-items-md-stretch mt-1">
                        <div class="col-md-6">
                            <div class="p-4 text-white rounded-4 h-100" style="background:linear-gradient(135deg, var(--brand-500), var(--brand-800));">
                                <h3 class="text-white mb-2">Welcome to PTMS!</h3>
                                <p class="mb-0" style="opacity:.9;"><i class="bi bi-emoji-heart-eyes me-1"></i> Thanks for your love and support.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body d-flex flex-column justify-content-center">
                                    <h3 class="mb-2">Manage school better</h3>
                                    <p class="text-secondary mb-0"><i class="bi bi-lightning-fill me-1" style="color:var(--brand-500);"></i> with <a href="" style="text-decoration:none;font-weight:600;">PTMS</a> <i class="bi bi-lightning-fill ms-1" style="color:var(--brand-500);"></i></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-lg-6">
                            <div class="card h-100">
                                <div class="card-header bg-transparent"><i class="bi bi-calendar-event me-2" style="color:var(--brand-500);"></i> Events</div>
                                <div class="card-body text-dark">
                                    @include('components.events.event-calendar', ['editable' => 'false', 'selectable' => 'false'])
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card h-100">
                                <div class="card-header bg-transparent d-flex justify-content-between"><span><i class="bi bi-megaphone me-2" style="color:var(--brand-500);"></i> Notices</span></div>
                                <div class="card-body p-0 text-dark">
                                    <div>
                                        @isset($notices)
                                        <div class="accordion accordion-flush" id="noticeAccordion">
                                            @foreach ($notices as $notice)
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="flush-heading{{$notice->id}}">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse{{$notice->id}}" aria-expanded={{($loop->first)?"true":"false"}} aria-controls="flush-collapse{{$notice->id}}">
                                                        Published at: {{$notice->created_at}}
                                                    </button>
                                                </h2>
                                                <div id="flush-collapse{{$notice->id}}" class="accordion-collapse collapse {{($loop->first)?"show":"hide"}}" aria-labelledby="flush-heading{{$notice->id}}" data-bs-parent="#noticeAccordion">
                                                    <div class="accordion-body overflow-auto">{!! \Mews\Purifier\Facades\Purifier::clean($notice->notice)!!}</div>
                                                </div>
                                            </div>
                                            @endforeach
                                            @endisset
                                            @if(count($notices) < 1)
                                                <div class="p-4 text-center text-secondary">
                                                    <i class="bi bi-megaphone d-block mb-2" style="font-size:1.5rem;"></i>
                                                    No notices yet
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
