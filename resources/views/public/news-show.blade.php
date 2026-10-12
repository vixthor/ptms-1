@extends('public.layout')

@section('title', 'News · ' . $siteSettings['site_name'])

@section('content')

<section class="site-section" style="padding-top:3rem;">
    <div class="site-container" style="max-width:720px;">
        <a href="{{ route('public.news') }}" style="font-size:.875rem;font-weight:600;"><i class="bi bi-arrow-left"></i> Back to News</a>
        <div class="mt-3" style="font-size:.8125rem;color:var(--ink-500);font-weight:600;text-transform:uppercase;letter-spacing:.05em;">{{ $notice->created_at->format('d F Y') }}</div>
        <div class="mt-3" style="font-size:1.0625rem;">
            {!! \Mews\Purifier\Facades\Purifier::clean($notice->notice) !!}
        </div>
    </div>
</section>

@endsection
