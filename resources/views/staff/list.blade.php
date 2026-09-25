@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-person-lines-fill"></i> Staff
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Staff</li>
                        </ol>
                    </nav>
                    @include('session-messages')

                    <div class="d-flex justify-content-end mb-3">
                        <a href="{{ route('staff.create') }}" class="btn btn-primary d-flex align-items-center gap-2" style="width:fit-content;">
                            <i class="bi bi-person-plus"></i> Add Staff
                        </a>
                    </div>

                    <div class="bg-white border shadow-sm p-3">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Photo</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Designation</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Phone</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($staff as $member)
                                <tr>
                                    <td>
                                        @if ($member->photo)
                                            <img src="{{asset($member->photo)}}" class="rounded" alt="Profile picture" height="30" width="30">
                                        @else
                                            <i class="bi bi-person-square"></i>
                                        @endif
                                    </td>
                                    <td>{{$member->first_name}} {{$member->last_name}}</td>
                                    <td><span class="badge bg-light text-dark border">{{ ucfirst($member->role) }}</span></td>
                                    <td>{{$member->email}}</td>
                                    <td>{{$member->phone}}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{route('staff.edit', ['id' => $member->id])}}" role="button" class="btn btn-sm btn-outline-primary"><i class="bi bi-pen"></i> Edit</a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary py-4">
                                        <i class="bi bi-person-lines-fill d-block mb-2" style="font-size:1.5rem;"></i>
                                        No staff members added yet.
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
