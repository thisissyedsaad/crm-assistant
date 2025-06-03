@extends('admin.layouts.app')

@section('title', 'Customers List | CRM Assistant')

@push('links')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/select.dataTables.min.css') }}">
@endpush

@section('content')
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label for="fromDate" class="form-label">From Date</label>
                                        <input type="date" id="fromDate" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="toDate" class="form-label">To Date</label>
                                        <input type="date" id="toDate" class="form-control">
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button id="filterBtn" class="btn btn-primary">Filter</button>
                                        <button id="resetBtn" class="btn btn-secondary ms-2">Reset</button>
                                    </div>
                                </div>

                                <table id="datatable" class="table table-bordered dt-responsive nowrap data-table-area" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Created Date/Time</th>
                                            <th>Customer Number</th>
                                            <th>Company Name</th>
                                            <th>Address</th>
                                            <th>Business Industry</th>
                                            <!-- <th># Of Orders</th> -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
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

    <script>
        $(function () {
            // Check if DataTable is already initialized on this table
            if ($.fn.DataTable.isDataTable('#datatable')) {
                // If it is, destroy the existing instance
                $('#datatable').DataTable().destroy();
            }

            var table = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true, // Enable horizontal scrolling if content overflows
                ajax: {
                    url: "{{ route('admin.customers.index') }}",
                    data: function (d) {
                        d.fromDate = $('#fromDate').val();
                        d.toDate = $('#toDate').val();
                        // DataTables automatically adds 'start', 'length', 'search[value]', 'order[0][column]', etc.
                    }
                },
                pageLength: 25, // Show 50 results per page (from the 100 fetched)
                columns: [
                    { data: 'createdAt', name: 'createdAt' },
                    { 
                        data: 'customerNo', 
                        name: 'customerNo',
                        render: function(data, type, row) {
                                return `<a href="/admin/customers/${row.id}">${data ?? 'N/A'}</a>`; 
                        }
                    },
                    { 
                        data: 'companyName', 
                        name: 'companyName',
                        render: function(data, type, row) {
                                return `<a href="/admin/customers/${row.id}">${data ?? 'N/A'}</a>`; 
                        }
                    },  
                    { data: 'address', name: 'address' },
                    { data: 'industry', name: 'industry' }
                //     { data: 'numberOfOrders', name: 'numberOfOrders', orderable: false, searchable: false }
                ]
            });

            $('#filterBtn').on('click', function () {
                table.draw();
            });

            $('#resetBtn').on('click', function () {
                $('#fromDate').val('');
                $('#toDate').val('');
                table.draw();
            });
        });
    </script>
@endpush