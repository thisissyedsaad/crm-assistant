@extends('admin.layouts.app')

@section('title', 'Managing Targets | CSD Assistant')

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
            min-width: 120px; 
            width: 15%; 
        } /* Team */
        
        #datatable th:nth-child(2), #datatable td:nth-child(2) { 
            min-width: 280px; 
            width: 35%; 
        } /* Daily Target */
        
        #datatable th:nth-child(3), #datatable td:nth-child(3) { 
            min-width: 120px; 
            width: 15%; 
        } /* Working Days */
        
        #datatable th:nth-child(4), #datatable td:nth-child(4) { 
            min-width: 120px; 
            width: 15%; 
        } /* Monthly Target */
        
        #datatable th:nth-child(5), #datatable td:nth-child(5) { 
            min-width: 100px; 
            width: 20%; 
        } /* Actions */
        
        /* Most columns should not wrap by default */
        #datatable th,
        #datatable td {
            white-space: nowrap;
        }
        
        /* Input styling */
        .table input.form-control-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border: 1px solid #dee2e6;
        }
        
        .form-control-sm:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .monthly-target {
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .gap-2 {
            gap: 0.5rem;
        }
        
        /* Separator styling */
        .align-self-center {
            color: #6c757d;
            font-weight: 500;
        }
        
        .btn-success {
            min-width: 80px;
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
                                    <h4 class="mb-0">Managing Targets</h4>
                                    <div class="page-title-right">
                                        <!-- <button type="button" class="btn btn-primary btn-sm" id="saveAllBtn">
                                            <i class="bx bx-save"></i> Save All
                                        </button> -->
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
                                            <th>Team</th>
                                            <th>Daily Target <small class="text-muted" style="color:green !important;">(Total / New / Existing)</small></th>
                                            <th># of Working Days</th>
                                            <th>Monthly Target</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($staffUsers as $user)
                                        <tr data-user-id="{{ $user->id }}">
                                            <td><strong>{{ $user->name }}</strong></td>
                                            <td>
                                                <div class="d-flex gap-2 align-items-center" style="white-space: nowrap;">
                                                    <input 
                                                        type="number" 
                                                        class="form-control form-control-sm daily-target-total" 
                                                        placeholder="Total"
                                                        value="{{ $user->dailyTarget->daily_target_total ?? 0 }}"
                                                        min="0"
                                                        style="width: 70px;"
                                                    >
                                                    <span class="align-self-center">/</span>
                                                    <input 
                                                        type="number" 
                                                        class="form-control form-control-sm daily-target-new" 
                                                        placeholder="New"
                                                        value="{{ $user->dailyTarget->daily_target_new ?? 0 }}"
                                                        min="0"
                                                        style="width: 70px;"
                                                    >
                                                    <span class="align-self-center">/</span>
                                                    <input 
                                                        type="number" 
                                                        class="form-control form-control-sm daily-target-existing" 
                                                        placeholder="Existing"
                                                        value="{{ $user->dailyTarget->daily_target_existing ?? 0 }}"
                                                        min="0"
                                                        style="width: 70px;"
                                                    >
                                                </div>
                                            </td>
                                            <td>
                                                <input 
                                                    type="number" 
                                                    class="form-control form-control-sm working-days" 
                                                    value="{{ $user->dailyTarget->working_days ?? 0 }}"
                                                    min="0"
                                                    style="width: 100px;"
                                                >
                                            </td>
                                            <td>
                                                <strong class="monthly-target text-primary">
                                                    {{ $user->dailyTarget ? $user->dailyTarget->monthly_target : 0 }}
                                                </strong>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-success save-row-btn" data-user-id="{{ $user->id }}">
                                                    <i class="bx bx-save"></i> Save
                                                </button>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable with responsive options
            if ($.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable().destroy();
            }
            
            var table = $('#datatable').DataTable({
                responsive: false, // We handle responsiveness with CSS
                scrollX: true,
                scrollCollapse: false,
                autoWidth: true,
                columnDefs: [
                    { targets: 0, className: 'text-nowrap' }, // Team
                    { targets: 1, className: 'text-nowrap', orderable: false }, // Daily Target
                    { targets: 2, className: 'text-nowrap' }, // Working Days
                    { targets: 3, className: 'text-nowrap' }, // Monthly Target
                    { targets: 4, className: 'text-nowrap', orderable: false } // Actions
                ],
                language: {
                    search: "Search Users:",
                    lengthMenu: "Show _MENU_ users per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ users"
                },
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                order: [[0, 'asc']], // Sort by Team name ascending
                initComplete: function() {
                    $('.dataTables_wrapper').css('width', '100%');
                },
                drawCallback: function() {
                    $('.dataTables_wrapper').css('width', '100%');
                }
            });

            // Auto-calculate monthly target on input change
            function calculateMonthlyTarget(row) {
                const total = parseInt(row.find('.daily-target-total').val()) || 0;
                const workingDays = parseInt(row.find('.working-days').val()) || 0;
                const monthlyTarget = total * workingDays;
                
                row.find('.monthly-target').text(monthlyTarget);
            }

            // Listen to input changes
            $('#datatable tbody').on('input', '.daily-target-total, .working-days', function() {
                const row = $(this).closest('tr');
                calculateMonthlyTarget(row);
            });

            // Save individual row
            $(document).on('click', '.save-row-btn', function() {
                const button = $(this);
                const row = button.closest('tr');
                const userId = row.data('user-id');
                
                if (!userId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'User ID not found',
                    });
                    return;
                }

                // Disable button and show loading
                button.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Saving...');

                const data = {
                    user_id: userId,
                    daily_target_total: parseInt(row.find('.daily-target-total').val()) || 0,
                    daily_target_new: parseInt(row.find('.daily-target-new').val()) || 0,
                    daily_target_existing: parseInt(row.find('.daily-target-existing').val()) || 0,
                    working_days: parseInt(row.find('.working-days').val()) || 0,
                    _token: '{{ csrf_token() }}'
                };

                $.ajax({
                    url: '{{ route("admin.targets.update") }}',
                    method: 'POST',
                    data: data,
                    success: function(response) {
                        if (response.success) {
                            // Update monthly target
                            row.find('.monthly-target').text(response.monthly_target);
                            
                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Saved!',
                                text: 'Target updated successfully',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to save target. Please try again.',
                        });
                    },
                    complete: function() {
                        button.prop('disabled', false).html('<i class="bx bx-save"></i> Save');
                    }
                });
            });

            // Save all targets (top button functionality)
            $('#saveAllBtn').on('click', function() {
                const button = $(this);
                button.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Saving...');

                const targets = [];
                
                $('#datatable tbody tr').each(function() {
                    const row = $(this);
                    const userId = row.data('user-id');
                    
                    if (userId) {
                        targets.push({
                            user_id: userId,
                            daily_target_total: parseInt(row.find('.daily-target-total').val()) || 0,
                            daily_target_new: parseInt(row.find('.daily-target-new').val()) || 0,
                            daily_target_existing: parseInt(row.find('.daily-target-existing').val()) || 0,
                            working_days: parseInt(row.find('.working-days').val()) || 0,
                        });
                    }
                });

                if (targets.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Data',
                        text: 'No staff members to save',
                    });
                    button.prop('disabled', false).html('<i class="bx bx-save"></i> Save All');
                    return;
                }

                $.ajax({
                    url: '{{ route("admin.targets.save-all") }}',
                    method: 'POST',
                    data: {
                        targets: targets,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to save targets. Please try again.',
                        });
                    },
                    complete: function() {
                        button.prop('disabled', false).html('<i class="bx bx-save"></i> Save All');
                    }
                });
            });
        });
    </script>
@endpush