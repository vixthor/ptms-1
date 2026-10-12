<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Report Card &middot; {{ $student->first_name }} {{ $student->last_name }}</title>
    <link href="{{ asset('css/bootstrap-icons-1.7.1/font/bootstrap-icons.css') }}" rel="stylesheet">
    @include('report_cards._styles')
    <style>
        body {
            margin: 0;
            background: #f5f6fb;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            padding: 2rem 1rem;
        }
        .toolbar {
            width: 190mm;
            margin: 0 auto 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .toolbar form { display: flex; align-items: center; gap: .6rem; }
        .toolbar select {
            padding: .5rem .7rem;
            border-radius: 8px;
            border: 1.5px solid #e2e2ea;
            font-family: inherit;
            font-size: .875rem;
        }
        .toolbar .btn {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .55rem 1rem;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            font-size: .875rem;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-primary { background: linear-gradient(135deg, #4747d7, #0d33e0); color: #fff; }
        .btn-outline { background: #fff; color: #5c5c68; border: 1.5px solid #e2e2ea !important; }
        .sheet-wrap {
            width: 190mm;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 12px 32px rgba(26,26,36,.10), 0 4px 8px rgba(26,26,36,.04);
            padding: 1.5rem;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .sheet-wrap { box-shadow: none; padding: 0; width: auto; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <form method="GET" action="{{ route('report.card.show', ['id' => $student->id]) }}">
            <label for="semester_id" style="font-size:.8125rem;color:#5c5c68;font-weight:600;">Semester</label>
            <select name="semester_id" id="semester_id" onchange="this.form.submit()">
                @forelse ($semesters as $semester)
                    <option value="{{ $semester->id }}" {{ $selectedSemesterId == $semester->id ? 'selected' : '' }}>{{ $semester->semester_name }}</option>
                @empty
                    <option value="">No semesters yet</option>
                @endforelse
            </select>
        </form>
        <div style="display:flex;gap:.6rem;flex-wrap:wrap;">
            @if (Auth::user()->canManageAcademics())
                <a href="{{ route('report.card.edit', ['id' => $student->id, 'semester_id' => $selectedSemesterId]) }}" class="btn btn-outline"><i class="bi bi-pencil"></i> Edit Details</a>
            @endif
            <a href="{{ url()->previous() }}" class="btn btn-outline"><i class="bi bi-arrow-left"></i> Back</a>
            <a href="{{ route('report.card.download', ['id' => $student->id, 'semester_id' => $selectedSemesterId]) }}" class="btn btn-primary"><i class="bi bi-download"></i> Download PDF</a>
        </div>
    </div>

    <div class="sheet-wrap">
        <div class="rc-sheet">
            @include('report_cards._card')
        </div>
    </div>
</body>
</html>
