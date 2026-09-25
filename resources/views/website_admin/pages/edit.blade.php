@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-globe"></i> Edit Page <span class="text-secondary" style="text-transform:capitalize;">&middot; {{ $page->slug }}</span>
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{route('website-admin.pages.index')}}">Website Pages</a></li>
                            <li class="breadcrumb-item active" aria-current="page" style="text-transform:capitalize;">{{ $page->slug }}</li>
                        </ol>
                    </nav>
                    @include('session-messages')

                    <div class="mb-4 form-card" style="max-width: 760px;">
                        <form action="{{ route('website-admin.pages.update', ['slug' => $page->slug]) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="title" class="form-label">Page Title (browser tab / SEO)<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                    <input type="text" class="form-control" id="title" name="title" value="{{old('title', $page->title)}}" required>
                                </div>
                                <div class="col-12">
                                    <label for="hero_title" class="form-label">Hero Heading</label>
                                    <input type="text" class="form-control" id="hero_title" name="hero_title" value="{{old('hero_title', $page->hero_title)}}">
                                </div>
                                <div class="col-12">
                                    <label for="hero_subtitle" class="form-label">Hero Subheading</label>
                                    <input type="text" class="form-control" id="hero_subtitle" name="hero_subtitle" value="{{old('hero_subtitle', $page->hero_subtitle)}}">
                                </div>
                                <div class="col-12">
                                    <label for="image" class="form-label">Hero Image <span class="text-secondary">(optional)</span></label>
                                    @if ($page->hero_image)
                                        <div class="mb-2"><img src="{{asset($page->hero_image)}}" alt="Current hero image" style="max-height:120px;border-radius:8px;"></div>
                                    @endif
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                </div>
                                <div class="col-12">
                                    <label for="body" class="form-label">Page Content</label>
                                    <textarea name="body" class="form-control" id="body-editor" rows="10">{{old('body', $page->body)}}</textarea>
                                </div>
                                <div class="col-12">
                                    <label for="meta_description" class="form-label">Meta Description <span class="text-secondary">(for search engines)</span></label>
                                    <input type="text" class="form-control" id="meta_description" name="meta_description" value="{{old('meta_description', $page->meta_description)}}">
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="published" name="published" value="1" {{ old('published', $page->published) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="published">Published (visible on the public site)</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Save Page</button>
                                <a href="{{route('website-admin.pages.index')}}" class="btn btn-outline-primary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>

<script src="https://cdn.ckeditor.com/ckeditor5/30.0.0/classic/ckeditor.js"></script>
<script>
    ClassicEditor.create(document.querySelector('#body-editor'), {
        toolbar: ['heading', 'bold', 'italic', '|', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo'],
    }).catch(error => console.error(error));
</script>
@endsection
