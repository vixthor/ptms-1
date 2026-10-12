@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-cash-coin"></i> Fees
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Fees</li>
                        </ol>
                    </nav>
                    @include('session-messages')

                    <div class="mb-4 form-card" style="max-width: 720px;">
                        <h6 class="mb-3">Add a fee</h6>
                        <form action="{{ route('website-admin.fee-items.store') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="program_name" class="form-label">Program / Class<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                    <input type="text" class="form-control" id="program_name" name="program_name" placeholder="e.g. Nursery" required value="{{old('program_name')}}">
                                </div>
                                <div class="col-md-3">
                                    <label for="amount" class="form-label">Amount<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="amount" name="amount" required value="{{old('amount')}}">
                                </div>
                                <div class="col-md-3">
                                    <label for="frequency" class="form-label">Frequency</label>
                                    <select class="form-select" id="frequency" name="frequency">
                                        <option value="Termly" {{old('frequency') == 'Termly' ? 'selected' : ''}}>Termly</option>
                                        <option value="Annually" {{old('frequency') == 'Annually' ? 'selected' : ''}}>Annually</option>
                                        <option value="One-time" {{old('frequency') == 'One-time' ? 'selected' : ''}}>One-time</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="sort_order" class="form-label">Order</label>
                                    <input type="number" class="form-control" id="sort_order" name="sort_order" value="{{old('sort_order', 0)}}">
                                </div>
                                <div class="col-12">
                                    <label for="note" class="form-label">Note <span class="text-secondary">(optional)</span></label>
                                    <input type="text" class="form-control" id="note" name="note" placeholder="e.g. Includes books and uniform" value="{{old('note')}}">
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add Fee</button>
                            </div>
                        </form>
                    </div>

                    <div class="bg-white border shadow-sm p-3">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Program</th>
                                    <th scope="col">Amount</th>
                                    <th scope="col">Frequency</th>
                                    <th scope="col">Note</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($fees as $fee)
                                <tr>
                                    <td>{{$fee->program_name}}</td>
                                    <td class="fw-semibold">{{ number_format($fee->amount, 2) }}</td>
                                    <td><span class="badge bg-light text-dark border">{{$fee->frequency}}</span></td>
                                    <td>{{$fee->note}}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="event.preventDefault(); if(confirm('Delete this fee?')) document.getElementById('delete-fee-{{$fee->id}}').submit();"><i class="bi bi-trash2"></i> Delete</button>
                                        <form id="delete-fee-{{$fee->id}}" action="{{ route('website-admin.fee-items.destroy', ['feeItem' => $fee->id]) }}" method="POST" class="d-none">
                                            @csrf
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-4">No fees added yet.</td>
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
