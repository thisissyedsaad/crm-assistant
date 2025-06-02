@extends('admin.layouts.app')

@section('title', 'Customer Detail | CRM Assistant')

@push('links')

@endpush

@section('content')
<!-- Main Content Area -->
<div class="main-content introduction-farm">
                <div class="content-wraper-area">
                    <div class="container-fluid">
                        <div class="row g-4">
                            <!-- <div class="col-12">
                                <div class="card">
                                    <div class="card-body card-breadcrumb">
                                        <div class="page-title-box d-flex align-items-center justify-content-between">
                                            <div class="page-title-box d-flex align-items-center justify-content-between">
                                                    <h4 class="mb-0">Customers List</h4>
                                                    <div class="page-title-right">
                                                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">Add New</a>
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> -->

                            <!-- <div class="col-lg-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="nav flex-column nav-pills me-3 account-tab" id="v-pills-tab"
                                            role="tablist" aria-orientation="vertical">
                                            <button class="nav-link active" id="v-pills-home-tab" data-bs-toggle="pill"
                                                data-bs-target="#v-pills-home" type="button" role="tab"
                                                aria-controls="v-pills-home" aria-selected="true">General</button>
                                            <button class="nav-link" id="v-pills-profile-tab" data-bs-toggle="pill"
                                                data-bs-target="#v-pills-profile" type="button" role="tab"
                                                aria-controls="v-pills-profile"
                                                aria-selected="false">Notifications</button>
                                            <button class="nav-link" id="v-pills-messages-tab" data-bs-toggle="pill"
                                                data-bs-target="#v-pills-messages" type="button" role="tab"
                                                aria-controls="v-pills-messages" aria-selected="false">Billing &
                                                Payments</button>
                                            <button class="nav-link" id="v-pills-settings-tab" data-bs-toggle="pill"
                                                data-bs-target="#v-pills-settings" type="button" role="tab"
                                                aria-controls="v-pills-settings" aria-selected="false">Team
                                                Members</button>
                                        </div>
                                    </div>
                                </div>
                            </div> -->

                            <div class="col-lg-12">
                                <div class="tab-content" id="v-pills-tabContent">
                                    <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel"
                                        aria-labelledby="v-pills-home-tab" tabindex="0">
                                        <div class="card mb-4">
                                            <div class="card-header-cu">
                                                <div class="card-title d-flex justify-content-between align-items-center">
                                                <h4>Basic Information</h4>
                                                <a href="{{ route('admin.customers.index') }}" class="btn btn-primary btn-sm">All Customers</a>
                                                </div>                                                
                                            </div>
                                            <div class="card-body">
                                                <h4>Customer Details</h4>
                                                <p><strong>Customer Number:</strong> {{ $customer['attributes']['customerNo'] }}</p>
                                                <p><strong>Company Name:</strong> {{ $customer['attributes']['companyName'] }}</p>
                                                <p><strong>Address:</strong> {{ $customer['attributes']['businessAddress']['address'] ?? 'N/A' }}</p>
                                                <p><strong>Industry:</strong> {{ $customer['attributes']['additionalField1'] ?? 'N/A' }}</p>
                                                <p><strong>Created At:</strong> {{ \Carbon\Carbon::parse($customer['createdAt'])->format('Y-m-d') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
@endsection

@push('scripts')

@endpush