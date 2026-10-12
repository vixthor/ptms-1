@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-vector-pen"></i> Mass Sign Results
                    </h1>
                    @include('session-messages')
                    <h6>Filter list by:</h6>
                    <div class="mb-4 mt-4">
                        <form action="{{ route('report.card.mass-sign.form') }}" method="GET">
                            <div class="row">
                                <div class="col">
                                    <select class="form-select" name="semester_id" required>
                                        <option value="" disabled {{ ! $selectedSemesterId ? 'selected' : '' }}>Please select a semester</option>
                                        @foreach ($semesters as $semester)
                                            <option value="{{ $semester->id }}" {{ (string) $selectedSemesterId === (string) $semester->id ? 'selected' : '' }}>{{ $semester->semester_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col">
                                    <select onchange="getSections(this);" class="form-select" name="class_id" required>
                                        <option value="" disabled {{ ! $selectedClassId ? 'selected' : '' }}>Please select a class</option>
                                        @foreach ($classes as $school_class)
                                            <option value="{{ $school_class->id }}" {{ (string) $selectedClassId === (string) $school_class->id ? 'selected' : '' }}>{{ $school_class->class_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col">
                                    <select class="form-select" id="section-select" name="section_id" required>
                                        <option value="" disabled {{ ! $selectedSectionId ? 'selected' : '' }}>Please select a section</option>
                                        @foreach ($sections as $section)
                                            <option value="{{ $section->id }}" {{ (string) $selectedSectionId === (string) $section->id ? 'selected' : '' }}>{{ $section->section_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-arrow-counterclockwise"></i> Load List</button>
                                </div>
                            </div>
                        </form>

                        @if ($roster->count() > 0)
                            <form action="{{ route('report.card.mass-sign') }}" method="POST" enctype="multipart/form-data" class="bg-white border mt-4 p-3 shadow-sm">
                                @csrf
                                <input type="hidden" name="semester_id" value="{{ $selectedSemesterId }}">
                                <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
                                <input type="hidden" name="section_id" value="{{ $selectedSectionId }}">

                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col"><input type="checkbox" id="select-all" checked onchange="document.querySelectorAll('.student-check').forEach(c => c.checked = this.checked);"></th>
                                            <th scope="col">Student Name</th>
                                            <th scope="col">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($roster as $promotion)
                                            <tr>
                                                <td><input type="checkbox" class="student-check" name="student_ids[]" value="{{ $promotion->student_id }}" checked></td>
                                                <td>{{ $promotion->student->first_name }} {{ $promotion->student->last_name }}</td>
                                                <td>
                                                    @if ($signedStudentIds->contains($promotion->student_id))
                                                        <span class="badge text-bg-success">Already signed</span>
                                                    @else
                                                        <span class="badge text-bg-secondary">Not signed</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                <div class="row">
                                    <div class="col-md-6 mt-2">
                                        <label for="headteacher_signature" class="form-label">Principal / HM's Signature<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                        <input type="file" name="headteacher_signature" id="headteacher_signature" class="form-control" accept="image/*" required>
                                        <div class="form-text">This signature image is applied to every checked student's report card.</div>
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <label for="headteacher_comment" class="form-label">Principal / HM's Comment (optional)</label>
                                        <textarea name="headteacher_comment" id="headteacher_comment" class="form-control" rows="2" placeholder="Leave blank to keep each student's existing comment"></textarea>
                                    </div>
                                </div>

                                <button type="submit" class="mt-3 btn btn-primary"><i class="bi bi-vector-pen"></i> Sign Selected Report Cards</button>
                            </form>
                        @elseif ($selectedSemesterId && $selectedClassId && $selectedSectionId)
                            <div class="bg-white border mt-4 p-3 shadow-sm text-muted">
                                No students found for that class/section.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
<script>
    function getSections(obj) {
        var class_id = obj.options[obj.selectedIndex].value;

        var url = "{{ route('get.sections.courses.by.classId', 'classId') }}".replace('classId', class_id);

        fetch(url)
        .then((resp) => resp.json())
        .then(function(data) {
            var sectionSelect = document.getElementById('section-select');
            sectionSelect.options.length = 0;
            data.sections.unshift({'id': '', 'section_name': 'Please select a section'});
            data.sections.forEach(function(section, key) {
                sectionSelect[key] = new Option(section.section_name, section.id);
            });
        })
        .catch(function(error) {
            console.log(error);
        });
    }
</script>
@endsection
