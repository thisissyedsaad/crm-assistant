@extends('admin.layouts.app')

@section('title', 'Users List | CRM Assistant')

@push('links')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/select.dataTables.min.css') }}">
    
    <style>
        /* DataTable Responsive Styling */
        .dataTables_wrapper {
            overflow-x: visible !important;
            width: 100% !important;
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
        
        /* Set minimum widths for columns */
        #datatable th:nth-child(1), #datatable td:nth-child(1) { 
            min-width: 150px; 
            width: 25%; 
        } /* Name */
        
        #datatable th:nth-child(2), #datatable td:nth-child(2) { 
            min-width: 200px; 
            width: 30%; 
        } /* Email */
        
        #datatable th:nth-child(3), #datatable td:nth-child(3) { 
            min-width: 100px; 
            width: 15%; 
        } /* Role */
        
        #datatable th:nth-child(4), #datatable td:nth-child(4) { 
            min-width: 120px; 
            width: 15%; 
        } /* Created At */
        
        #datatable th:nth-child(5), #datatable td:nth-child(5) { 
            min-width: 150px; 
            width: 15%; 
        } /* Actions */
        
        /* Most columns should not wrap by default */
        #datatable th,
        #datatable td {
            white-space: nowrap;
        }
        
        /* Allow text wrapping for name and email columns when needed */
        #datatable th:nth-child(1), #datatable td:nth-child(1),
        #datatable th:nth-child(2), #datatable td:nth-child(2) {
            white-space: normal !important;
            word-wrap: break-word;
        }
        
        /* Actions column styling */
        #datatable td:nth-child(5) {
            white-space: nowrap;
        }
        
        #datatable td:nth-child(5) .btn {
            margin-right: 5px;
            margin-bottom: 2px;
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
        
        /* Mobile specific styling */
        @media (max-width: 767px) {
            .dataTables_filter input[type="search"] {
                width: 100% !important;
                max-width: 100% !important;
                min-width: 200px !important;
                box-sizing: border-box !important;
                display: block !important;
            }
            
            .dataTables_filter {
                width: 100% !important;
                display: block !important;
            }
            
            .dataTables_filter label {
                width: 100% !important;
                display: block !important;
            }
            
            /* Stack action buttons on mobile */
            #datatable td:nth-child(5) .btn {
                display: block;
                width: 100%;
                margin-bottom: 5px;
                margin-right: 0;
            }
            
            #datatable td:nth-child(5) form {
                margin-top: 5px;
            }
        }
        
        /* Tablet styling */
        @media (min-width: 768px) and (max-width: 991px) {
            #datatable th:nth-child(1), #datatable td:nth-child(1) { 
                width: 20%; 
            }
            
            #datatable th:nth-child(2), #datatable td:nth-child(2) { 
                width: 25%; 
            }
            
            #datatable th:nth-child(3), #datatable td:nth-child(3) { 
                width: 15%; 
            }
            
            #datatable th:nth-child(4), #datatable td:nth-child(4) { 
                width: 20%; 
            }
            
            #datatable th:nth-child(5), #datatable td:nth-child(5) { 
                width: 20%; 
            }
        }
        
        /* Force override for DataTable search inputs */
        .dataTables_wrapper .dataTables_filter input {
            width: 200px !important;
            min-width: 200px !important;
            padding: 8px 12px !important;
            border: 1px solid #ddd !important;
            border-radius: 4px !important;
            margin-left: 8px !important;
        }
        
        @media (max-width: 767px) {
            .dataTables_wrapper .dataTables_filter input {
                width: 100% !important;
                min-width: 250px !important;
                margin-left: 0 !important;
                margin-top: 5px !important;
            }
        }
    </style>
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
                                                <h4 class="mb-0">Users List</h4>
                                                <div class="page-title-right">
                                                <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">Add New</a>
                                                </div>
                                        </div>
                                </div>
                                </div>
                        </div>

                        <div class="col-12">
                                <div class="card">
                                <div class="card-body">
                                        <!-- <div class="card-title d-flex justify-content-between align-items-center">
                                        <h4>Users Table</h4>
                                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">Add New</a>
                                        </div> -->

                                        <table id="datatable" class="table table-bordered dt-responsive nowrap data-table-area">
                                        <thead>
                                                <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Created At</th>
                                                <th>Actions</th>
                                                </tr>
                                        </thead>

                                        <tbody>
                                                @foreach ($users as $user)
                                                <tr>
                                                        <td>{{ $user->name }}</td>
                                                        <td>{{ $user->email }}</td>
                                                        <td>{{ ucfirst($user->role) }}</td>
                                                        <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                                        <td>
                                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-primary">Edit</a>

                                                        @if(auth()->id() != $user->id)
                                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display:inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                                                        </form>
                                                        @else
                                                        <button class="btn btn-danger btn-sm" disabled title="You cannot delete yourself">Delete</button>
                                                        @endif
                                                        </td>
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
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable with responsive options
            if ($.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable().destroy();
            }
            
            $('#datatable').DataTable({
                responsive: false, // We handle responsiveness with CSS
                scrollX: true,
                scrollCollapse: false,
                autoWidth: true,
                columnDefs: [
                    { targets: 0, className: 'text-wrap' }, // Name
                    { targets: 1, className: 'text-wrap' }, // Email  
                    { targets: 2, className: 'text-nowrap' }, // Role
                    { targets: 3, className: 'text-nowrap' }, // Created At
                    { targets: 4, className: 'text-nowrap', orderable: false } // Actions
                ],
                language: {
                    search: "Search Users:",
                    lengthMenu: "Show _MENU_ users per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ users"
                },
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                order: [[3, 'desc']], // Sort by Created At descending
                initComplete: function() {
                    $('.dataTables_wrapper').css('width', '100%');
                },
                drawCallback: function() {
                    $('.dataTables_wrapper').css('width', '100%');
                }
            });
        });
    </script>
@endpush