@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-person-lines-fill"></i> Edit Staff
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{route('staff.list')}}">Staff</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit Staff</li>
                        </ol>
                    </nav>

                    @include('session-messages')

                    <div class="mb-4 form-card">
                        <form class="row g-3" action="{{ route('staff.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="staff_id" value="{{$staff->id}}">
                            <div class="col-3">
                                <label for="inputDesignation" class="form-label">Designation<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                <select id="inputDesignation" class="form-select" name="designation" required>
                                    <option value="headteacher" {{($staff->role == 'headteacher')?'selected':null}}>Headteacher</option>
                                    <option value="proprietor" {{($staff->role == 'proprietor')?'selected':null}}>Proprietor</option>
                                    <option value="secretary" {{($staff->role == 'secretary')?'selected':null}}>Secretary</option>
                                </select>
                            </div>
                            <div class="col-3">
                                <label for="inputFirstName" class="form-label">First Name<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                <input type="text" class="form-control" id="inputFirstName" name="first_name" placeholder="First Name" required value="{{$staff->first_name}}">
                            </div>
                            <div class="col-3">
                                <label for="inputLastName" class="form-label">Last Name<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                <input type="text" class="form-control" id="inputLastName" name="last_name" placeholder="Last Name" required value="{{$staff->last_name}}">
                            </div>
                            <div class="col-3">
                                <label for="inputEmail" class="form-label">Email<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                <input type="email" class="form-control" id="inputEmail" name="email" required value="{{$staff->email}}">
                            </div>
                            <div class="col-4">
                                <label for="inputAddress" class="form-label">Address<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                <input type="text" class="form-control" id="inputAddress" name="address" placeholder="634 Main St" required value="{{$staff->address}}">
                            </div>
                            <div class="col-3">
                                <label for="inputAddress2" class="form-label">Address 2</label>
                                <input type="text" class="form-control" id="inputAddress2" name="address2" placeholder="Apartment, studio, or floor" value="{{$staff->address2}}">
                            </div>
                            <div class="col-2">
                                <label for="inputCity" class="form-label">City<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                <input type="text" class="form-control" id="inputCity" name="city" placeholder="City" required value="{{$staff->city}}">
                            </div>
                            <div class="col-2">
                                <label for="inputZip" class="form-label">Zip<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                <input type="text" class="form-control" id="inputZip" name="zip" required value="{{$staff->zip}}">
                            </div>
                            <div class="col-3">
                                <label for="inputPhone" class="form-label">Phone<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                <input type="text" class="form-control" id="inputPhone" name="phone" placeholder="+44 7......" required value="{{$staff->phone}}">
                            </div>
                            <div class="col-2">
                                <label for="inputState" class="form-label">Gender<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                <select id="inputState" class="form-select" name="gender" required>
                                    <option value="Male" {{($staff->gender == 'Male')?'selected':null}}>Male</option>
                                    <option value="Female" {{($staff->gender == 'Female')?'selected':null}}>Female</option>
                                </select>
                            </div>
                            <div class="col-3">
                                <label for="inputNationality" class="form-label">Nationality<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                <input type="text" class="form-control" id="inputNationality" name="nationality" placeholder="e.g. British, Nigerian, ..." required value="{{$staff->nationality}}">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-person-check"></i> Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
