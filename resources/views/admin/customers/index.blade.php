@extends('admin.layouts.app')

@section('title', 'Customers List | CRM Assistant')

@push('links')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/select.dataTables.min.css') }}">
    <style>
        /* Fix scrolling for DataTables */
        .dataTables_wrapper {
            overflow-x: visible !important;
        }
        
        .dataTables_scrollBody {
            overflow-x: auto !important;
            overflow-y: visible !important;
        }
        
        /* Table should use full width when columns fit, scroll when they don't */
        #datatable {
            width: 100% !important;
            table-layout: auto;
        }
        
        /* Set minimum widths but allow columns to expand to fill available space */
        #datatable th:nth-child(1), #datatable td:nth-child(1) { 
            min-width: 150px; 
            width: 20%; /* Flexible width */
        } /* Created Date/Time */
        
        #datatable th:nth-child(2), #datatable td:nth-child(2) { 
            min-width: 130px; 
            width: 15%; /* Flexible width */
        } /* Customer Number */
        
        #datatable th:nth-child(3), #datatable td:nth-child(3) { 
            min-width: 200px; 
            width: 25%; /* Flexible width */
        } /* Company Name */
        
        #datatable th:nth-child(4), #datatable td:nth-child(4) { 
            min-width: 180px; 
            width: 25%; /* Flexible width */
        } /* Address */
        
        #datatable th:nth-child(5), #datatable td:nth-child(5) { 
            min-width: 140px; 
            width: 15%; /* Flexible width */
        } /* Business Industry */
        
        /* Most columns should not wrap by default */
        #datatable th,
        #datatable td {
            white-space: nowrap;
        }
        
        /* Address and Company Name columns - allow text wrapping when needed */
        #datatable th:nth-child(3), #datatable td:nth-child(3),
        #datatable th:nth-child(4), #datatable td:nth-child(4) {
            white-space: normal !important;
            word-wrap: break-word;
        }
        
        /* Scrollbar styling */
        .dataTables_scrollBody::-webkit-scrollbar {
            height: 8px;
        }
        
        .dataTables_scrollBody::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        .dataTables_scrollBody::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        
        .dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
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

                                <table id="datatable" class="table table-bordered dt-responsive nowrap data-table-area">
                                    <thead>
                                        <tr>
                                            <th>Created Date/Time</th>
                                            <th>Customer Number</th>
                                            <th>Company Name</th>
                                            <th>Address</th>
                                            <th>Business Industry</th>
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
                scrollX: true, // Enable horizontal scrolling only when needed
                scrollCollapse: false, // Prevent table from collapsing
                autoWidth: true, // Enable auto width calculation for better fit
                responsive: false, // Disable responsive plugin to force scrolling
                ajax: {
                    url: "{{ route('admin.customers.index') }}",
                    data: function (d) {
                        d.fromDate = $('#fromDate').val();
                        d.toDate = $('#toDate').val();
                        // DataTables automatically adds 'start', 'length', 'search[value]', 'order[0][column]', etc.
                    }
                },
                pageLength: 25, // Show 25 results per page
                columns: [
                    { 
                        data: 'createdAt', 
                        name: 'createdAt',
                        className: 'text-nowrap'
                    },
                    { 
                        data: 'customerNo', 
                        name: 'customerNo',
                        className: 'text-nowrap',
                        render: function(data, type, row) {
                            return `<a href="/admin/customers/${row.id}">${data ?? '-'}</a>`; 
                        }
                    },
                    { 
                        data: 'companyName', 
                        name: 'companyName',
                        className: 'text-wrap',
                        render: function(data, type, row) {
                            return `<a href="/admin/customers/${row.id}">${data ?? '-'}</a>`; 
                        }
                    },  
                    { 
                        data: 'address', 
                        name: 'address',
                        className: 'text-wrap',
                        render: function(data, type, row) {
                            if (type === 'display' && data && data.length > 40) {
                                return '<span title="' + data + '">' + data.substr(0, 40) + '...</span>';
                            }
                            return data || '-';
                        }
                    },
                    { 
                        data: 'industry', 
                        name: 'industry',
                        className: 'text-wrap',
                        render: function(data, type, row) {
                            return data || '-';
                        }
                    }
                ],
                // Force table layout
                initComplete: function() {
                    // Ensure table uses full width when possible
                    $('.dataTables_wrapper').css({
                        'width': '100%'
                    });
                }
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