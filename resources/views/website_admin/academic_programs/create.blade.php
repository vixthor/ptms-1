@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-mortarboard"></i> Add Program
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{route('website-admin.academic-programs.index')}}">Academic Programs</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Add Program</li>
                        </ol>
                    </nav>
                    @include('session-messages')

                    <div class="mb-4 form-card" style="max-width: 640px;">
                        <form action="{{ route('website-admin.academic-programs.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="name" class="form-label">Program Name<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                    <input type="text" class="form-control" id="name" name="name" placeholder="e.g. Nursery" value="{{old('name')}}" required>
                                </div>
                                <div class="col-12">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="4">{{old('description')}}</textarea>
                                </div>
                                <div class="col-12">
                                    <label for="image" class="form-label">Photo <span class="text-secondary">(optional)</span></label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                </div>
                                <div class="col-md-4">
                                    <label for="sort_order" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="sort_order" name="sort_order" value="{{old('sort_order', 0)}}">
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Save Program</button>
                                <a href="{{route('website-admin.academic-programs.index')}}" class="btn btn-outline-primary">Cancel</a>
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
