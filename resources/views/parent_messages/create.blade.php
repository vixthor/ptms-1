@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3"><i class="bi bi-chat-dots"></i> Message Parents</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Message Parents</li>
                        </ol>
                    </nav>
                    @include('session-messages')

                    @if($errors->any())
                        <div class="alert alert-danger" role="alert">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <div class="row g-4">
                        <div class="col-lg-7">
                            <div class="mb-4 form-card">
                                <form action="{{ route('parent-messages.store') }}" method="POST" id="parentMessageForm">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="audience" class="form-label">Send to<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                            <select class="form-select" id="audience" name="audience" required>
                                                @foreach($audiences as $value => $label)
                                                    <option value="{{ $value }}" @selected(old('audience', $selectedAudience ?? array_key_first($audiences)) === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12" id="classField">
                                            <label for="class_id" class="form-label">Class</label>
                                            <select class="form-select" id="class_id" name="class_id">
                                                @foreach($classes as $schoolClass)
                                                    <option value="{{ $schoolClass->id }}" @selected((int) old('class_id', $selectedClassId) === $schoolClass->id)>{{ $schoolClass->class_name }}</option>
                                                @endforeach
                                            </select>
                                            @if($classes->isEmpty())
                                                <div class="form-text text-danger">You are not class teacher of any class this session.</div>
                                            @endif
                                        </div>
                                        <div class="col-12">
                                            <label for="body" class="form-label">Message<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                            <textarea class="form-control" id="body" name="body" rows="5" maxlength="480" required placeholder="Dear parent, …">{{ old('body') }}</textarea>
                                            <div class="form-text d-flex justify-content-between">
                                                <span>Sign off with the school's name so parents know who it's from.</span>
                                                <span><span id="bodyCount">0</span>/480 &middot; <span id="smsPages">0</span> SMS</span>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <span class="form-label d-block">Send by</span>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="channel" id="channelWhatsapp" value="{{ \App\Models\ParentMessage::CHANNEL_WHATSAPP }}" @checked(old('channel', $smsConfigured ? 'sms' : 'whatsapp') === 'whatsapp')>
                                                <label class="form-check-label" for="channelWhatsapp">WhatsApp <span class="text-secondary">&mdash; free; you open each family's chat and press send</span></label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="channel" id="channelSms" value="{{ \App\Models\ParentMessage::CHANNEL_SMS }}" @checked(old('channel', $smsConfigured ? 'sms' : 'whatsapp') === 'sms') @disabled(! $smsConfigured)>
                                                <label class="form-check-label" for="channelSms">SMS <span class="text-secondary">&mdash; {{ $smsConfigured ? 'sent to every number at once (uses SMS credit)' : 'not set up yet: add Termii keys to .env to enable' }}</span></label>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="mt-4 btn btn-primary" @disabled($classes->isEmpty() && ! array_key_exists(\App\Models\ParentMessage::AUDIENCE_SCHOOL, $audiences))><i class="bi bi-send"></i> Continue</button>
                                </form>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="card">
                                <div class="card-header bg-transparent"><i class="bi bi-clock-history me-2" style="color:var(--brand-500);"></i> Recent messages</div>
                                <div class="card-body p-0">
                                    @if($recentMessages->isEmpty())
                                        <div class="p-4 text-center text-secondary">No messages sent this session</div>
                                    @else
                                        <ul class="dash-list">
                                            @foreach($recentMessages as $message)
                                                <li>
                                                    <div style="min-width:0;">
                                                        <a href="{{ route('parent-messages.show', $message) }}" class="fw-semibold text-decoration-none d-block text-truncate">{{ Str::limit($message->body, 50) }}</a>
                                                        <div class="dash-meta">{{ $message->audienceLabel() }} &middot; {{ $message->recipients_count }} {{ Str::plural('family', $message->recipients_count) }} &middot; {{ $message->created_at->diffForHumans() }}</div>
                                                    </div>
                                                    <span class="badge text-uppercase" style="background:var(--ink-100);color:var(--ink-500);">{{ $message->channel }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
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
<script>
    (function () {
        var audience = document.getElementById('audience');
        var classField = document.getElementById('classField');
        var body = document.getElementById('body');

        function toggleClassField() {
            classField.style.display = audience.value === '{{ \App\Models\ParentMessage::AUDIENCE_CLASS }}' ? '' : 'none';
        }

        function countCharacters() {
            var length = body.value.length;
            document.getElementById('bodyCount').textContent = length;
            document.getElementById('smsPages').textContent = length === 0 ? 0 : (length <= 160 ? 1 : Math.ceil(length / 153));
        }

        audience.addEventListener('change', toggleClassField);
        body.addEventListener('input', countCharacters);
        toggleClassField();
        countCharacters();
    })();
</script>
@endsection
