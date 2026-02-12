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

        /* Alternating row colors for better visual appeal */
        #datatable tbody tr:nth-child(odd) {
            background-color: #f8f9fa;
        }

        #datatable tbody tr:nth-child(even) {
            background-color: #ffffff;
        }

        #datatable tbody tr:hover {
            background-color: #e9ecef !important;
        }

        /* Total field styling - make it look disabled */
        .daily-target-total-display {
            background-color: #e9ecef;
            color: #495057;
            font-weight: 600;
            cursor: not-allowed;
        }

        /* Calendar Grid Styling */
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #dee2e6;
            border-radius: 6px;
            background: white;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
            min-height: 45px;
        }

        .calendar-day:hover:not(.day-header):not(.empty-day):not(.past-day) {
            border-color: #007bff;
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(0, 123, 255, 0.2);
        }

        .calendar-day.selected {
            background: #007bff !important;
            color: white !important;
            border-color: #0056b3 !important;
        }

        .calendar-day.selected:hover {
            background: #0056b3 !important;
        }

        /* Half-day styling */
        .calendar-day.half-day {
            background: linear-gradient(135deg, #007bff 50%, #fff 50%) !important;
            color: #007bff !important;
            border-color: #007bff !important;
            font-weight: 700;
        }

        .calendar-day.half-day:hover {
            background: linear-gradient(135deg, #0056b3 50%, #f0f0f0 50%) !important;
        }

        .calendar-day.weekend.half-day {
            background: linear-gradient(135deg, #ffc107 50%, #fff3cd 50%) !important;
            color: #856404 !important;
            border-color: #ffc107 !important;
        }

        .calendar-day.past-day.half-day {
            background: linear-gradient(135deg, #6c757d 50%, #e9ecef 50%) !important;
            color: #495057 !important;
            border-color: #6c757d !important;
        }

        .calendar-day.day-header {
            background: #007bff;
            color: white;
            border-color: #007bff;
            cursor: default;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .calendar-day.empty-day {
            background: transparent;
            border-color: transparent;
            cursor: default;
        }

        .calendar-day.past-day {
            background: #f0f0f0;
            color: #6c757d;
            cursor: pointer;
            opacity: 0.8;
        }

        .calendar-day.past-day:hover {
            border-color: #007bff;
            opacity: 1;
        }

        .calendar-day.past-day.selected {
            background: #6c757d !important;
            color: white !important;
            border-color: #495057 !important;
            opacity: 1;
        }

        .calendar-day.weekend {
            background: #fff3cd;
        }

        .calendar-day.weekend.selected {
            background: #ffc107 !important;
            color: #212529 !important;
            border-color: #e0a800 !important;
        }

        .calendar-day.today {
            border-color: #28a745;
            border-width: 3px;
            font-weight: 700;
        }

        /* Month/Year Selector Styling */
        .month-year-selector {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: nowrap;
        }

        .month-year-selector label {
            margin: 0;
            white-space: nowrap;
            font-size: 14px;
        }

        .month-year-selector select {
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            min-width: 110px;
            height: 38px;
        }

        .month-year-selector select:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .month-year-selector .btn {
            height: 38px;
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 8px 16px;
            border-radius: 6px;
        }

        /* No targets alert styling */
        .no-targets-alert {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        .no-targets-alert .alert-message {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #856404;
        }

        .no-targets-alert .alert-message i {
            font-size: 1.5rem;
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

            .month-year-selector {
                flex-wrap: wrap;
            }

            .no-targets-alert {
                flex-direction: column;
                text-align: center;
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
        tr > th {
            font-size: 16px !important;
        }
        tr > td {
            font-size: 16px !important;
        }
        tr > td > button.btn {
            font-size: 16px;
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
                                <div class="page-title-box d-flex align-items-center justify-content-between flex-wrap gap-3">
                                    <h4 class="mb-0">Managing Targets</h4>
                                    <div class="page-title-right d-flex align-items-center gap-3 flex-wrap">
                                        <!-- Month/Year Selector -->
                                        <div class="month-year-selector">
                                            <label class="mb-0"><strong>Period:</strong></label>
                                            <select id="monthSelector" class="form-select form-select-sm">
                                                @foreach($monthNames as $num => $name)
                                                    <option value="{{ $num }}" {{ $selectedMonth == $num ? 'selected' : '' }}>
                                                        {{ $name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <select id="yearSelector" class="form-select form-select-sm">
                                                @foreach($yearOptions as $year)
                                                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                                        {{ $year }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-primary btn-sm" id="loadPeriodBtn">
                                                <i class="bx bx-filter"></i> Filter
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <!-- No Targets Alert with Copy Button -->
                                @if(!$hasTargetsForMonth)
                                    <div class="no-targets-alert" id="noTargetsAlert">
                                        <div class="alert-message">
                                            <i class="bx bx-info-circle"></i>
                                            <span>No targets have been set for <strong>{{ $monthNames[$selectedMonth] }} {{ $selectedYear }}</strong>.</span>
                                        </div>
                                        @if($hasPreviousMonthTargets)
                                            <button type="button" class="btn btn-warning btn-sm" id="copyFromPreviousBtn">
                                                <i class="bx bx-copy"></i> Copy from Previous Month
                                            </button>
                                        @endif
                                    </div>
                                @endif

                                <table id="datatable" class="table table-bordered dt-responsive nowrap data-table-area">
                                    <thead>
                                        <tr>
                                            <th>Team</th>
                                            <th>Daily Target <small class="text-muted" style="color:green !important;">(Total / New / Existing)</small></th>
                                            <th># of Working Days</th>
                                            <th>Monthly Target</th>
                                            <th>Hide</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($staffUsers as $user)
                                            @php
                                                // Get the target for this user (already filtered by year/month in controller)
                                                $userTarget = $user->dailyTarget;

                                                // Get working days from calendar table if exists, otherwise from daily_targets
                                                $currentCalendar = $user->workingDaysCalendar->first();

                                                if ($currentCalendar) {
                                                    $workingDays = $currentCalendar->total_working_days;
                                                } else {
                                                    $workingDays = $userTarget->working_days ?? 0;
                                                }
                                            @endphp
                                        <tr data-user-id="{{ $user->id }}">
                                            <td>
                                                <a href="{{ route('admin.staff-sales-dashboard.index', ['user_id' => $user->id]) }}"
                                                   class="text-decoration-none"
                                                   title="View {{ $user->name }}'s Dashboard">
                                                    <strong class="text-primary">{{ $user->name }}</strong>
                                                    <i class="bx bx-link-external ms-1" style="font-size: 0.8rem;"></i>
                                                </a>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2 align-items-center" style="white-space: nowrap;">
                                                    <strong class="">
                                                        {{ ($userTarget->daily_target_new ?? 0) + ($userTarget->daily_target_existing ?? 0) }}
                                                    </strong>
                                                    <span class="align-self-center">/</span>
                                                    <input
                                                        type="number"
                                                        class="form-control form-control-sm daily-target-new"
                                                        placeholder="New"
                                                        value="{{ $userTarget->daily_target_new ?? 0 }}"
                                                        min="0"
                                                        style="width: 70px;"
                                                    >
                                                    <span class="align-self-center">/</span>
                                                    <input
                                                        type="number"
                                                        class="form-control form-control-sm daily-target-existing"
                                                        placeholder="Existing"
                                                        value="{{ $userTarget->daily_target_existing ?? 0 }}"
                                                        min="0"
                                                        style="width: 70px;"
                                                    >
                                                </div>
                                            </td>
                                            <td>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary open-calendar-btn"
                                                    data-user-id="{{ $user->id }}"
                                                    data-user-name="{{ $user->name }}"
                                                >
                                                    <i class="bx bx-calendar"></i>
                                                    <span class="working-days-count">{{ $workingDays }}</span> Days
                                                </button>
                                                <input type="hidden" class="working-days" value="{{ $workingDays }}">
                                            </td>
                                            <td>
                                                <strong class="monthly-target text-primary">
                                                    {{ $userTarget ? $userTarget->monthly_target : 0 }}
                                                </strong>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check d-flex justify-content-center">
                                                    <input
                                                        type="checkbox"
                                                        class="form-check-input hide-checkbox"
                                                        data-user-id="{{ $user->id }}"
                                                        {{ $user->hide_from_dashboard ? 'checked' : '' }}
                                                        style="width: 20px; height: 20px; cursor: pointer;"
                                                    >
                                                </div>
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

<!-- Working Days Calendar Modal -->
<div class="modal fade" id="workingDaysModal" tabindex="-1" aria-labelledby="workingDaysModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="workingDaysModalLabel">
                    <i class="bx bx-calendar"></i> Select Working Days - <span id="modal-user-name"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <h6 class="text-muted" id="current-month-year"></h6>
                    <small class="text-info">Click dates to select/deselect working days</small>
                </div>

                <!-- Color Legend -->
                <div class="d-flex justify-content-center gap-3 mb-3 flex-wrap" style="font-size: 0.85rem;">
                    <span><span style="display:inline-block; width:15px; height:15px; background:#007bff; border-radius:3px;"></span> Full Day</span>
                    <span><span style="display:inline-block; width:15px; height:15px; background:linear-gradient(135deg, #007bff 50%, #fff 50%); border-radius:3px; border:1px solid #007bff;"></span> Half Day</span>
                    <span><span style="display:inline-block; width:15px; height:15px; background:#fff3cd; border-radius:3px; border:1px solid #ddd;"></span> Weekend</span>
                    <span><span style="display:inline-block; width:15px; height:15px; background:#e9ecef; border-radius:3px;"></span> Past Day</span>
                    <span><span style="display:inline-block; width:15px; height:15px; background:white; border:3px solid #28a745; border-radius:3px;"></span> Today</span>
                </div>
                <div class="text-center mb-2">
                    <small class="text-muted">Click: Off → Full Day → Half Day → Off</small>
                </div>

                <!-- Calendar Grid -->
                <div id="calendar-grid" class="calendar-grid">
                    <!-- Calendar will be generated here by JavaScript -->
                </div>

                <div class="mt-3 text-center">
                    <strong>Total Selected Days: <span id="selected-days-count" class="text-primary">0</span></strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="save-calendar-btn">
                    <i class="bx bx-save"></i> Save Working Days
                </button>
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
            // Selected year and month from server
            let selectedYear = {{ $selectedYear }};
            let selectedMonth = {{ $selectedMonth }};

            // Month names for display
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'];

            // Initialize DataTable with responsive options
            if ($.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable().destroy();
            }

            var table = $('#datatable').DataTable({
                responsive: false,
                scrollX: true,
                scrollCollapse: false,
                autoWidth: true,
                columnDefs: [
                    { targets: 0, className: 'text-nowrap' },
                    { targets: 1, className: 'text-nowrap', orderable: false },
                    { targets: 2, className: 'text-nowrap' },
                    { targets: 3, className: 'text-nowrap' },
                    { targets: 4, className: 'text-center', orderable: false }, // Hide
                    { targets: 5, className: 'text-nowrap', orderable: false }  // Actions
                ],
                language: {
                    search: "Search Users:",
                    lengthMenu: "Show _MENU_ users per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ users"
                },
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                order: [[0, 'asc']],
                initComplete: function() {
                    $('.dataTables_wrapper').css('width', '100%');
                },
                drawCallback: function() {
                    $('.dataTables_wrapper').css('width', '100%');
                }
            });

            // ========================================
            // MONTH/YEAR SELECTOR FUNCTIONALITY
            // ========================================

            // Load period button - redirect with new month/year
            $('#loadPeriodBtn').on('click', function() {
                const month = $('#monthSelector').val();
                const year = $('#yearSelector').val();
                window.location.href = `{{ route('admin.targets.index') }}?year=${year}&month=${month}`;
            });

            // Copy from previous month
            $('#copyFromPreviousBtn').on('click', function() {
                const button = $(this);

                Swal.fire({
                    title: 'Copy Targets?',
                    text: 'This will copy all targets from the previous month to the current selected month.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, copy them!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        button.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Copying...');

                        $.ajax({
                            url: '{{ route("admin.targets.copy-previous") }}',
                            method: 'POST',
                            data: {
                                year: selectedYear,
                                month: selectedMonth,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Copied!',
                                        text: response.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        // Reload page to show copied targets
                                        window.location.reload();
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: xhr.responseJSON?.message || 'Failed to copy targets.',
                                });
                                button.prop('disabled', false).html('<i class="bx bx-copy"></i> Copy from Previous Month');
                            }
                        });
                    }
                });
            });

            // ========================================
            // HIDE FROM DASHBOARD FUNCTIONALITY
            // ========================================

            $(document).on('change', '.hide-checkbox', function() {
                const checkbox = $(this);
                const userId = checkbox.data('user-id');
                const isHidden = checkbox.is(':checked');

                $.ajax({
                    url: '{{ route("admin.targets.toggle-hide") }}',
                    method: 'POST',
                    data: {
                        user_id: userId,
                        hide: isHidden,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: isHidden ? 'Hidden!' : 'Visible!',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        // Revert checkbox on error
                        checkbox.prop('checked', !isHidden);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to update hide status.',
                        });
                    }
                });
            });

            // ========================================
            // TARGET CALCULATION FUNCTIONALITY
            // ========================================

            function calculateTargets(row) {
                const newTarget = parseInt(row.find('.daily-target-new').val()) || 0;
                const existingTarget = parseInt(row.find('.daily-target-existing').val()) || 0;
                const total = newTarget + existingTarget;

                row.find('.daily-target-total').val(total);

                const workingDays = parseInt(row.find('.working-days').val()) || 0;
                const monthlyTarget = total * workingDays;

                row.find('.monthly-target').text(monthlyTarget);
            }

            $('#datatable tbody').on('input', '.daily-target-new, .daily-target-existing, .working-days', function() {
                const row = $(this).closest('tr');
                calculateTargets(row);
            });

            $('#datatable tbody tr').each(function() {
                calculateTargets($(this));
            });

            // ========================================
            // SAVE INDIVIDUAL ROW
            // ========================================

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

                button.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Saving...');

                const newTarget = parseInt(row.find('.daily-target-new').val()) || 0;
                const existingTarget = parseInt(row.find('.daily-target-existing').val()) || 0;
                const total = newTarget + existingTarget;

                const data = {
                    user_id: userId,
                    year: selectedYear,
                    month: selectedMonth,
                    daily_target_total: total,
                    daily_target_new: newTarget,
                    daily_target_existing: existingTarget,
                    working_days: parseInt(row.find('.working-days').val()) || 0,
                    _token: '{{ csrf_token() }}'
                };

                $.ajax({
                    url: '{{ route("admin.targets.update") }}',
                    method: 'POST',
                    data: data,
                    success: function(response) {
                        if (response.success) {
                            row.find('td:eq(1) strong').first().text(total);
                            row.find('.monthly-target').text(response.monthly_target);

                            // Hide the no targets alert if it exists
                            $('#noTargetsAlert').fadeOut();

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

            // ========================================
            // SAVE ALL TARGETS
            // ========================================

            $('#saveAllBtn').on('click', function() {
                const button = $(this);
                button.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Saving...');

                const targets = [];

                $('#datatable tbody tr').each(function() {
                    const row = $(this);
                    const userId = row.data('user-id');

                    if (userId) {
                        const newTarget = parseInt(row.find('.daily-target-new').val()) || 0;
                        const existingTarget = parseInt(row.find('.daily-target-existing').val()) || 0;
                        const total = newTarget + existingTarget;

                        targets.push({
                            user_id: userId,
                            daily_target_total: total,
                            daily_target_new: newTarget,
                            daily_target_existing: existingTarget,
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
                        year: selectedYear,
                        month: selectedMonth,
                        targets: targets,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
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

            // ========================================
            // WORKING DAYS CALENDAR FUNCTIONALITY
            // ========================================

            let currentUserId = null;
            let currentUserName = '';
            // Changed from array to object: { day: value } where value is 0, 0.5, or 1
            let workingDaysData = {};
            const currentDate = new Date();
            const todayYear = currentDate.getFullYear();
            const todayMonth = currentDate.getMonth() + 1;
            const todayDate = currentDate.getDate();

            $(document).on('click', '.open-calendar-btn', function() {
                const button = $(this);
                currentUserId = button.data('user-id');
                currentUserName = button.data('user-name');

                $('#modal-user-name').text(currentUserName);
                $('#current-month-year').text(`${monthNames[selectedMonth - 1]} ${selectedYear}`);

                loadWorkingDays();
                $('#workingDaysModal').modal('show');
            });

            function loadWorkingDays() {
                $.ajax({
                    url: '{{ route("admin.targets.calendar.get") }}',
                    method: 'POST',
                    data: {
                        user_id: currentUserId,
                        year: selectedYear,
                        month: selectedMonth,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Handle both old format (array) and new format (object)
                            const rawData = response.working_days || {};

                            if (Array.isArray(rawData)) {
                                // Old format: convert array to object with value 1
                                workingDaysData = {};
                                rawData.forEach(day => {
                                    workingDaysData[parseInt(day)] = 1;
                                });
                            } else {
                                // New format: object with day => value
                                workingDaysData = {};
                                Object.keys(rawData).forEach(day => {
                                    workingDaysData[parseInt(day)] = parseFloat(rawData[day]);
                                });
                            }
                            renderCalendar();
                        }
                    },
                    error: function() {
                        workingDaysData = {};
                        renderCalendar();
                    }
                });
            }

            function renderCalendar() {
                const daysInMonth = new Date(selectedYear, selectedMonth, 0).getDate();
                const firstDay = new Date(selectedYear, selectedMonth - 1, 1).getDay();
                const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

                // Determine if we're viewing current month
                const isCurrentMonth = (selectedYear === todayYear && selectedMonth === todayMonth);
                const isPastMonth = (selectedYear < todayYear) || (selectedYear === todayYear && selectedMonth < todayMonth);

                let html = '';

                dayNames.forEach(day => {
                    html += `<div class="calendar-day day-header">${day}</div>`;
                });

                for (let i = 0; i < firstDay; i++) {
                    html += '<div class="calendar-day empty-day"></div>';
                }

                for (let day = 1; day <= daysInMonth; day++) {
                    const date = new Date(selectedYear, selectedMonth - 1, day);
                    const dayOfWeek = date.getDay();
                    const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
                    const isToday = isCurrentMonth && day === todayDate;

                    // Past day logic: only applies to current month
                    let isPast = false;
                    if (isCurrentMonth && day < todayDate) {
                        isPast = true;
                    }

                    // Get value for this day (0, 0.5, or 1)
                    const dayValue = workingDaysData[day] || 0;
                    const isFullDay = dayValue === 1;
                    const isHalfDay = dayValue === 0.5;

                    let classes = 'calendar-day';
                    if (isFullDay) classes += ' selected';
                    if (isHalfDay) classes += ' half-day';
                    if (isWeekend) classes += ' weekend';
                    if (isToday) classes += ' today';
                    if (isPast) classes += ' past-day';

                    html += `<div class="${classes}" data-day="${day}" data-value="${dayValue}">${day}</div>`;
                }

                $('#calendar-grid').html(html);
                updateSelectedCount();
            }

            // Click cycling: 0 → 1 → 0.5 → 0
            $(document).on('click', '.calendar-day:not(.day-header):not(.empty-day)', function() {
                const day = parseInt($(this).data('day'));
                const currentValue = workingDaysData[day] || 0;

                let newValue;
                if (currentValue === 0) {
                    newValue = 1; // Off → Full Day
                } else if (currentValue === 1) {
                    newValue = 0.5; // Full Day → Half Day
                } else {
                    newValue = 0; // Half Day → Off
                }

                // Update data
                if (newValue === 0) {
                    delete workingDaysData[day];
                } else {
                    workingDaysData[day] = newValue;
                }

                // Update UI
                $(this).removeClass('selected half-day');
                $(this).data('value', newValue);

                if (newValue === 1) {
                    $(this).addClass('selected');
                } else if (newValue === 0.5) {
                    $(this).addClass('half-day');
                }

                updateSelectedCount();
            });

            function updateSelectedCount() {
                // Sum all values (full day = 1, half day = 0.5)
                let total = 0;
                Object.values(workingDaysData).forEach(value => {
                    total += parseFloat(value);
                });

                // Display with one decimal if needed
                const displayValue = total % 1 === 0 ? total : total.toFixed(1);
                $('#selected-days-count').text(displayValue);
            }

            $('#save-calendar-btn').on('click', function() {
                const button = $(this);
                button.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Saving...');

                $.ajax({
                    url: '{{ route("admin.targets.calendar.save") }}',
                    method: 'POST',
                    data: {
                        user_id: currentUserId,
                        year: selectedYear,
                        month: selectedMonth,
                        working_days: JSON.stringify(workingDaysData),
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            const row = $(`tr[data-user-id="${currentUserId}"]`);
                            // Display with one decimal if needed
                            const displayValue = response.total_working_days % 1 === 0
                                ? response.total_working_days
                                : parseFloat(response.total_working_days).toFixed(1);
                            row.find('.working-days-count').text(displayValue);
                            row.find('.working-days').val(response.total_working_days);
                            row.find('.monthly-target').text(response.monthly_target);

                            $('#workingDaysModal').modal('hide');

                            Swal.fire({
                                icon: 'success',
                                title: 'Saved!',
                                text: 'Working days saved successfully',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to save working days. Please try again.',
                        });
                    },
                    complete: function() {
                        button.prop('disabled', false).html('<i class="bx bx-save"></i> Save Working Days');
                    }
                });
            });
        });
    </script>
@endpush
