@extends('public.layout')

@section('title', 'Admissions · ' . $siteSettings['site_name'])
@section('meta_description', 'How to apply to ' . $siteSettings['site_name'])

@section('content')

<x-public.page-hero
    eyebrow="Admissions"
    :title="'Joining ' . $siteSettings['site_name'] . ' is simple.'"
    subtitle="Here's exactly what to expect, from your first inquiry to your child's first day."
>
    <a href="{{ route('public.contact') }}" class="btn-site btn-site-framed"><strong>Start an inquiry</strong></a>
    <a href="#fees" class="btn-site btn-site-framed">View fees</a>
</x-public.page-hero>

<section class="home-section">
    <div class="site-container">
        <div class="page-heading">
            <h2 class="home-title">The process</h2>
            <span class="home-rule"></span>
        </div>
        @if ($steps->count() > 0)
        <ol class="steps-list">
            @foreach ($steps as $step)
            <li>
                <div>
                    <h4>{{ $step->title }}</h4>
                    <p>{{ $step->description }}</p>
                </div>
            </li>
            @endforeach
        </ol>
        @else
        <p class="page-empty">Admission steps will be listed here soon.</p>
        @endif
    </div>
</section>

<section class="home-section" id="fees">
    <div class="site-container">
        <div class="page-heading">
            <h2 class="home-title">Fees &amp; term dates</h2>
            <span class="home-rule"></span>
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
                        <td class="note">{{ $fee->note }}</td>
                        <td class="amount">{{ number_format($fee->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="page-empty">Fee information will be published here soon.</p>
        @endif
    </div>
</section>

@endsection
