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
            background: #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
            opacity: 0.5;
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
                                            @php
                                                // Get working days from calendar table if exists, otherwise from daily_targets
                                                $currentCalendar = $user->workingDaysCalendar->first();

                                                if ($currentCalendar) {
                                                    $workingDays = $currentCalendar->total_working_days;
                                                } else {
                                                    // Fallback: Calculate working days (weekdays) for current month
                                                    $workingDays = $user->dailyTarget->working_days ?? 0;
                                                }
                                            @endphp
                                        <tr data-user-id="{{ $user->id }}">
                                            <td><strong>{{ $user->name }}</strong></td>
                                            <td>
                                                <div class="d-flex gap-2 align-items-center" style="white-space: nowrap;">
                                                    <strong class="">
                                                        {{ ($user->dailyTarget->daily_target_new ?? 0) + ($user->dailyTarget->daily_target_existing ?? 0) }}
                                                    </strong>
                                                    <!-- <input
                                                        type="text"
                                                        class="form-control form-control-sm daily-target-total daily-target-total-display"
                                                        placeholder="Total"
                                                        value="{{ ($user->dailyTarget->daily_target_new ?? 0) + ($user->dailyTarget->daily_target_existing ?? 0) }}"
                                                        readonly
                                                        style   ="width: 70px;"
                                                    > -->
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
                    <small class="text-info">Click dates to select/deselect working days for current month</small>
                </div>

                <!-- Color Legend -->
                <div class="d-flex justify-content-center gap-3 mb-3 flex-wrap" style="font-size: 0.85rem;">
                    <span><span style="display:inline-block; width:15px; height:15px; background:#007bff; border-radius:3px;"></span> Selected</span>
                    <span><span style="display:inline-block; width:15px; height:15px; background:#fff3cd; border-radius:3px; border:1px solid #ddd;"></span> Weekend</span>
                    <span><span style="display:inline-block; width:15px; height:15px; background:#e9ecef; border-radius:3px;"></span> Past Day</span>
                    <span><span style="display:inline-block; width:15px; height:15px; background:white; border:3px solid #28a745; border-radius:3px;"></span> Today</span>
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

            // Auto-calculate total and monthly target
            function calculateTargets(row) {
                const newTarget = parseInt(row.find('.daily-target-new').val()) || 0;
                const existingTarget = parseInt(row.find('.daily-target-existing').val()) || 0;
                const total = newTarget + existingTarget;

                // Update the total field
                row.find('.daily-target-total').val(total);

                // Calculate monthly target
                const workingDays = parseInt(row.find('.working-days').val()) || 0;
                const monthlyTarget = total * workingDays;

                row.find('.monthly-target').text(monthlyTarget);
            }

            // Listen to input changes on New, Existing, and Working Days
            $('#datatable tbody').on('input', '.daily-target-new, .daily-target-existing, .working-days', function() {
                const row = $(this).closest('tr');
                calculateTargets(row);
            });

            // Calculate targets for all rows on page load
            $('#datatable tbody tr').each(function() {
                calculateTargets($(this));
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

                const newTarget = parseInt(row.find('.daily-target-new').val()) || 0;
                const existingTarget = parseInt(row.find('.daily-target-existing').val()) || 0;
                const total = newTarget + existingTarget;

                const data = {
                    user_id: userId,
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
                            // Update the Total display (bold number in Daily Target column)
                            row.find('td:eq(1) strong').first().text(total);

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

            // ========================================
            // WORKING DAYS CALENDAR FUNCTIONALITY
            // ========================================

            let currentUserId = null;
            let currentUserName = '';
            let selectedDays = [];
            const currentDate = new Date();
            const currentYear = currentDate.getFullYear();
            const currentMonth = currentDate.getMonth() + 1; // 1-12
            const today = currentDate.getDate();

            // Open calendar modal
            $(document).on('click', '.open-calendar-btn', function() {
                const button = $(this);
                currentUserId = button.data('user-id');
                currentUserName = button.data('user-name');

                // Update modal title
                $('#modal-user-name').text(currentUserName);

                // Set month/year display
                const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'];
                $('#current-month-year').text(`${monthNames[currentMonth - 1]} ${currentYear}`);

                // Load saved working days for this user/month
                loadWorkingDays();

                // Show modal
                $('#workingDaysModal').modal('show');
            });

            // Load working days from database
            function loadWorkingDays() {
                $.ajax({
                    url: '{{ route("admin.targets.calendar.get") }}',
                    method: 'POST',
                    data: {
                        user_id: currentUserId,
                        year: currentYear,
                        month: currentMonth,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Convert string days to integers
                            selectedDays = (response.working_days || []).map(day => parseInt(day));
                            console.log('Loaded working days:', selectedDays);
                            renderCalendar();
                        }
                    },
                    error: function() {
                        selectedDays = [];
                        renderCalendar();
                    }
                });
            }

            // Render calendar grid
            function renderCalendar() {
                const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
                const firstDay = new Date(currentYear, currentMonth - 1, 1).getDay(); // 0 = Sunday
                const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

                console.log('Rendering calendar with selected days:', selectedDays);

                let html = '';

                // Day headers
                dayNames.forEach(day => {
                    html += `<div class="calendar-day day-header">${day}</div>`;
                });

                // Empty cells before first day
                for (let i = 0; i < firstDay; i++) {
                    html += '<div class="calendar-day empty-day"></div>';
                }

                // Days of month
                for (let day = 1; day <= daysInMonth; day++) {
                    const date = new Date(currentYear, currentMonth - 1, day);
                    const dayOfWeek = date.getDay();
                    const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
                    const isToday = day === today;
                    const isPast = day < today;
                    const isSelected = selectedDays.includes(day);

                    if (isSelected) {
                        console.log(`Day ${day} is marked as selected`);
                    }

                    let classes = 'calendar-day';
                    if (isSelected) classes += ' selected';
                    if (isWeekend) classes += ' weekend';
                    if (isToday) classes += ' today';
                    if (isPast) classes += ' past-day';

                    html += `<div class="${classes}" data-day="${day}">${day}</div>`;
                }

                $('#calendar-grid').html(html);
                updateSelectedCount();
            }

            // Toggle day selection
            $(document).on('click', '.calendar-day:not(.day-header):not(.empty-day):not(.past-day)', function() {
                const day = parseInt($(this).data('day'));
                const index = selectedDays.indexOf(day);

                if (index > -1) {
                    // Deselect
                    selectedDays.splice(index, 1);
                    $(this).removeClass('selected');
                } else {
                    // Select
                    selectedDays.push(day);
                    $(this).addClass('selected');
                }

                updateSelectedCount();
            });

            // Update selected days count
            function updateSelectedCount() {
                $('#selected-days-count').text(selectedDays.length);
            }

            // Save working days
            $('#save-calendar-btn').on('click', function() {
                const button = $(this);
                button.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Saving...');

                $.ajax({
                    url: '{{ route("admin.targets.calendar.save") }}',
                    method: 'POST',
                    data: {
                        user_id: currentUserId,
                        year: currentYear,
                        month: currentMonth,
                        working_days: selectedDays,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update the button display in the table
                            const row = $(`tr[data-user-id="${currentUserId}"]`);
                            row.find('.working-days-count').text(response.total_working_days);
                            row.find('.working-days').val(response.total_working_days);

                            // Update monthly target from server response (already saved in DB)
                            row.find('.monthly-target').text(response.monthly_target);

                            // Close modal
                            $('#workingDaysModal').modal('hide');

                            // Show success message
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