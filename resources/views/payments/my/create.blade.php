@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-credit-card"></i> Make a Payment
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{route('student.payments.list')}}">My Payments</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Make a Payment</li>
                        </ol>
                    </nav>
                    @include('session-messages')

                    @if (!$paystackConfigured)
                        <div class="d-flex align-items-start gap-2 rounded-3 px-3 py-2 mb-3" style="background:var(--warning-100);color:var(--warning-500);font-size:.875rem;font-weight:500;">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span>Online payments aren't set up yet. Please check back later or contact the school office.</span>
                        </div>
                    @endif

                    <div class="mb-4 form-card" style="max-width: 560px;">
                        <form action="{{route('student.payments.initialize')}}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="category" class="form-label">What are you paying for?<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                    <select class="form-select" id="category" name="category" required>
                                        @foreach ($categories as $category)
                                            <option value="{{$category}}" {{old('category') == $category ? 'selected' : ''}}>{{$category}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="paid_for" class="form-label">Description<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                    <input type="text" class="form-control" id="paid_for" name="paid_for" placeholder="e.g. End of year celebration" required value="{{old('paid_for')}}">
                                </div>
                                <div class="col-12">
                                    <label for="amount" class="form-label">Amount<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                    <div class="input-group">
                                        <span class="input-group-text">₦</span>
                                        <input type="number" step="0.01" min="1" class="form-control" id="amount" name="amount" placeholder="0.00" required value="{{old('amount')}}">
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary" {{ $paystackConfigured ? '' : 'disabled' }}>
                                    <i class="bi bi-lock-fill"></i> Pay Securely with Paystack
                                </button>
                                <a href="{{route('student.payments.list')}}" class="btn btn-outline-primary">Cancel</a>
                            </div>
                            <p class="text-secondary mt-3 mb-0" style="font-size:.8125rem;">
                                <i class="bi bi-shield-check me-1"></i> You'll be redirected to Paystack's secure checkout. We never see or store your card details.
                            </p>
                        </form>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
