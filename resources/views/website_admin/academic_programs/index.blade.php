@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-mortarboard"></i> Academic Programs
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Academic Programs</li>
                        </ol>
                    </nav>
                    @include('session-messages')

                    <div class="d-flex justify-content-end mb-3">
                        <a href="{{ route('website-admin.academic-programs.create') }}" class="btn btn-primary d-flex align-items-center gap-2" style="width:fit-content;">
                            <i class="bi bi-plus-circle"></i> Add Program
                        </a>
                    </div>

                    <div class="bg-white border shadow-sm p-3">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Photo</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Order</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($programs as $program)
                                <tr>
                                    <td>
                                        @if ($program->image)
                                            <img src="{{asset($program->image)}}" class="rounded" alt="{{$program->name}}" height="30" width="30" style="object-fit:cover;">
                                        @else
                                            <i class="bi bi-image"></i>
                                        @endif
                                    </td>
                                    <td>{{$program->name}}</td>
                                    <td style="white-space:normal;max-width:360px;">{{ \Illuminate\Support\Str::limit($program->description, 80) }}</td>
                                    <td>{{$program->sort_order}}</td>
                                    <td>
                                        @if($program->published)
                                            <span class="badge" style="background:var(--success-100);color:var(--success-500);">Published</span>
                                        @else
                                            <span class="badge" style="background:var(--ink-100);color:var(--ink-500);">Hidden</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{route('website-admin.academic-programs.edit', ['academicProgram' => $program->id])}}" role="button" class="btn btn-sm btn-outline-primary"><i class="bi bi-pen"></i> Edit</a>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="event.preventDefault(); if(confirm('Delete this program?')) document.getElementById('delete-program-{{$program->id}}').submit();"><i class="bi bi-trash2"></i> Delete</button>
                                            <form id="delete-program-{{$program->id}}" action="{{ route('website-admin.academic-programs.destroy', ['academicProgram' => $program->id]) }}" method="POST" class="d-none">
                                                @csrf
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary py-4">
                                        <i class="bi bi-mortarboard d-block mb-2" style="font-size:1.5rem;"></i>
                                        No programs added yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
