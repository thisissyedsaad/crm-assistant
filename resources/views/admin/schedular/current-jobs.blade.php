@extends('admin.layouts.app')

@section('title', 'Current Jobs | CSD Assistant')

@push('links')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/select.dataTables.min.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Dashboard Cards Styling */
        .dashboard-cards {
            margin-bottom: 30px;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 15px;
            padding: 25px;
            color: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }
        
        .stats-card:hover::before {
            transform: translateX(100%);
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }
        
        .stats-card.total-jobs {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .stats-card.collections-overdue {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .stats-card.deliveries-overdue {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .stats-card.delivered {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        
        .stats-card .card-icon {
            font-size: 3rem;
            opacity: 0.3;
            position: absolute;
            top: 20px;
            right: 20px;
        }
        
        .stats-card .card-content {
            position: relative;
            z-index: 2;
        }
        
        .stats-card .card-title {
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            opacity: 0.9;
        }
        
        .stats-card .card-value {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 8px;
        }
        
        .stats-card .card-subtitle {
            font-size: 0.85rem;
            opacity: 0.8;
            font-weight: 400;
        }
        
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .stats-card {
                padding: 20px;
                margin-bottom: 15px;
            }
            
            .stats-card .card-value {
                font-size: 2rem;
            }
            
            .stats-card .card-icon {
                font-size: 2.5rem;
            }
        }

        /* Smart responsive table styling */
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
            min-width: 140px; 
            width: 8%; 
        } /* Date */
        
        #datatable th:nth-child(2), #datatable td:nth-child(2) { 
            min-width: 120px; 
            width: 8%; 
        } /* Order Number */
        
        #datatable th:nth-child(3), #datatable td:nth-child(3) { 
            min-width: 120px; 
            width: 8%; 
        } /* User */
        
        #datatable th:nth-child(4), #datatable td:nth-child(4) { 
            min-width: 120px; 
            width: 8%; 
        } /* Collection Date */
        
        #datatable th:nth-child(5), #datatable td:nth-child(5) { 
            min-width: 120px; 
            width: 7%; 
        } /* Collection Time */
        
        #datatable th:nth-child(6), #datatable td:nth-child(6) { 
            min-width: 140px; 
            width: 8%; 
        } /* Driver Loaded Time */
        
        #datatable th:nth-child(7), #datatable td:nth-child(7) { 
            min-width: 100px; 
            width: 6%; 
        } /* Sale Price */
        
        #datatable th:nth-child(8), #datatable td:nth-child(8) { 
            min-width: 120px; 
            width: 7%; 
        } /* ETA Delivery */
        
        #datatable th:nth-child(9), #datatable td:nth-child(9) { 
            min-width: 120px; 
            width: 7%; 
        } /* Mid-Point Check */
        
        #datatable th:nth-child(10), #datatable td:nth-child(10) { 
            min-width: 100px; 
            width: 6%; 
        } /* Notes */
        
        #datatable th:nth-child(11), #datatable td:nth-child(11) { 
            min-width: 140px; 
            width: 8%; 
        } /* Collection Check-In */
        
        #datatable th:nth-child(12), #datatable td:nth-child(12) { 
            min-width: 140px; 
            width: 8%; 
        } /* Driver Confirmed ETA */
        
        #datatable th:nth-child(13), #datatable td:nth-child(13) { 
            min-width: 160px; 
            width: 9%; 
        } /* Mid-Point Check Complete */
        
        /* Most columns should not wrap by default */
        #datatable th,
        #datatable td {
            white-space: nowrap;
        }
        
        /* Status badge colors */
        .status-quote { background-color: #ffbc62 !important; color: #000 !important; }
        .status-open { background-color: #e097fd !important; color: #000 !important; }
        .status-mainopen { background-color: #ff8888 !important; color: #fff !important; }
        .status-planned { background-color: #ffff69 !important; color: #000 !important; }
        .status-signed-off { background-color: #88ccff !important; color: #000 !important; }
        .status-checked { background-color: #88ff88 !important; color: #000 !important; }
        .status-invoiced { background-color: #e0e1df !important; color: #000 !important; }
        
        /* Comments button styling */
        .btn-view-comments {
            padding: 2px 8px;
            font-size: 11px;
            border-radius: 3px;
        }
        
        /* Modal styling */
        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        
        .comments-content {
            max-height: 400px;
            overflow-y: auto;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #e9ecef;
            white-space: pre-wrap;
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

        /* Autocomplete Search Styles */
        .autocomplete-container {
            position: relative;
            width: 100%;
            max-width: 600px;
        }
        
        .autocomplete-input {
            width: 100%;
            padding: 12px 16px;
            font-size: 16px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            transition: border-color 0.3s ease;
        }
        
        .autocomplete-input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .autocomplete-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e1e5e9;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .autocomplete-item {
            padding: 12px 16px;
            cursor: pointer;
            border-bottom: 1px solid #f8f9fa;
            transition: background-color 0.2s ease;
        }
        
        .autocomplete-item:hover,
        .autocomplete-item.active {
            background-color: #f8f9fa;
        }
        
        .autocomplete-item:last-child {
            border-bottom: none;
        }
        
        .autocomplete-loading {
            padding: 12px 16px;
            text-align: center;
            color: #6c757d;
            font-style: italic;
        }
        
        .autocomplete-no-results {
            padding: 12px 16px;
            text-align: center;
            color: #6c757d;
            font-style: italic;
        }
        
        .order-info {
            font-weight: 500;
            color: #495057;
        }
        
        .order-details {
            font-size: 0.9em;
            color: #6c757d;
            margin-top: 2px;
        }

        /* Show button styling */
        .show-order-status {
            color: #007bff;
            cursor: pointer;
            text-decoration: underline;
            font-size: 0.85em;
        }

        .show-order-status:hover {
            color: #0056b3;
        }

        .status-loading {
            color: #6c757d;
            font-style: italic;
            font-size: 0.85em;
        }

        span#modalOrderNumber {
            font-size: 15px;
            text-decoration: none !important;
        }
        .order-num > a{
            color: #fff !important;
        }
    </style>
@endpush

@section('content')
<div class="main-content introduction-farm">
    <div class="content-wraper-area">
        <div class="data-table-area">
            <div class="container-fluid">
                <!-- Dashboard Cards Row -->
                <div class="row g-4 dashboard-cards">
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card stats-card total-jobs">
                            <div class="card-content">
                                <div class="card-title">Total Jobs</div>
                                <div class="card-value" id="totalJobs">
                                    {{ $countData['totalJobs'] }}
                                </div>
                                <div class="card-subtitle">Active Jobs</div>
                            </div>
                            <div class="card-icon">
                                <i class="bx bx-briefcase"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card stats-card collections-overdue">
                            <div class="card-content">
                                <div class="card-title">Collections Overdue</div>
                                <div class="card-value" id="collectionsOverdue">
                                    {{ $countData['collectionsOverdue'] }}
                                </div>
                                <div class="card-subtitle">Pending Collections</div>
                            </div>
                            <div class="card-icon">
                                <i class="bx bx-time-five"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card stats-card deliveries-overdue">
                            <div class="card-content">
                                <div class="card-title">Deliveries Overdue</div>
                                <div class="card-value" id="deliveriesOverdue">
                                    {{ $countData['deliveriesOverdue'] }}
                                </div>
                                <div class="card-subtitle">Pending Deliveries</div>
                            </div>
                            <div class="card-icon">
                                <i class="bx bx-truck"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card stats-card delivered">
                            <div class="card-content">
                                <div class="card-title">Delivered</div>
                                <div class="card-value" id="delivered">
                                    {{ $countData['delivered'] }}
                                </div>
                                <div class="card-subtitle">Completed Today</div>
                            </div>
                            <div class="card-icon">
                                <i class="bx bx-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <table id="datatable" class="table table-bordered dt-responsive nowrap data-table-area">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Order Number</th>
                                            <th>User</th>
                                            <th>Collection Date</th>
                                            <th>Collection Time</th>
                                            <th>Driver Loaded (Time)</th>
                                            <th>Sale Price</th>
                                            <th>ETA Delivery</th>
                                            <th>Mid-Point Check</th>
                                            <th>Notes</th>
                                            <th>Collection Check-In</th>
                                            <th>Driver Confirmed ETA</th>
                                            <th>Mid-Point Check Complete</th>
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

<!-- Comments Modal -->
<div class="modal fade" id="commentsModal" tabindex="-1" aria-labelledby="commentsModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg">
       <div class="modal-content border-0 shadow-lg">
           <div class="modal-header bg-primary text-white border-0">
               <h5 class="modal-title d-flex align-items-center" id="commentsModalLabel">
                   <i class="bx bx-info-circle me-2"></i>Current Jobs
               </h5>
               <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
           </div>
           <div class="modal-body p-4">
               <div class="row mb-3">
                   <div class="col-sm-4">
                       <div class="d-flex align-items-center">
                           <i class="bx bx-hash text-secondary me-2"></i>
                           <strong>Order Number:</strong>
                       </div>
                   </div>
                   <div class="col-sm-8">
                        <span id="modalOrderNumber" class="badge bg-primary text-white" >-</span>
                   </div>
               </div>
               <div class="row mb-3">
                   <div class="col-sm-4">
                       <div class="d-flex align-items-center">
                           <i class="bx bx-buildings text-primary me-2"></i>
                           <strong>Company:</strong>
                       </div>
                   </div>
                   <div class="col-sm-8">
                       <span id="modalCompanyName" class="text-muted">-</span>
                   </div>
               </div>
               <div class="row mb-3">
                   <div class="col-sm-4">
                       <div class="d-flex align-items-center">
                           <i class="bx bx-buildings text-primary me-2"></i>
                           <strong>Driver Name:</strong>
                       </div>
                   </div>
                   <div class="col-sm-8">
                       <span id="modalCarrierName" class="text-muted">-</span>
                   </div>
               </div>
               <div class="row mb-3">
                   <div class="col-sm-4">
                       <div class="d-flex align-items-center">
                           <i class="bx bx-buildings text-primary me-2"></i>
                           <strong>New/Existing:</strong>
                       </div>
                   </div>
                   <div class="col-sm-8">
                       <span id="modalNewExist" class="text-muted">-</span>
                   </div>
               </div>
               <div class="mb-3">
                   <label class="form-label d-flex align-items-center mb-2">
                       <i class="bx bx-message-square-detail text-primary me-2"></i>
                       <strong>Comments:</strong>
                   </label>
                   <div id="modalCommentsContent" class="comments-content bg-light p-3 rounded border">
                       No comments available
                   </div>
               </div>
           </div>
           <div class="modal-footer border-0 bg-light">
               <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                   <i class="bx bx-check me-1"></i>Close
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

    <script>
        $(function () {
            // Initialize DataTable
            if ($.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable().destroy();
            }
            var table = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                scrollCollapse: false,
                autoWidth: true,
                responsive: false,
                ordering: true,
                order: [[0, 'desc']], // Default sort by first column (Date) descending
                ajax: {
                    url: "{{ route('admin.schedular.current-jobs.index') }}",
                    data: function (d) {
                        d.fromDate = $('#fromDate').val();
                        d.toDate = $('#toDate').val();
                    }
                },
                pageLength: 25,
                lengthMenu: [[25, 50, 100], [25, 50, 100]],
                columns: [
                    { 
                        data: 'updatedAt', 
                        name: 'updatedAt',
                        className: 'text-nowrap',
                        orderable: true,
                        title: 'Date'
                    },
                    { 
                        data: 'orderNo', 
                        name: 'orderNo',
                        className: 'text-nowrap',
                        orderable: true,
                        title: 'Order Number',
                        render: function(data, type, row) {
                            return data ? `<span class="order-num badge bg-primary"><a href="/admin/orders/${row.id}">${data}</a></span>` : '-'; 
                        }
                    },
                    { 
                        data: 'customerUserId', 
                        name: 'customerUserId',
                        className: 'text-nowrap',
                        orderable: true,
                        title: 'User',
                        render: function(data, type, row) {
                            return data ? data : '-';
                        }
                    },
                    { 
                        data: 'collectionDate', 
                        name: 'collectionDate',
                        className: 'text-nowrap',
                        orderable: true,
                        title: 'Collection Date',
                        render: function(data, type, row) {
                            return data ? data : '-';
                        }
                    },
                    { 
                        data: 'collectionTime', 
                        name: 'collectionTime',
                        className: 'text-nowrap',
                        orderable: true,
                        title: 'Collection Time',
                        render: function(data, type, row) {
                            return data ? data : '-';
                        }
                    },
                    { 
                        data: 'departureTime', 
                        name: 'departureTime',
                        className: 'text-nowrap',
                        orderable: true,
                        title: 'Driver Loaded (Time)',
                        render: function(data, type, row) {
                            return data ? data : '<span class="text-muted">Pending</span>';
                        }
                    },
                    { 
                        data: 'orderPrice', 
                        name: 'orderPrice',
                        className: 'text-nowrap text-end',
                        orderable: true,
                        title: 'Sale Price',
                        render: function(data, type, row) {
                            return data ? '£' + parseFloat(data).toFixed(2) : '-';
                        }
                    },
                    { 
                        data: 'deliveryTime', 
                        name: 'deliveryTime',
                        className: 'text-nowrap',
                        orderable: true,
                        title: 'ETA Delivery',
                        render: function(data, type, row) {
                            return data ? data : '-';
                        }
                    },
                    { 
                        data: 'midpointCheck', 
                        name: 'midpointCheck',
                        className: 'text-nowrap',
                        orderable: true,
                        title: 'Mid-Point Check',
                        render: function(data, type, row) {
                            return data ? data : '-';
                        }
                    },
                    { 
                        data: 'internalNotes', 
                        name: 'internalNotes',
                        className: 'text-wrap',
                        orderable: true,
                        title: 'Notes',
                        render: function(data, type, row) {
                            return `<button type="button" class="btn btn-sm btn-outline-primary btn-view-comments" 
                                           onclick="showActualModal('${row.orderNo || ''}', '${row.customerNo || ''}', \`${data.replace(/`/g, '\\`').replace(/\\/g, '\\\\')}\`, '${row.carrierNo || ''}')">
                                        <i class="fas fa-eye me-1"></i>View
                                    </button>`;
                        }
                    },
                    { 
                        data: 'collectionCheckIn', 
                        name: 'collectionCheckIn',
                        className: 'text-nowrap text-center',
                        orderable: false,
                        title: 'Collection Check-In',
                        render: function(data, type, row) {
                            if (data === true || data === 1) {
                                return `<span class="badge bg-success"><i class="fas fa-check"></i> Completed</span>`;
                            } else {
                                return `<button type="button" class="btn btn-sm btn-outline-primary" 
                                               onclick="confirmAction('Collection Check-In', ${row.id}, 'collection-checkin')">
                                            <i class="fas fa-clipboard-check"></i> Check-In
                                        </button>`;
                            }
                        }
                    },
                    { 
                        data: 'driverConfirmedETA', 
                        name: 'driverConfirmedETA',
                        className: 'text-nowrap text-center',
                        orderable: false,
                        title: 'Driver Confirmed ETA',
                        render: function(data, type, row) {
                            if (data === true || data === 1) {
                                return `<span class="badge bg-success"><i class="fas fa-check"></i> Confirmed</span>`;
                            } else {
                                return `<button type="button" class="btn btn-sm btn-outline-info" 
                                               onclick="confirmAction('Driver ETA Confirmation', ${row.id}, 'driver-eta')">
                                            <i class="fas fa-truck"></i> Confirm ETA
                                        </button>`;
                            }
                        }
                    },
                    { 
                        data: 'midpointCheckComplete', 
                        name: 'midpointCheckComplete',
                        className: 'text-nowrap text-center',
                        orderable: false,
                        title: 'Mid-Point Check Complete',
                        render: function(data, type, row) {
                            if (data === true || data === 1) {
                                return `<span class="badge bg-success"><i class="fas fa-check-circle"></i> Complete</span>`;
                            } else {
                                return `<button type="button" class="btn btn-sm btn-outline-warning" 
                                               onclick="confirmAction('Mid-Point Check', ${row.id}, 'midpoint-check')">
                                            <i class="fas fa-map-marker-alt"></i> Mark Complete
                                        </button>`;
                            }
                        }
                    }
                ],
                initComplete: function() {
                    $('.dataTables_wrapper').css({
                        'width': '100%'
                    });
                },
                drawCallback: function(settings) {
                    $('.dataTables_wrapper').css({
                        'width': '100%'
                    });
                }
            });

            // DataTable filter functionality
            $('#filterBtn').on('click', function () {
                table.draw();
            });

            $('#resetBtn').on('click', function () {
                $('#fromDate').val('');
                $('#toDate').val('');
                table.draw();
            });

        });
        
        // Page Refresh
        function confirmAction(actionName, orderId, actionType) {
            Swal.fire({
                title: 'Confirm Action',
                text: `Are you sure you want to mark "${actionName}" as complete for Order #${orderId}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, mark complete!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    
                    // Show loading
                    Swal.fire({
                        title: 'Processing...',
                        text: 'Updating job status',
                        icon: 'info',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Make AJAX call
                    $.ajax({
                        url: '{{ route("admin.schedular.current-jobs.update-status") }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            orderId: orderId,
                            actionType: actionType
                        },
                        success: function(response) {
                            if (response.success) {
                                
                                // Show success message
                                let message = response.message;
                                if (response.completed) {
                                    message += ' - Job will be removed from the list!';
                                }
                                
                                Swal.fire({
                                    title: response.completed ? 'Job Completed!' : 'Success!',
                                    text: message,
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                
                                // GUARANTEED FIX - Reload entire page
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2200);
                                
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.message,
                                    icon: 'error'
                                });
                            }
                        },
                        error: function(xhr) {
                            console.error('AJAX Error:', xhr.responseText);
                            Swal.fire({
                                title: 'Error!',
                                text: 'Error occurred while updating status. Please try again.',
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        }

        function showActualModal(orderNo, customerNo, comments, carrierNo) {
            // Show modal immediately with available data
            $('#modalOrderNumber').text(orderNo || '-');
            $('#modalCompanyName').text('Loading...');
            $('#modalCarrierName').text('Loading...');
            $('#modalNewExist').text('Loading...');
            
            $('#modalCommentsContent').text(comments || 'No comments available');
            $('#commentsModal').modal('show');
                    
            // API call for company name
            if (customerNo) {
                $.ajax({
                    url: '{{ route("admin.schedular.getCustomer") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        customerNo: customerNo,
                        carrierNo: carrierNo
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#modalCompanyName').text(response.companyName);
                            $('#modalCarrierName').text(response.carrierName);
                            $('#modalNewExist').text(response.newExist);

                            // Make company name clickable
                            $('#modalCompanyName').off('click').on('click', function() {
                                // Redirect to customer details page
                                window.location.href = `/admin/customers/${customerNo}`;
                            });

                            $('#modalCompanyName').css({
                                'cursor': 'pointer',
                            });

                        } else {
                            $('#modalCompanyName').text('Customer #' + customerNo);
                        }
                    },
                    error: function() {
                        $('#modalCompanyName').text('Customer #' + customerNo);
                    }
                });
            } else {
                $('#modalCompanyName').text('-');
            }
        }

        // var table; // Global variable
        // $(function () {
        //     // Initialize DataTable and assign to global variable
        //     if ($.fn.DataTable.isDataTable('#datatable')) {
        //         $('#datatable').DataTable().destroy();
        //     }
            
        //     table = $('#datatable').DataTable({
        //         processing: true,
        //         serverSide: true,
        //         scrollX: true,
        //         scrollCollapse: false,
        //         autoWidth: true,
        //         responsive: false,
        //         ordering: true,
        //         order: [[0, 'desc']],
        //         ajax: {
        //             url: "{{ route('admin.schedular.current-jobs.index') }}",
        //             data: function (d) {
        //                 d.fromDate = $('#fromDate').val();
        //                 d.toDate = $('#toDate').val();
        //                 d._t = new Date().getTime(); // Cache buster
        //             }
        //         },
        //         pageLength: 25,
        //         lengthMenu: [[25, 50, 100], [25, 50, 100]],
        //         columns: [
        //             // ... your complete columns config (copy from your existing)
        //             { 
        //                 data: 'updatedAt', 
        //                 name: 'updatedAt',
        //                 className: 'text-nowrap',
        //                 orderable: true,
        //                 title: 'Date'
        //             },
        //             { 
        //                 data: 'orderNo', 
        //                 name: 'orderNo',
        //                 className: 'text-nowrap',
        //                 orderable: true,
        //                 title: 'Order Number',
        //                 render: function(data, type, row) {
        //                     return data ? `<a href="/admin/orders/${row.id}">${data}</a>` : '-'; 
        //                 }
        //             },
        //             { 
        //                 data: 'customerUserId', 
        //                 name: 'customerUserId',
        //                 className: 'text-nowrap',
        //                 orderable: true,
        //                 title: 'User',
        //                 render: function(data, type, row) {
        //                     return data ? data : '-';
        //                 }
        //             },
        //             { 
        //                 data: 'collectionDate', 
        //                 name: 'collectionDate',
        //                 className: 'text-nowrap',
        //                 orderable: true,
        //                 title: 'Collection Date',
        //                 render: function(data, type, row) {
        //                     return data ? data : '-';
        //                 }
        //             },
        //             { 
        //                 data: 'collectionTime', 
        //                 name: 'collectionTime',
        //                 className: 'text-nowrap',
        //                 orderable: true,
        //                 title: 'Collection Time',
        //                 render: function(data, type, row) {
        //                     return data ? data : '-';
        //                 }
        //             },
        //             { 
        //                 data: 'departureTime', 
        //                 name: 'departureTime',
        //                 className: 'text-nowrap',
        //                 orderable: true,
        //                 title: 'Driver Loaded (Time)',
        //                 render: function(data, type, row) {
        //                     return data ? data : '<span class="text-muted">Pending</span>';
        //                 }
        //             },
        //             { 
        //                 data: 'orderPrice', 
        //                 name: 'orderPrice',
        //                 className: 'text-nowrap text-end',
        //                 orderable: true,
        //                 title: 'Sale Price',
        //                 render: function(data, type, row) {
        //                     return data ? '£' + parseFloat(data).toFixed(2) : '-';
        //                 }
        //             },
        //             { 
        //                 data: 'deliveryTime', 
        //                 name: 'deliveryTime',
        //                 className: 'text-nowrap',
        //                 orderable: true,
        //                 title: 'ETA Delivery',
        //                 render: function(data, type, row) {
        //                     return data ? data : '-';
        //                 }
        //             },
        //             { 
        //                 data: 'midpointCheck', 
        //                 name: 'midpointCheck',
        //                 className: 'text-nowrap',
        //                 orderable: true,
        //                 title: 'Mid-Point Check',
        //                 render: function(data, type, row) {
        //                     return data ? data : '-';
        //                 }
        //             },
        //             { 
        //                 data: 'internalNotes', 
        //                 name: 'internalNotes',
        //                 className: 'text-wrap',
        //                 orderable: true,
        //                 title: 'Notes',
        //                 render: function(data, type, row) {
        //                     return `<button type="button" class="btn btn-sm btn-outline-primary btn-view-comments" 
        //                                 onclick="showActualModal('${row.orderNo || ''}', '${row.customerNo || ''}', \`${data.replace(/`/g, '\\`').replace(/\\/g, '\\\\')}\`, '${row.carrierNo || ''}')">
        //                                 <i class="fas fa-eye me-1"></i>View
        //                             </button>`;
        //                 }
        //             },
        //             { 
        //                 data: 'collectionCheckIn', 
        //                 name: 'collectionCheckIn',
        //                 className: 'text-nowrap text-center',
        //                 orderable: false,
        //                 title: 'Collection Check-In',
        //                 render: function(data, type, row) {
        //                     if (data === true || data === 1) {
        //                         return `<span class="badge bg-success"><i class="fas fa-check"></i> Completed</span>`;
        //                     } else {
        //                         return `<button type="button" class="btn btn-sm btn-outline-primary" 
        //                                     onclick="confirmAction('Collection Check-In', ${row.id}, 'collection-checkin')">
        //                                     <i class="fas fa-clipboard-check"></i> Check-In
        //                                 </button>`;
        //                     }
        //                 }
        //             },
        //             { 
        //                 data: 'driverConfirmedETA', 
        //                 name: 'driverConfirmedETA',
        //                 className: 'text-nowrap text-center',
        //                 orderable: false,
        //                 title: 'Driver Confirmed ETA',
        //                 render: function(data, type, row) {
        //                     if (data === true || data === 1) {
        //                         return `<span class="badge bg-success"><i class="fas fa-check"></i> Confirmed</span>`;
        //                     } else {
        //                         return `<button type="button" class="btn btn-sm btn-outline-info" 
        //                                     onclick="confirmAction('Driver ETA Confirmation', ${row.id}, 'driver-eta')">
        //                                     <i class="fas fa-truck"></i> Confirm ETA
        //                                 </button>`;
        //                     }
        //                 }
        //             },
        //             { 
        //                 data: 'midpointCheckComplete', 
        //                 name: 'midpointCheckComplete',
        //                 className: 'text-nowrap text-center',
        //                 orderable: false,
        //                 title: 'Mid-Point Check Complete',
        //                 render: function(data, type, row) {
        //                     if (data === true || data === 1) {
        //                         return `<span class="badge bg-success"><i class="fas fa-check-circle"></i> Complete</span>`;
        //                     } else {
        //                         return `<button type="button" class="btn btn-sm btn-outline-warning" 
        //                                     onclick="confirmAction('Mid-Point Check', ${row.id}, 'midpoint-check')">
        //                                     <i class="fas fa-map-marker-alt"></i> Mark Complete
        //                                 </button>`;
        //                     }
        //                 }
        //             }
        //         ],
        //         initComplete: function() {
        //             $('.dataTables_wrapper').css({
        //                 'width': '100%'
        //             });
        //         },
        //         drawCallback: function(settings) {
        //             $('.dataTables_wrapper').css({
        //                 'width': '100%'
        //             });
        //         }
        //     });

        //     // DataTable filter functionality
        //     $('#filterBtn').on('click', function () {
        //         table.draw();
        //     });

        //     $('#resetBtn').on('click', function () {
        //         $('#fromDate').val('');
        //         $('#toDate').val('');
        //         table.draw();
        //     });
        // });

        // // FIXED refreshDataTable function
        // function refreshDataTable() {
        //     console.log('🔄 Refreshing DataTable...');
            
        //     // Destroy existing DataTable if it exists
        //     if ($.fn.DataTable.isDataTable('#datatable')) {
        //         $('#datatable').DataTable().destroy();
        //         console.log('🗑️ Old DataTable destroyed');
        //     }
            
        //     // Recreate DataTable
        //     table = $('#datatable').DataTable({
        //         processing: true,
        //         serverSide: true,
        //         scrollX: true,
        //         scrollCollapse: false,
        //         autoWidth: true,
        //         responsive: false,
        //         ordering: true,
        //         order: [[0, 'desc']],
        //         ajax: {
        //             url: "{{ route('admin.schedular.current-jobs.index') }}",
        //             data: function (d) {
        //                 d.fromDate = $('#fromDate').val();
        //                 d.toDate = $('#toDate').val();
        //                 d._t = new Date().getTime(); // Cache buster
        //             }
        //         },
        //         pageLength: 25,
        //         lengthMenu: [[25, 50, 100], [25, 50, 100]],
        //         columns: [
        //             // Copy the same columns config from above
        //             { 
        //                 data: 'updatedAt', 
        //                 name: 'updatedAt',
        //                 className: 'text-nowrap',
        //                 orderable: true,
        //                 title: 'Date'
        //             },
        //             { 
        //                 data: 'orderNo', 
        //                 name: 'orderNo',
        //                 className: 'text-nowrap',
        //                 orderable: true,
        //                 title: 'Order Number',
        //                 render: function(data, type, row) {
        //                     return data ? `<a href="/admin/orders/${row.id}">${data}</a>` : '-'; 
        //                 }
        //             },
        //             { 
        //                 data: 'customerUserId', 
        //                 name: 'customerUserId',
        //                 className: 'text-nowrap',
        //                 orderable: true,
        //                 title: 'User',
        //                 render: function(data, type, row) {
        //                     return data ? data : '-';
        //                 }
        //             },
        //             { 
        //                 data: 'collectionDate', 
        //                 name: 'collectionDate',
        //                 className: 'text-nowrap',
        //                 orderable: true,
        //                 title: 'Collection Date',
        //                 render: function(data, type, row) {
        //                     return data ? data : '-';
        //                 }
        //             },
        //             { 
        //                 data: 'collectionTime', 
        //                 name: 'collectionTime',
        //                 className: 'text-nowrap',
        //                 orderable: true,
        //                 title: 'Collection Time',
        //                 render: function(data, type, row) {
        //                     return data ? data : '-';
        //                 }
        //             },
        //             { 
        //                 data: 'departureTime', 
        //                 name: 'departureTime',
        //                 className: 'text-nowrap',
        //                 orderable: true,
        //                 title: 'Driver Loaded (Time)',
        //                 render: function(data, type, row) {
        //                     return data ? data : '<span class="text-muted">Pending</span>';
        //                 }
        //             },
        //             { 
        //                 data: 'orderPrice', 
        //                 name: 'orderPrice',
        //                 className: 'text-nowrap text-end',
        //                 orderable: true,
        //                 title: 'Sale Price',
        //                 render: function(data, type, row) {
        //                     return data ? '£' + parseFloat(data).toFixed(2) : '-';
        //                 }
        //             },
        //             { 
        //                 data: 'deliveryTime', 
        //                 name: 'deliveryTime',
        //                 className: 'text-nowrap',
        //                 orderable: true,
        //                 title: 'ETA Delivery',
        //                 render: function(data, type, row) {
        //                     return data ? data : '-';
        //                 }
        //             },
        //             { 
        //                 data: 'midpointCheck', 
        //                 name: 'midpointCheck',
        //                 className: 'text-nowrap',
        //                 orderable: true,
        //                 title: 'Mid-Point Check',
        //                 render: function(data, type, row) {
        //                     return data ? data : '-';
        //                 }
        //             },
        //             { 
        //                 data: 'internalNotes', 
        //                 name: 'internalNotes',
        //                 className: 'text-wrap',
        //                 orderable: true,
        //                 title: 'Notes',
        //                 render: function(data, type, row) {
        //                     return `<button type="button" class="btn btn-sm btn-outline-primary btn-view-comments" 
        //                                 onclick="showActualModal('${row.orderNo || ''}', '${row.customerNo || ''}', \`${data.replace(/`/g, '\\`').replace(/\\/g, '\\\\')}\`, '${row.carrierNo || ''}')">
        //                                 <i class="fas fa-eye me-1"></i>View
        //                             </button>`;
        //                 }
        //             },
        //             { 
        //                 data: 'collectionCheckIn', 
        //                 name: 'collectionCheckIn',
        //                 className: 'text-nowrap text-center',
        //                 orderable: false,
        //                 title: 'Collection Check-In',
        //                 render: function(data, type, row) {
        //                     if (data === true || data === 1) {
        //                         return `<span class="badge bg-success"><i class="fas fa-check"></i> Completed</span>`;
        //                     } else {
        //                         return `<button type="button" class="btn btn-sm btn-outline-primary" 
        //                                     onclick="confirmAction('Collection Check-In', ${row.id}, 'collection-checkin')">
        //                                     <i class="fas fa-clipboard-check"></i> Check-In
        //                                 </button>`;
        //                     }
        //                 }
        //             },
        //             { 
        //                 data: 'driverConfirmedETA', 
        //                 name: 'driverConfirmedETA',
        //                 className: 'text-nowrap text-center',
        //                 orderable: false,
        //                 title: 'Driver Confirmed ETA',
        //                 render: function(data, type, row) {
        //                     if (data === true || data === 1) {
        //                         return `<span class="badge bg-success"><i class="fas fa-check"></i> Confirmed</span>`;
        //                     } else {
        //                         return `<button type="button" class="btn btn-sm btn-outline-info" 
        //                                     onclick="confirmAction('Driver ETA Confirmation', ${row.id}, 'driver-eta')">
        //                                     <i class="fas fa-truck"></i> Confirm ETA
        //                                 </button>`;
        //                     }
        //                 }
        //             },
        //             { 
        //                 data: 'midpointCheckComplete', 
        //                 name: 'midpointCheckComplete',
        //                 className: 'text-nowrap text-center',
        //                 orderable: false,
        //                 title: 'Mid-Point Check Complete',
        //                 render: function(data, type, row) {
        //                     if (data === true || data === 1) {
        //                         return `<span class="badge bg-success"><i class="fas fa-check-circle"></i> Complete</span>`;
        //                     } else {
        //                         return `<button type="button" class="btn btn-sm btn-outline-warning" 
        //                                     onclick="confirmAction('Mid-Point Check', ${row.id}, 'midpoint-check')">
        //                                     <i class="fas fa-map-marker-alt"></i> Mark Complete
        //                                 </button>`;
        //                     }
        //                 }
        //             }
        //         ],
        //         initComplete: function() {
        //             $('.dataTables_wrapper').css({
        //                 'width': '100%'
        //             });
        //             console.log('✅ Fresh DataTable created successfully!');
        //         },
        //         drawCallback: function(settings) {
        //             $('.dataTables_wrapper').css({
        //                 'width': '100%'
        //             });
        //         }
        //     });
        // }

        // // FIXED confirmAction function
        // function confirmAction(actionName, orderId, actionType) {
        //     Swal.fire({
        //         title: 'Confirm Action',
        //         text: `Are you sure you want to mark "${actionName}" as complete for Order #${orderId}?`,
        //         icon: 'question',
        //         showCancelButton: true,
        //         confirmButtonColor: '#3085d6',
        //         cancelButtonColor: '#d33',
        //         confirmButtonText: 'Yes, mark complete!',
        //         cancelButtonText: 'Cancel'
        //     }).then((result) => {
        //         if (result.isConfirmed) {
                    
        //             // Show loading
        //             Swal.fire({
        //                 title: 'Processing...',
        //                 text: 'Updating job status',
        //                 icon: 'info',
        //                 allowOutsideClick: false,
        //                 showConfirmButton: false,
        //                 willOpen: () => {
        //                     Swal.showLoading();
        //                 }
        //             });

        //             $.ajax({
        //                 url: '{{ route("admin.schedular.current-jobs.update-status") }}',
        //                 method: 'POST',
        //                 data: {
        //                     _token: '{{ csrf_token() }}',
        //                     orderId: orderId,
        //                     actionType: actionType
        //                 },
        //                 success: function(response) {
        //                     if (response.success) {
                                
        //                         Swal.fire({
        //                             title: response.completed ? 'Job Completed!' : 'Success!',
        //                             text: response.message,
        //                             icon: 'success',
        //                             timer: 1500,
        //                             showConfirmButton: false
        //                         });
                                
        //                         // NUCLEAR REFRESH - Now it will work!
        //                         setTimeout(() => {
        //                             refreshDataTable();
        //                         }, 800);
                                
        //                     } else {
        //                         Swal.fire({
        //                             title: 'Error!',
        //                             text: response.message,
        //                             icon: 'error'
        //                         });
        //                     }
        //                 },
        //                 error: function(xhr) {
        //                     console.error('AJAX Error:', xhr.responseText);
        //                     Swal.fire({
        //                         title: 'Error!',
        //                         text: 'Error occurred while updating status. Please try again.',
        //                         icon: 'error'
        //                     });
        //                 }
        //             });
        //         }
        //     });
        // }

        
    </script>
@endpush