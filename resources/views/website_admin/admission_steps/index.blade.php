@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-list-ol"></i> Admission Steps
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Admission Steps</li>
                        </ol>
                    </nav>
                    @include('session-messages')

                    <div class="mb-4 form-card" style="max-width: 640px;">
                        <h6 class="mb-3">Add a step</h6>
                        <form action="{{ route('website-admin.admission-steps.store') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label for="title" class="form-label">Title<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                    <input type="text" class="form-control" id="title" name="title" placeholder="e.g. Submit an inquiry" required value="{{old('title')}}">
                                </div>
                                <div class="col-md-4">
                                    <label for="sort_order" class="form-label">Order</label>
                                    <input type="number" class="form-control" id="sort_order" name="sort_order" value="{{old('sort_order', 0)}}">
                                </div>
                                <div class="col-12">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="2">{{old('description')}}</textarea>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add Step</button>
                            </div>
                        </form>
                    </div>

                    <div class="bg-white border shadow-sm p-3">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Title</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($steps as $step)
                                <tr>
                                    <td>{{$step->sort_order}}</td>
                                    <td>{{$step->title}}</td>
                                    <td style="white-space:normal;max-width:420px;">{{$step->description}}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#edit-step-{{$step->id}}"><i class="bi bi-pen"></i> Edit</button>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="event.preventDefault(); if(confirm('Delete this step?')) document.getElementById('delete-step-{{$step->id}}').submit();"><i class="bi bi-trash2"></i> Delete</button>
                                            <form id="delete-step-{{$step->id}}" action="{{ route('website-admin.admission-steps.destroy', ['admissionStep' => $step->id]) }}" method="POST" class="d-none">
                                                @csrf
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="collapse" id="edit-step-{{$step->id}}">
                                    <td colspan="4" class="bg-light">
                                        <form action="{{ route('website-admin.admission-steps.update', ['admissionStep' => $step->id]) }}" method="POST" class="row g-2 align-items-end py-2">
                                            @csrf
                                            <div class="col-md-5">
                                                <label class="form-label">Title</label>
                                                <input type="text" class="form-control form-control-sm" name="title" value="{{$step->title}}" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Description</label>
                                                <input type="text" class="form-control form-control-sm" name="description" value="{{$step->description}}">
                                            </div>
                                            <div class="col-md-1">
                                                <label class="form-label">Order</label>
                                                <input type="number" class="form-control form-control-sm" name="sort_order" value="{{$step->sort_order}}">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="submit" class="btn btn-sm btn-primary w-100">Save</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-secondary py-4">No admission steps added yet.</td>
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
