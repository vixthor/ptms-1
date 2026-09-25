@if (session('status'))
    <div class="d-flex align-items-start gap-2 rounded-3 px-3 py-2 mb-3" style="background:var(--success-100);color:var(--success-500);font-size:.875rem;font-weight:500;">
        <i class="bi bi-check-circle-fill"></i> <span>{{ session('status') }}</span>
    </div>
@endif
@if (session('error'))
    <div class="d-flex align-items-start gap-2 rounded-3 px-3 py-2 mb-3" style="background:var(--danger-100);color:var(--danger-500);font-size:.875rem;font-weight:500;">
        <i class="bi bi-exclamation-circle-fill"></i> <span>{{ session('error') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="rounded-3 px-3 py-2 mb-3" style="background:var(--danger-100);color:var(--danger-500);font-size:.875rem;font-weight:500;">
        @foreach ($errors->all() as $error)
            <div class="d-flex align-items-start gap-2 {{ !$loop->last ? 'mb-1' : '' }}">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span>{{ $error }}</span>
            </div>
        @endforeach
    </div>
@endif
