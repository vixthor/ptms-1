@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-trophy"></i> Add Activity
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{route('website-admin.extracurricular-activities.index')}}">Extra-curricular Activities</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Add Activity</li>
                        </ol>
                    </nav>
                    @include('session-messages')

                    <div class="mb-4 form-card" style="max-width: 640px;">
                        <form action="{{ route('website-admin.extracurricular-activities.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @include('website_admin.extracurricular_activities._form')
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Save Activity</button>
                                <a href="{{route('website-admin.extracurricular-activities.index')}}" class="btn btn-outline-primary">Cancel</a>
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
