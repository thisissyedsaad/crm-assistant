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
                                                <h4 class="mb-0">Orders List</h4>
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
                                                <th>Order Date</th>
                                                <th>Order Number</th>
                                                <!-- <th>Company Name</th>
                                                <th>Customer Name</th> -->
                                                <th>Carrier/Vehicle</th>
                                                <th>Sale Price</th>
                                                <th>Purchase</th>
                                                </tr>
                                        </thead>

                                        <tbody>
                                                @foreach ($orders as $order)
                                                <tr>
                                                        <td>{{ \Carbon\Carbon::parse($order['attributes']['date'])->format('Y-m-d') }}</td>
                                                        <td>{{ $order['attributes']['orderNo'] ?? 'N/A' }}</td>
                                                        <!-- <td>{{ $order['attributes']['companyName'] ?? 'N/A' }}</td>
                                                        <td>{{ $order['attributes']['companyName'] ?? 'N/A' }}</td> -->
                                                        <td>{{ $order['attributes']['vehicleTypeName'] ?? 'N/A' }}</td>
                                                        <td>{{ $order['attributes']['orderPrice'] ?? '-' }}</td>
                                                        <td>{{ $order['attributes']['orderPurchasePrice'] ?? '-' }}</td>
                                                        <!-- <td>{{ $order['attributes']['additionalField1'] ?? 'N/A' }}</td> -->
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