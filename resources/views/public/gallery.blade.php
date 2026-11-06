@extends('public.layout')

@section('title', 'Gallery · ' . $siteSettings['site_name'])
@section('meta_description', 'Photos from around ' . $siteSettings['site_name'])

@section('content')

<x-public.page-hero
    eyebrow="Gallery"
    :title="'Life at ' . $siteSettings['site_name'] . '.'"
/>

<section class="home-section">
    <div class="site-container">
        @if ($photos->count() > 0)
        <div class="gallery-grid" id="galleryGrid">
            @foreach ($photos as $photo)
            <a href="{{ asset($photo->image) }}" class="item" data-caption="{{ $photo->caption }}" aria-label="Enlarge {{ $photo->caption ?: 'photo ' . $loop->iteration }}">
                <img src="{{ asset($photo->image) }}" alt="{{ $photo->caption }}" loading="lazy">
                @if ($photo->caption)
                <span class="caption">{{ $photo->caption }}</span>
                @endif
            </a>
            @endforeach
        </div>
        @else
        <p class="page-empty">Photos will be added here soon.</p>
        @endif
    </div>
</section>

@if ($photos->count() > 0)
<dialog class="lightbox" id="galleryLightbox" aria-label="Photo viewer">
    <button type="button" class="lightbox-close" data-lightbox="close" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    <button type="button" class="lightbox-nav prev" data-lightbox="prev" aria-label="Previous photo"><i class="bi bi-chevron-left"></i></button>
    <figure>
        <img src="" alt="">
        <figcaption></figcaption>
    </figure>
    <button type="button" class="lightbox-nav next" data-lightbox="next" aria-label="Next photo"><i class="bi bi-chevron-right"></i></button>
</dialog>
@endif

@endsection

@push('scripts')
<script>
    (function () {
        const grid = document.getElementById('galleryGrid');
        const lightbox = document.getElementById('galleryLightbox');
        if (!grid || !lightbox || typeof lightbox.showModal !== 'function') {
            return;
        }

        const items = Array.from(grid.querySelectorAll('.item'));
        const image = lightbox.querySelector('img');
        const caption = lightbox.querySelector('figcaption');
        let current = 0;

        function show(index) {
            current = (index + items.length) % items.length;
            const item = items[current];
            image.src = item.getAttribute('href');
            image.alt = item.dataset.caption || '';
            caption.textContent = item.dataset.caption || '';
            caption.hidden = !item.dataset.caption;
        }

        items.forEach(function (item, index) {
            item.addEventListener('click', function (event) {
                event.preventDefault();
                show(index);
                lightbox.showModal();
            });
        });

        lightbox.addEventListener('click', function (event) {
            const action = event.target.closest('[data-lightbox]');
            if (action) {
                const name = action.dataset.lightbox;
                if (name === 'close') { lightbox.close(); }
                if (name === 'prev') { show(current - 1); }
                if (name === 'next') { show(current + 1); }
                return;
            }
            if (event.target === lightbox) {
                lightbox.close();
            }
        });

        lightbox.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowLeft') { show(current - 1); }
            if (event.key === 'ArrowRight') { show(current + 1); }
        });

        const navButtons = lightbox.querySelectorAll('.lightbox-nav');
        navButtons.forEach(function (button) { button.hidden = items.length < 2; });
    })();
</script>
@endpush
