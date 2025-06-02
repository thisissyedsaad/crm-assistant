@extends('admin.layouts.app')

@section('title', 'Users Management')

@section('content')
        <!-- Main Content Area -->
        <div class="main-content introduction-farm">
            <div class="content-wraper-area">
                <!-- Basic Form area Start -->
                <div class="container-fluid">
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body card-breadcrumb">
                                    <div class="page-title-box d-flex align-items-center justify-content-between">
                                        <h4 class="mb-0">Create New User</h4>
                                        <div class="page-title-right">
                                            <!-- <ol class="breadcrumb m-0">
                                                <li class="breadcrumb-item"><a href="javascript: void(0);">Form</a>
                                                </li>
                                                <li class="breadcrumb-item active">Basic Form</li>
                                            </ol> -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6">
                            <div class="card card-h-100">
                                <div class="card-body">
                                    <div class="card-title">
                                        <h4 class="card-title">Form Layouts</h4>
                                    </div>
                                    <form>
                                        <div class="mb-3">
                                            <label class="form-label" for="formrow-firstname-input">First
                                                name</label>
                                            <input type="text" class="form-control" id="formrow-firstname-input">
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label"
                                                        for="formrow-email-input">Email</label>
                                                    <input type="email" class="form-control"
                                                        id="formrow-email-input">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label"
                                                        for="formrow-password-input">Password</label>
                                                    <input type="password" class="form-control"
                                                        id="formrow-password-input">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="form-check mt-3">
                                                <input type="checkbox" class="form-check-input"
                                                    id="formrow-customCheck">
                                                <label class="form-check-label" for="formrow-customCheck">Check me
                                                    out</label>
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <button type="submit" class="btn btn-primary w-md">Submit</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
@endsection