@php
    $activity = $activity ?? null;
@endphp
<div class="row g-3">
    <div class="col-12">
        <label for="name" class="form-label">Activity Name<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
        <input type="text" class="form-control" id="name" name="name" placeholder="e.g. Sports" value="{{ old('name', $activity?->name) }}" required>
    </div>
    <div class="col-12">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $activity?->description) }}</textarea>
    </div>
    <div class="col-12">
        <label for="image" class="form-label">Photo <span class="text-secondary">(optional)</span></label>
        @if ($activity?->image)
            <div class="mb-2"><img src="{{ asset($activity->image) }}" alt="{{ $activity->name }}" style="max-height:100px;border-radius:8px;"></div>
        @endif
        <input type="file" class="form-control" id="image" name="image" accept="image/*">
    </div>
    <div class="col-md-4">
        <label for="sort_order" class="form-label">Display Order</label>
        <input type="number" min="0" class="form-control" id="sort_order" name="sort_order" value="{{ old('sort_order', $activity?->sort_order ?? 0) }}">
    </div>
    <div class="col-md-8 d-flex align-items-end">
        <div class="form-check">
            <input type="hidden" name="published" value="0">
            <input class="form-check-input" type="checkbox" id="published" name="published" value="1" {{ old('published', $activity?->published ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="published">Published (visible on the public site)</label>
        </div>
    </div>
</div>
