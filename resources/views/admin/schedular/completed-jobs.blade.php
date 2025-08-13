@extends('admin.layouts.app')

@section('title', 'Completed Jobs | CSD Assistant')

@push('links')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/select.dataTables.min.css') }}">

    <style>
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

        /* Page header styling */
        .page-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            border-radius: 10px;
        }

        .page-header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .page-header p {
            margin: 0;
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Date filter styling */
        .date-filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        span#modalOrderNumber {
            font-size: 15px;
            text-decoration: none !important;
        }
    </style>
@endpush

@section('content')
<div class="main-content introduction-farm">
    <div class="content-wraper-area">
        <div class="data-table-area">
            <div class="container-fluid">
                
                <!-- Page Header -->
                <!-- <div class="page-header text-center">
                    <h1><i class="fas fa-check-circle me-3"></i>Completed Jobs</h1>
                    <p>View all successfully completed jobs and their details</p>
                </div> -->

                <div class="row g-4">
                    <div class="col-12">
                        <div class="card date-filter-section">
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
                                    <div class="col-md-3 d-flex align-items-end date-btn-filter">
                                        <button id="filterBtn" class="btn btn-success">
                                            <i class="fas fa-filter me-1"></i>Filter
                                        </button>
                                        <button id="resetBtn" class="btn btn-secondary ms-2">
                                            <i class="fas fa-refresh me-1"></i>Reset
                                        </button>
                                    </div>
                                    <!-- <div class="col-md-3 d-flex align-items-end">
                                        <div class="ms-auto">
                                            <span class="badge bg-success fs-6 px-3 py-2">
                                                <i class="fas fa-check-circle me-1"></i>All Completed
                                            </span>
                                        </div>
                                    </div> -->
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
                                            <th>Completed Date</th>
                                            <th>Order Number</th>
                                            <th>User</th>
                                            <th>Collection Date</th>
                                            <th>Collection Time</th>
                                            <th>Driver Loaded (Time)</th>
                                            <th>Sale Price</th>
                                            <th>ETA Delivery</th>
                                            <th>Mid-Point Check</th>
                                            <th>Notes</th>
                                            <th>Collection Status</th>
                                            <th>Driver ETA Status</th>
                                            <th>Mid-Point Status</th>
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
<!-- <div class="modal fade" id="commentsModal" tabindex="-1" aria-labelledby="commentsModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg">
       <div class="modal-content border-0 shadow-lg">
           <div class="modal-header bg-success text-white border-0">
               <h5 class="modal-title d-flex align-items-center" id="commentsModalLabel">
                   <i class="fas fa-check-circle me-2"></i>Completed Job Details
               </h5>
               <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
           </div>
           <div class="modal-body p-4">
               <div class="row mb-3">
                   <div class="col-sm-4">
                       <div class="d-flex align-items-center">
                           <i class="fas fa-hashtag text-secondary me-2"></i>
                           <strong>Order Number:</strong>
                       </div>
                   </div>
                   <div class="col-sm-8">
                        <span id="modalOrderNumber" class="badge bg-success text-white">-</span>
                   </div>
               </div>
               <div class="row mb-3">
                   <div class="col-sm-4">
                       <div class="d-flex align-items-center">
                           <i class="fas fa-building text-primary me-2"></i>
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
                           <i class="fas fa-truck text-primary me-2"></i>
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
                           <i class="fas fa-user-tag text-primary me-2"></i>
                           <strong>New/Existing:</strong>
                       </div>
                   </div>
                   <div class="col-sm-8">
                       <span id="modalNewExist" class="text-muted">-</span>
                   </div>
               </div>
               <div class="mb-3">
                   <label class="form-label d-flex align-items-center mb-2">
                       <i class="fas fa-comment-dots text-primary me-2"></i>
                       <strong>Comments:</strong>
                   </label>
                   <div id="modalCommentsContent" class="comments-content bg-light p-3 rounded border">
                       No comments available
                   </div>
               </div>
           </div>
           <div class="modal-footer border-0 bg-light">
               <button type="button" class="btn btn-success" data-bs-dismiss="modal">
                   <i class="fas fa-check me-1"></i>Close
               </button>
           </div>
       </div>
   </div>
</div> -->

<div class="modal fade" id="commentsModal" tabindex="-1" aria-labelledby="commentsModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg">
       <div class="modal-content border-0 shadow-lg">
           <div class="modal-header bg-success text-white border-0">
               <h5 class="modal-title d-flex align-items-center" id="commentsModalLabel">
                   <i class="bx bx-info-circle me-2"></i>Completed Jobs
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
                        <span id="modalOrderNumber" class="badge bg-success text-white" >-</span>
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
               <button type="button" class="btn btn-success" data-bs-dismiss="modal">
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
                order: [[0, 'desc']], // Default sort by completion date descending
                ajax: {
                    url: "{{ route('admin.schedular.completed-jobs.index') }}",
                    data: function (d) {
                        d.fromDate = $('#fromDate').val();
                        d.toDate = $('#toDate').val();
                        d._t = new Date().getTime(); // Cache buster
                    }
                },
                pageLength: 25,
                lengthMenu: [[25, 50, 100], [25, 50, 100]],
                columns: [
                    { 
                        data: 'updatedAt', 
                        name: 'updated_at',
                        className: 'text-nowrap',
                        orderable: true,
                        title: 'Completed Date'
                    },
                    { 
                        data: 'orderNo', 
                        name: 'orderNo',
                        className: 'text-nowrap',
                        orderable: true,
                        title: 'Order Number',
                        render: function(data, type, row) {
                            return data ? `<span class="badge bg-success">${data}</span>` : '-'; 
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
                            return data ? data : '<span class="text-muted">-</span>';
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
                                return `<span class="badge bg-success"><i class="fas fa-check"></i> Completed</span>`;
                        }
                    },
                    { 
                        data: 'driverConfirmedETA', 
                        name: 'driverConfirmedETA',
                        className: 'text-nowrap text-center',
                        orderable: false,
                        title: 'Driver Confirmed ETA',
                        render: function(data, type, row) {
                                return `<span class="badge bg-success"><i class="fas fa-check"></i> Confirmed</span>`;
                        }
                    },
                    { 
                        data: 'midpointCheckComplete', 
                        name: 'midpointCheckComplete',
                        className: 'text-nowrap text-center',
                        orderable: false,
                        title: 'Mid-Point Check Complete',
                        render: function(data, type, row) {
                                return `<span class="badge bg-success"><i class="fas fa-check-circle"></i> Completed</span>`;
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
                    url: '{{ route("admin.schedular.completed.getCustomer") }}',
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
    </script>
@endpush