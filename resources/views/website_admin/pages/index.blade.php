@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-globe"></i> Website Pages
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Website Pages</li>
                        </ol>
                    </nav>
                    @include('session-messages')

                    <div class="row g-3">
                        @foreach ($pages as $page)
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body d-flex flex-column">
                                    <div class="fw-semibold mb-1" style="text-transform:capitalize;">{{ $page->slug }}</div>
                                    <p class="text-secondary flex-grow-1" style="font-size:.8125rem;">{{ $page->title }}</p>
                                    <a href="{{ route('website-admin.pages.edit', ['slug' => $page->slug]) }}" class="btn btn-outline-primary btn-sm" style="width:fit-content;"><i class="bi bi-pen"></i> Edit</a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
