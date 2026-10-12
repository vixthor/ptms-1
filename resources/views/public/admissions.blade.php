@extends('public.layout')

@section('title', 'Admissions · ' . $siteSettings['site_name'])
@section('meta_description', 'How to apply to ' . $siteSettings['site_name'])

@section('content')

<section class="site-hero">
    <div class="site-container">
        <div class="hero-inner">
            <span class="eyebrow">Admissions</span>
            <h1>Joining {{ $siteSettings['site_name'] }} is simple.</h1>
            <p class="lead">Here's exactly what to expect, from your first inquiry to your child's first day.</p>
            <div class="hero-actions">
                <a href="{{ route('public.contact') }}" class="btn-site btn-site-accent">Start an Inquiry <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>

<section class="site-section">
    <div class="site-container" style="max-width:720px;">
        <div class="section-heading">
            <h2>The process</h2>
        </div>
        @if ($steps->count() > 0)
        <ul class="steps-list">
            @foreach ($steps as $step)
            <li>
                <div>
                    <h4>{{ $step->title }}</h4>
                    <p>{{ $step->description }}</p>
                </div>
            </li>
            @endforeach
        </ul>
        @else
        <p class="text-secondary">Admission steps will be listed here soon.</p>
        @endif
    </div>
</section>

<section class="site-section alt">
    <div class="site-container">
        <div class="section-heading">
            <h2>Fees</h2>
            <p>Transparent, per-term pricing by program.</p>
        </div>
        @if ($fees->count() > 0)
        <div class="fee-table-wrap">
            <table class="fee-table">
                <thead>
                    <tr>
                        <th>Program</th>
                        <th>Frequency</th>
                        <th>Note</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($fees as $fee)
                    <tr>
                        <td>{{ $fee->program_name }}</td>
                        <td>{{ $fee->frequency }}</td>
                        <td class="text-secondary">{{ $fee->note }}</td>
                        <td class="amount">{{ number_format($fee->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-secondary">Fee information will be published here soon.</p>
        @endif
    </div>
</section>

@endsection
