<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Report Card &middot; {{ $student->first_name }} {{ $student->last_name }}</title>
    @include('report_cards._styles')
    <style>
        html, body { margin: 0; padding: 0; }
    </style>
</head>
<body>
    <div class="rc-sheet">
        @php($forPdf = true)
        @include('report_cards._card')
    </div>
</body>
</html>
