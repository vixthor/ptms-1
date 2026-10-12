@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-images"></i> Gallery
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Gallery</li>
                        </ol>
                    </nav>
                    @include('session-messages')

                    <div class="mb-4 form-card" style="max-width: 640px;">
                        <h6 class="mb-3">Upload a photo</h6>
                        <form action="{{ route('website-admin.gallery-photos.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="image" class="form-label">Photo<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="caption" class="form-label">Caption</label>
                                    <input type="text" class="form-control" id="caption" name="caption" value="{{old('caption')}}">
                                </div>
                                <div class="col-md-6">
                                    <label for="category" class="form-label">Category <span class="text-secondary">(optional)</span></label>
                                    <input type="text" class="form-control" id="category" name="category" placeholder="e.g. Events" value="{{old('category')}}">
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Upload</button>
                            </div>
                        </form>
                    </div>

                    <div class="row g-3">
                        @forelse ($photos as $photo)
                        <div class="col-md-3">
                            <div class="card h-100">
                                <img src="{{asset($photo->image)}}" class="card-img-top" alt="{{$photo->caption}}" style="height:140px;object-fit:cover;border-radius:var(--radius-lg) var(--radius-lg) 0 0;">
                                <div class="card-body">
                                    <p class="mb-1" style="font-size:.8125rem;">{{$photo->caption ?: '—'}}</p>
                                    @if($photo->category)
                                        <span class="badge bg-light text-dark border mb-2">{{$photo->category}}</span>
                                    @endif
                                    <form action="{{ route('website-admin.gallery-photos.destroy', ['galleryPhoto' => $photo->id]) }}" method="POST" onsubmit="return confirm('Delete this photo?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary w-100"><i class="bi bi-trash2"></i> Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-center text-secondary py-4">
                            <i class="bi bi-images d-block mb-2" style="font-size:1.5rem;"></i>
                            No photos uploaded yet.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
