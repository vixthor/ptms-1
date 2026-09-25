@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-currency-exchange"></i> Payments
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Payments</li>
                        </ol>
                    </nav>
                    @include('session-messages')

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-3" style="width:46px;height:46px;background:var(--accent-100);color:var(--accent-500);font-size:1.25rem;">
                                        <i class="bi bi-cash-stack"></i>
                                    </span>
                                    <div>
                                        <div class="text-secondary" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.04em;font-weight:700;">Total Collected</div>
                                        <div style="font-family:var(--font-display);font-size:1.4rem;font-weight:700;color:var(--ink-900);">{{ number_format($totalAmount, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-3" style="width:46px;height:46px;background:var(--brand-50);color:var(--brand-600);font-size:1.25rem;">
                                        <i class="bi bi-receipt"></i>
                                    </span>
                                    <div>
                                        <div class="text-secondary" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.04em;font-weight:700;">Payments Recorded</div>
                                        <div style="font-family:var(--font-display);font-size:1.4rem;font-weight:700;color:var(--ink-900);">{{ $payments->count() }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-stretch">
                            <a href="{{ route('payments.create') }}" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-plus-circle"></i> Record Payment
                            </a>
                        </div>
                    </div>

                    <h6>Filter list by:</h6>
                    <div class="mb-4 mt-4">
                        <form class="row g-2 align-items-end" action="{{route('payments.list')}}" method="GET">
                            <div class="col-md-4">
                                <label class="form-label">Student</label>
                                <select class="form-select" name="student_id">
                                    <option value="">All students</option>
                                    @foreach ($students as $promotion)
                                        <option value="{{$promotion->student->id}}" {{(request()->query('student_id') == $promotion->student->id)?'selected':''}}>{{$promotion->student->first_name}} {{$promotion->student->last_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category">
                                    <option value="">All categories</option>
                                    @foreach ($categories as $category)
                                        <option value="{{$category}}" {{(request()->query('category') == $category)?'selected':''}}>{{$category}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-outline-primary"><i class="bi bi-funnel"></i> Filter</button>
                            </div>
                        </form>

                        <div class="bg-white border shadow-sm p-3 mt-4">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">Date</th>
                                        <th scope="col">Student</th>
                                        <th scope="col">Category</th>
                                        <th scope="col">Description</th>
                                        <th scope="col">Method</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Amount</th>
                                        <th scope="col">Recorded By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($payments as $payment)
                                    <tr>
                                        <td>{{ \Illuminate\Support\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                                        <td>{{ $payment->student->first_name ?? '—' }} {{ $payment->student->last_name ?? '' }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $payment->category }}</span></td>
                                        <td>{{ $payment->paid_for }}</td>
                                        <td>{{ $payment->payment_method }} @if($payment->gateway === 'paystack')<i class="bi bi-credit-card-2-front ms-1" title="Paid via Paystack"></i>@endif</td>
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
                                        <td>{{ $payment->recordedBy->first_name ?? '—' }} {{ $payment->recordedBy->last_name ?? '' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-secondary py-4">
                                            <i class="bi bi-currency-exchange d-block mb-2" style="font-size:1.5rem;"></i>
                                            No payments recorded yet.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
