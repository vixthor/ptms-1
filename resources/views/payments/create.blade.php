@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-currency-exchange"></i> Record Payment
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{route('payments.list')}}">Payments</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Record Payment</li>
                        </ol>
                    </nav>
                    @include('session-messages')

                    @if ($students->count() < 1)
                        <p class="text-danger">
                            <small><i class="bi bi-exclamation-diamond-fill me-2"></i> No students found for the current academic session yet. Add students before recording a payment.</small>
                        </p>
                    @endif

                    <div class="mb-4 form-card" style="max-width: 640px;">
                        <form action="{{route('payments.store')}}" method="POST">
                            @csrf
                            <input type="hidden" name="session_id" value="{{$current_school_session_id}}">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="student_id" class="form-label">Student<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                    <select class="form-select" id="student_id" name="student_id" required>
                                        <option value="" selected disabled>Please select a student</option>
                                        @foreach ($students as $promotion)
                                            <option value="{{$promotion->student->id}}" {{old('student_id') == $promotion->student->id ? 'selected' : ''}}>
                                                {{$promotion->student->first_name}} {{$promotion->student->last_name}}
                                                @if($promotion->section) — {{$promotion->section->section_name}} @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="category" class="form-label">Category<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                    <select class="form-select" id="category" name="category" required>
                                        @foreach ($categories as $category)
                                            <option value="{{$category}}" {{old('category') == $category ? 'selected' : ''}}>{{$category}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="amount" class="form-label">Amount<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                    <input type="number" step="0.01" min="0.01" class="form-control" id="amount" name="amount" placeholder="0.00" required value="{{old('amount')}}">
                                </div>
                                <div class="col-md-6">
                                    <label for="payment_method" class="form-label">Payment Method<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                    <select class="form-select" id="payment_method" name="payment_method" required>
                                        <option value="Cash" {{old('payment_method') == 'Cash' ? 'selected' : ''}}>Cash</option>
                                        <option value="Bank Transfer" {{old('payment_method') == 'Bank Transfer' ? 'selected' : ''}}>Bank Transfer</option>
                                        <option value="Card" {{old('payment_method') == 'Card' ? 'selected' : ''}}>Card</option>
                                        <option value="Mobile Money" {{old('payment_method') == 'Mobile Money' ? 'selected' : ''}}>Mobile Money</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="paid_for" class="form-label">Description<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                    <input type="text" class="form-control" id="paid_for" name="paid_for" placeholder="e.g. End of year celebration" required value="{{old('paid_for')}}">
                                </div>
                                <div class="col-md-6">
                                    <label for="payment_date" class="form-label">Payment Date<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                    <input type="date" class="form-control" id="payment_date" name="payment_date" required value="{{old('payment_date', date('Y-m-d'))}}">
                                </div>
                                <div class="col-12">
                                    <label for="note" class="form-label">Note</label>
                                    <textarea class="form-control" id="note" name="note" rows="3" placeholder="Optional note">{{old('note')}}</textarea>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Save Payment</button>
                                <a href="{{route('payments.list')}}" class="btn btn-outline-primary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
