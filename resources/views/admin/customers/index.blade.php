@extends('admin.layouts.app')

@section('title', 'Users List | CRM Assistant')

@push('links')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/select.dataTables.min.css') }}">
@endpush

@section('content')
<!-- Main Content Area -->
<div class="main-content introduction-farm">
        <div class="content-wraper-area">
                <div class="data-table-area">
                <div class="container-fluid">
                        <div class="row g-4">
                        <div class="col-12">
                                <div class="card">
                                <div class="card-body card-breadcrumb">
                                        <div class="page-title-box d-flex align-items-center justify-content-between">
                                                <h4 class="mb-0">Customers List</h4>
                                                <div class="page-title-right">
                                                <!-- <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">Add New</a> -->
                                                </div>
                                        </div>
                                </div>
                                </div>
                        </div>

                        <div class="col-12">
                                <div class="card">
                                <div class="card-body">

                                        <table id="datatable" class="table table-bordered dt-responsive nowrap data-table-area">
                                        <thead>
                                                <tr>
                                                <th>Customer Number</th>
                                                <th>Customer Name</th>
                                                <th>Address</th>
                                                <th>Business Industry</th>
                                                <th>Created At</th>
                                                <!-- <th># Of Orders</th> -->
                                                </tr>
                                        </thead>

                                        <tbody>
                                                @foreach ($customers as $customer)
                                                <tr>
                                                        <td>
                                                            <a href="{{ route('admin.customers.show', $customer['id']) }}">
                                                                {{ $customer['attributes']['customerNo'] ?? 'N/A' }}
                                                            </a>
                                                        </td>
                                                        <td>{{ $customer['attributes']['companyName'] ?? 'N/A' }}</td>
                                                        <td>{{ $customer['attributes']['businessAddress']['address'] != '' ? $customer['attributes']['businessAddress']['address'] : 'N/A' }}</td>
                                                        <td>{{ $customer['attributes']['additionalField1'] ?? 'N/A' }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($customer['createdAt'])->format('Y-m-d') }}</td>
                                                        <!-- <td>{{ $customer['attributes']['additionalField1'] ?? 'N/A' }}</td> -->
                                                </tr>
                                                @endforeach
                                        </tbody>

                                        </table>
                                </div>
                                </div>
                        </div>

                        </div>
                </div>
                </div>
        </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/admin/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/admin/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/dataTables-custom.js') }}"></script>

    <!-- <script>
        $('#datatable').DataTable({
            scrollX: true,    // <-- This enables horizontal scrolling
        });
    </script> -->
@endpush