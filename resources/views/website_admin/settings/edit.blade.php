@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-gear"></i> Site Settings
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Site Settings</li>
                        </ol>
                    </nav>
                    @include('session-messages')

                    <div class="mb-4 form-card" style="max-width: 720px;">
                        <form action="{{ route('website-admin.settings.update') }}" method="POST">
                            @csrf
                            <h6 class="mb-3">General</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Site Name</label>
                                    <input type="text" class="form-control" name="site_name" value="{{old('site_name', $settings['site_name'])}}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tagline</label>
                                    <input type="text" class="form-control" name="tagline" value="{{old('tagline', $settings['tagline'])}}">
                                </div>
                            </div>

                            <h6 class="mb-3">Homepage Stats</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label">Enrollment</label>
                                    <input type="number" class="form-control" name="enrollment_count" value="{{old('enrollment_count', $settings['enrollment_count'])}}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Staff</label>
                                    <input type="number" class="form-control" name="staff_count" value="{{old('staff_count', $settings['staff_count'])}}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Clubs</label>
                                    <input type="number" class="form-control" name="clubs_count" value="{{old('clubs_count', $settings['clubs_count'])}}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Subjects</label>
                                    <input type="number" class="form-control" name="subjects_count" value="{{old('subjects_count', $settings['subjects_count'])}}">
                                </div>
                            </div>

                            <h6 class="mb-3">Contact</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Phone</label>
                                    <input type="text" class="form-control" name="phone" value="{{old('phone', $settings['phone'])}}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">WhatsApp Number</label>
                                    <input type="text" class="form-control" name="whatsapp" value="{{old('whatsapp', $settings['whatsapp'])}}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" value="{{old('email', $settings['email'])}}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <input type="text" class="form-control" name="address" value="{{old('address', $settings['address'])}}">
                                </div>
                            </div>

                            <h6 class="mb-3">Social Links</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Facebook URL</label>
                                    <input type="url" class="form-control" name="facebook_url" value="{{old('facebook_url', $settings['facebook_url'])}}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Twitter / X URL</label>
                                    <input type="url" class="form-control" name="twitter_url" value="{{old('twitter_url', $settings['twitter_url'])}}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Instagram URL</label>
                                    <input type="url" class="form-control" name="instagram_url" value="{{old('instagram_url', $settings['instagram_url'])}}">
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Save Settings</button>
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
