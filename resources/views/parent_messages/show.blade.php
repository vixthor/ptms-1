@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3"><i class="bi bi-chat-dots"></i> Message to {{ $message->audienceLabel() }}</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('parent-messages.create') }}">Message Parents</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Message</li>
                        </ol>
                    </nav>

                    @if($message->status === \App\Models\ParentMessage::STATUS_SENT)
                        <div class="alert alert-success"><i class="bi bi-check2-circle me-1"></i> SMS sent to {{ $message->recipients_count }} {{ Str::plural('number', $message->recipients_count) }}.</div>
                    @elseif($message->status === \App\Models\ParentMessage::STATUS_FAILED)
                        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i> The SMS could not be sent. {{ $message->error }}</div>
                    @else
                        <div class="alert alert-info"><i class="bi bi-whatsapp me-1"></i> Open each family's chat below and press send in WhatsApp. Tick them off as you go.</div>
                    @endif

                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="dash-meta mb-1">{{ optional($message->sender)->first_name }} {{ optional($message->sender)->last_name }} &middot; {{ $message->created_at->format('j M Y, g:ia') }}</div>
                            <div style="white-space:pre-line;">{{ $message->body }}</div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-transparent">{{ $message->recipients_count }} {{ Str::plural('family', $message->recipients_count) }}</div>
                        <div class="card-body p-0">
                            <ul class="dash-list">
                                @foreach($message->recipients as $recipient)
                                    <li>
                                        <div>
                                            <div class="fw-semibold" style="color:var(--ink-900);">{{ $recipient['name'] }}</div>
                                            <div class="dash-meta">+{{ $recipient['phone'] }}</div>
                                        </div>
                                        @if($message->channel === \App\Models\ParentMessage::CHANNEL_WHATSAPP)
                                            <a href="{{ \App\Services\ParentMessagingService::whatsappLink($recipient['phone'], $message->body) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success" onclick="this.classList.replace('btn-outline-success','btn-success'); this.innerHTML='<i class=&quot;bi bi-check2&quot;></i> Opened';">
                                                <i class="bi bi-whatsapp"></i> Open chat
                                            </a>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
