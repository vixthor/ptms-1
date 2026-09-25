@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-currency-exchange"></i> My Payments
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">My Payments</li>
                        </ol>
                    </nav>
                    @include('session-messages')

                    @if (!$paystackConfigured)
                        <div class="d-flex align-items-start gap-2 rounded-3 px-3 py-2 mb-3" style="background:var(--warning-100);color:var(--warning-500);font-size:.875rem;font-weight:500;">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span>Online payments aren't set up yet. Please check back later or contact the school office.</span>
                        </div>
                    @endif

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-3" style="width:46px;height:46px;background:var(--accent-100);color:var(--accent-500);font-size:1.25rem;">
                                        <i class="bi bi-cash-stack"></i>
                                    </span>
                                    <div>
                                        <div class="text-secondary" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.04em;font-weight:700;">Total Paid</div>
                                        <div style="font-family:var(--font-display);font-size:1.4rem;font-weight:700;color:var(--ink-900);">{{ number_format($totalPaid, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-stretch">
                            <a href="{{ route('student.payments.create') }}" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-credit-card"></i> Make a Payment
                            </a>
                        </div>
                    </div>

                    <div class="bg-white border shadow-sm p-3 mt-4">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Date</th>
                                    <th scope="col">Category</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($payments as $payment)
                                <tr>
                                    <td>{{ \Illuminate\Support\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $payment->category }}</span></td>
                                    <td>{{ $payment->paid_for }}</td>
                                    <td>
                                        @if($payment->status === 'success')
                                            <span class="badge" style="background:var(--success-100);color:var(--success-500);">Success</span>
                                        @elseif($payment->status === 'pending')
                                            <span class="badge" style="background:var(--warning-100);color:var(--warning-500);">Pending</span>
                                        @else
                                            <span class="badge" style="background:var(--danger-100);color:var(--danger-500);">Failed</span>
                                        @endif
                                    </td>
                                    <td class="fw-semibold">{{ number_format($payment->amount, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-4">
                                        <i class="bi bi-currency-exchange d-block mb-2" style="font-size:1.5rem;"></i>
                                        You haven't made any payments yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
