@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-mortarboard"></i> Edit Program
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{route('website-admin.academic-programs.index')}}">Academic Programs</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit Program</li>
                        </ol>
                    </nav>
                    @include('session-messages')

                    <div class="mb-4 form-card" style="max-width: 640px;">
                        <form action="{{ route('website-admin.academic-programs.update', ['academicProgram' => $program->id]) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="name" class="form-label">Program Name<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{old('name', $program->name)}}" required>
                                </div>
                                <div class="col-12">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="4">{{old('description', $program->description)}}</textarea>
                                </div>
                                <div class="col-12">
                                    <label for="image" class="form-label">Photo <span class="text-secondary">(optional)</span></label>
                                    @if ($program->image)
                                        <div class="mb-2"><img src="{{asset($program->image)}}" alt="{{$program->name}}" style="max-height:100px;border-radius:8px;"></div>
                                    @endif
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                </div>
                                <div class="col-md-4">
                                    <label for="sort_order" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="sort_order" name="sort_order" value="{{old('sort_order', $program->sort_order)}}">
                                </div>
                                <div class="col-md-8 d-flex align-items-end">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="published" name="published" value="1" {{ old('published', $program->published) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="published">Published (visible on the public site)</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Update Program</button>
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
