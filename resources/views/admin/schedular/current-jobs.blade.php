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
            cursor: pointer; /* Add cursor pointer for clickable cards */
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
        
        /* Active card styling */
        .stats-card.active {
            transform: scale(1.05) translateY(-5px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.8);
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

        .stats-card.midpoint-check {
            background: linear-gradient(135deg, #c20068 0%, #f9e738 100%);
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
            min-width: 120px; 
            width: 10%; 
        } /* Order Number */
        
        #datatable th:nth-child(2), #datatable td:nth-child(2) { 
            min-width: 120px; 
            width: 8%; 
        } /* Collection Time */
        
        #datatable th:nth-child(3), #datatable td:nth-child(3) { 
            min-width: 140px; 
            width: 10%; 
        } /* Driver Loaded Time */
        
        #datatable th:nth-child(4), #datatable td:nth-child(4) { 
            min-width: 120px; 
            width: 8%; 
        } /* ETA Delivery */
        
        #datatable th:nth-child(5), #datatable td:nth-child(5) { 
            min-width: 120px; 
            width: 8%; 
        } /* Mid-Point Check */
        
        #datatable th:nth-child(6), #datatable td:nth-child(6) { 
            min-width: 100px; 
            width: 6%; 
        } /* Notes */
        
        #datatable th:nth-child(7), #datatable td:nth-child(7) { 
            min-width: 140px; 
            width: 12%; 
        } /* Collection Check-In */
        
        #datatable th:nth-child(8), #datatable td:nth-child(8) { 
            min-width: 140px; 
            width: 12%; 
        } /* Driver Confirmed ETA */
        
        #datatable th:nth-child(9), #datatable td:nth-child(9) { 
            min-width: 160px; 
            width: 12%; 
        } /* Mid-Point Check Complete */
        
        #datatable th:nth-child(10), #datatable td:nth-child(10) { 
            min-width: 120px; 
            width: 12%; 
        } /* Delivered */
        
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
            padding: 1px 3px;
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

        /* Button disabled styling */
        .btn:disabled {
            opacity: 0.4 !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
        }

    @media (min-width: 1400px) {
        .col-xxl {
            flex: 0 0 auto;
            width: 20%; /* 5 cards = 20% each */
        }
    }

    @media (min-width: 1200px) and (max-width: 1399.98px) {
        .col-xl {
            flex: 0 0 auto;
            width: 20%; /* 5 cards = 20% each */
        }
    }

    /* For tablets and smaller, keep your existing responsive behavior */
    @media (max-width: 1199.98px) {
        .dashboard-cards .col-lg-4 {
            width: 33.333333%; /* 3 cards per row */
        }
    }

    @media (max-width: 991.98px) {
        .dashboard-cards .col-md-6 {
            width: 50%; /* 2 cards per row */
        }
    }

    @media (max-width: 575.98px) {
        .dashboard-cards .col-sm-6 {
            width: 100%; /* 1 card per row */
        }
    }

    /* #datatable thead th:first-child,
    #datatable tbody td:first-child {
        position: sticky !important;
        left: 0 !important;
        z-index: 10 !important;
        background-color: #fff !important;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1) !important;
        border-right: 2px solid #dee2e6 !important;
    }
    #datatable tbody tr:nth-child(even) td:first-child {
        background-color: #f8f9fa !important; 
    }
    #datatable tbody tr:nth-child(odd) td:first-child {
        background-color: #ffffff !important; 
    }      */
    #datatable tbody tr:nth-child(even) {
        background-color: #f8f9fa !important; /* Light gray */
    }

    #datatable tbody tr:nth-child(odd) {
        background-color: #ffffff !important; /* White */
    }

    #datatable tbody tr:hover {
        background-color: #e3f2fd !important; /* Light blue */
        cursor: pointer;
        transform: translateY(-1px); /* Slight lift */
        box-shadow: 0 2px 8px rgba(0,0,0,0.1); /* Shadow */
        transition: all 0.2s ease; /* Smooth animation */
    }

    #datatable tbody tr:hover td:first-child {
        background-color: #e3f2fd !important; /* Same color as row */
    }

    /* Filter status indicator */
    .filter-status {
        margin-bottom: 15px;
        padding: 10px 15px;
        background-color: #e3f2fd;
        border-left: 4px solid #2196f3;
        border-radius: 4px;
        display: none;
    }

    .filter-status.active {
        display: block;
    }

    .clear-filter-btn {
        margin-left: 10px;
        padding: 2px 8px;
        font-size: 12px;
    }
    .row-icons {
        width: 30px; 
        height: 30px; 
        margin: 0 12px; /* Small margin between icons */
        display: inline-block;
    }

    /* Actions column specific styling */
    td:last-child {
        white-space: nowrap !important;
        text-align: center !important;
    }

    /* Reduce cell padding */
    .dataTables_wrapper table.dataTable td,
    .dataTables_wrapper table.dataTable th {
        padding: 4px 8px !important; /* Default is usually 8px 10px */
    }

    /* Or target specific table */
    #datatable td,
    #datatable th {
        padding: 3px 4px !important;
    }

    /* Make table more compact overall */
    .dataTables_wrapper table.dataTable {
        margin: 0 !important;
    }
    #datatable th,
    #datatable td {
        text-align: center !important;
        vertical-align: middle !important;
    }

    /* If you want to center all DataTables on the page */
    .dataTables_wrapper table.dataTable th,
    .dataTables_wrapper table.dataTable td {
        text-align: center !important;
        vertical-align: middle !important;
    }

    table.dataTable th, 
    table.dataTable td {
        white-space: nowrap;   /* stop text wrapping */
        padding: 4px 8px !important; /* reduce space */
    }

    .dataTables_wrapper .dataTables_scrollHeadInner,
    table.dataTable {
        width: 100% !important; /* always fit container */
    }

    /* Fix action column overflow and width */
    #datatable th:last-child,
    #datatable td:last-child {
        min-width: 180px !important;
        max-width: 180px !important;
        width: 180px !important;
        white-space: nowrap !important;
        overflow: visible !important;
        text-align: center !important;
        padding: 3px 2px !important;
    }

    /* Make action icons smaller and better spaced */
    .row-icons {
        width: 24px !important; 
        height: 24px !important; 
        margin: 0 1px !important;
        display: inline-block;
        vertical-align: middle;
    }

    /* Reduce column widths more aggressively */
    #datatable th:nth-child(1), #datatable td:nth-child(1) { 
        min-width: 90px !important; 
        max-width: 90px !important;
        width: 90px !important; 
    }

    #datatable th:nth-child(2), #datatable td:nth-child(2) { 
        min-width: 80px !important; 
        max-width: 80px !important;
        width: 80px !important; 
    }

    #datatable th:nth-child(3), #datatable td:nth-child(3) { 
        min-width: 80px !important; 
        max-width: 80px !important;
        width: 80px !important; 
    }

    #datatable th:nth-child(4), #datatable td:nth-child(4) { 
        min-width: 80px !important; 
        max-width: 80px !important;
        width: 80px !important; 
    }

    #datatable th:nth-child(5), #datatable td:nth-child(5) { 
        min-width: 80px !important; 
        max-width: 80px !important;
        width: 80px !important; 
    }

    /* Force table to use exact widths */
    #datatable {
        table-layout: fixed !important;
        width: 100% !important;
    }

    /* Reduce cell padding even more */
    #datatable td,
    #datatable th {
        padding: 2px 4px !important;
        font-size: 12px !important;
        line-height: 1.2 !important;
    }

    /* Make order number badges smaller */
    .order-num {
        font-size: 11px !important;
        padding: 2px 6px !important;
    }

    /* Ensure no text wrapping anywhere */
    #datatable th,
    #datatable td {
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    /* Special handling for action column to prevent overflow */
    #datatable td:last-child {
        overflow: visible !important;
        text-overflow: unset !important;
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
                    <div class="col-xxl col-xl col-lg-4 col-md-6 col-sm-6">
                        <div class="card stats-card total-jobs" data-filter="all">
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

                    <div class="col-xxl col-xl col-lg-4 col-md-6 col-sm-6">
                        <div class="card stats-card collections-overdue" data-filter="collections-overdue">
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

                    <div class="col-xxl col-xl col-lg-4 col-md-6 col-sm-6">
                        <div class="card stats-card deliveries-overdue" data-filter="deliveries-overdue">
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

                    <div class="col-xxl col-xl col-lg-4 col-md-6 col-sm-6">
                        <div class="card stats-card midpoint-check" data-filter="midpoint-overdue">
                            <div class="card-content">
                                <div class="card-title">Mid-Point Overdue</div>
                                <div class="card-value" id="midPointOverdue">
                                    {{ $countData['midPointCheckInOverdue'] }}
                                </div>
                                <div class="card-subtitle">Overdue Check-ins</div>
                            </div>
                            <div class="card-icon">
                                <i class="bx bx-clipboard"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl col-xl col-lg-4 col-md-6 col-sm-6">
                        <div class="card stats-card delivered" data-filter="delivered">
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

                <!-- Filter Status Indicator -->
                <div class="filter-status" id="filterStatus">
                    <i class="bx bx-filter-alt"></i>
                    <span id="filterText">Showing all jobs</span>
                    <button type="button" class="btn btn-sm btn-outline-primary clear-filter-btn" onclick="clearFilter()">
                        <i class="bx bx-x"></i> Clear Filter
                    </button>
                </div>

                <div class="row g-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <table id="datatable" class="table table-bordered dt-responsive nowrap data-table-area">
                                    <thead>
                                        <tr>
                                            <th>Order Number</th>
                                            <th>Collection Time</th>
                                            <th>Driver Loaded</th>
                                            <th>ETA Delivery</th>
                                            <th>Mid-Point Check</th>
                                            <th>Actions</th>
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

        $('#datatable').on('draw.dt', function () {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                new bootstrap.Tooltip(el);
            });
        });

        // Global variables for filtering
        window.currentFilter = 'all';
        let table;

        $(function () {
            // Initialize DataTable
            if ($.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable().destroy();
            }
            
            table = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                scrollCollapse: false,
                autoWidth: false,
                responsive: false,
                ordering: true,
                order: [[0, 'desc']], // Default sort by first column descending
                columnDefs: [
                    { targets: [0], width: '90px' },
                    { targets: [1], width: '80px' },
                    { targets: [2], width: '80px' },
                    { targets: [3], width: '80px' },
                    { targets: [4], width: '80px' },
                    { targets: [5], width: '180px' },
                    { className: "text-center", targets: "_all" }
                ],
                ajax: {
                    url: "{{ route('admin.schedular.current-jobs.index') }}",
                    data: function (d) {
                        d.fromDate = $('#fromDate').val();
                        d.toDate = $('#toDate').val();
                        d.filterType = window.currentFilter; // Add filter parameter
                    }
                },
                pageLength: 10,
                lengthMenu: [[10, 20, 30, 40, 50, 60, 70, 80, 90, 100], [10, 20, 30, 40, 50, 60, 70, 80, 90, 100]],
                columns: [
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
                        title: 'Driver Loaded',
                        render: function(data, type, row) {
                            return data ? data : '<span class="text-muted">Pending</span>';
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
                        data: null, 
                        name: 'actions',
                        className: 'text-center',
                        orderable: false,
                        title: 'Actions',
                        width: '200px', // Adjust width as needed
                        render: function(data, type, row) {
                            let actions = '';

                            // NEW: View Notes button
                            let safeNotes = (row.internalNotes || '').replace(/`/g, '\\`').replace(/\\/g, '\\\\');
                            actions += `<img src="{{ asset('assets/admin/img/icons/view.png') }}" 
                                            alt="View" 
                                            class="row-icons" 
                                            style="cursor: pointer;" 
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="bottom" 
                                            data-bs-title="View More Details"
                                            onclick="showActualModal('${row.orderNo || ''}', '${row.customerNo || ''}', \`${safeNotes}\`, '${row.carrierNo || ''}')">`;
                            
                            // Collection Check-In
                            if (row.collectionCheckIn === true || row.collectionCheckIn === 1) {
                                actions += `<img src="{{ asset('assets/admin/img/icons/complete.png') }}" alt="Collection Completed" class="row-icons" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Collection Check-in Completed">`;
                            } else {
                                actions += `<img onclick="confirmAction('Collection Check-In', ${row.id}, 'collection-checkin')" src="{{ asset('assets/admin/img/icons/check-in.png') }}" alt="Check-In" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Click to perform Collection Check-in" class="row-icons" style="cursor: pointer;">`;
                            }
                            
                            // Driver Confirmed ETA
                            if (row.driverConfirmedETA === true || row.driverConfirmedETA === 1) {
                                actions += `<img src="{{ asset('assets/admin/img/icons/complete.png') }}" alt="Driver Confirmed" class="row-icons" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Driver ETA Confirmed">`;
                            } else {
                                let isDisabled = !(row.collectionCheckIn === true || row.collectionCheckIn === 1);
                                let tooltip = isDisabled ? "Complete Collection Check-in first" : "Click to confirm Driver ETA";
                                let disabledAttr = isDisabled ? 'style="cursor: not-allowed; opacity: 0.5;"' : 'style="cursor: pointer;"';
                                let onclickAttr = isDisabled ? '' : `onclick="confirmAction('Driver ETA Confirmation', ${row.id}, 'driver-eta')"`;
                                
                                actions += `<img src="{{ asset('assets/admin/img/icons/driver-confirmed.png') }}" alt="Confirm ETA" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="${tooltip}" class="row-icons" ${disabledAttr} ${onclickAttr}>`;
                            }
                            
                            // Mid-Point Check
                            if (row.midpointCheckComplete === true || row.midpointCheckComplete === 1) {
                                actions += `<img src="{{ asset('assets/admin/img/icons/complete.png') }}" alt="Mid-Point Complete" class="row-icons" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Mid-Point Check Completed">`;
                            } else {
                                let isDisabled = !(row.driverConfirmedETA === true || row.driverConfirmedETA === 1);
                                let tooltip = isDisabled ? "Complete Driver ETA first" : "Click to mark Mid-Point Check complete";
                                let disabledAttr = isDisabled ? 'style="cursor: not-allowed; opacity: 0.5;"' : 'style="cursor: pointer;"';
                                let onclickAttr = isDisabled ? '' : `onclick="confirmAction('Mid-Point Check', ${row.id}, 'midpoint-check')"`;
                                
                                actions += `<img src="{{ asset('assets/admin/img/icons/mid-point-check.png') }}" alt="Mark Complete" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="${tooltip}" class="row-icons" ${disabledAttr} ${onclickAttr}>`;
                            }
                            
                            // Delivered
                            const deliveredStatus = parseInt(row.delivered);
                            if (deliveredStatus === 1) {
                                actions += `<img src="{{ asset('assets/admin/img/icons/complete.png') }}" alt="Delivered" class="row-icons" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Delivered">`;
                            } else if (row.delivered === null || row.delivered === undefined || row.delivered === '' || isNaN(deliveredStatus)) {
                                let isDisabled = !(row.driverConfirmedETA === true || row.driverConfirmedETA === 1);
                                let tooltip = isDisabled ? "Complete Driver ETA first" : "Click to mark as delivered";
                                let disabledAttr = isDisabled ? 'style="cursor: not-allowed; opacity: 0.5;"' : 'style="cursor: pointer;"';
                                let onclickAttr = isDisabled ? '' : `onclick="confirmAction('Delivery Status', ${row.id}, 'delivered', ${row.midpointCheckComplete ? 'true' : 'false'})"`;
                                
                                actions += `<img src="{{ asset('assets/admin/img/icons/delivered.png') }}" alt="Mark Delivered" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="${tooltip}" class="row-icons" ${disabledAttr} ${onclickAttr}>`;
                            } else {
                                actions += `<span class="badge bg-secondary">Unknown (${row.delivered})</span>`;
                            }
                            
                            return actions;
                        }
                    }
                ],
                // columns: [
                //     { 
                //         data: 'orderNo', 
                //         name: 'orderNo',
                //         className: 'text-center',
                //         orderable: true,
                //         title: 'Order No',
                //         width: '90px',
                //         render: function(data, type, row) {
                //             return data ? `<span class="order-num badge bg-primary"><a href="/admin/orders/${row.id}">${data}</a></span>` : '-'; 
                //         }
                //     },
                //     { 
                //         data: 'collectionTime', 
                //         name: 'collectionTime',
                //         className: 'text-center',
                //         orderable: true,
                //         title: 'Collection',
                //         width: '80px',
                //         render: function(data, type, row) {
                //             return data ? data : '-';
                //         }
                //     },
                //     { 
                //         data: 'departureTime', 
                //         name: 'departureTime',
                //         className: 'text-center',
                //         orderable: true,
                //         title: 'Loaded',
                //         width: '80px',
                //         render: function(data, type, row) {
                //             return data ? data : '<span class="text-muted">Pending</span>';
                //         }
                //     },
                //     { 
                //         data: 'deliveryTime', 
                //         name: 'deliveryTime',
                //         className: 'text-center',
                //         orderable: true,
                //         title: 'ETA',
                //         width: '80px',
                //         render: function(data, type, row) {
                //             return data ? data : '-';
                //         }
                //     },
                //     { 
                //         data: 'midpointCheck', 
                //         name: 'midpointCheck',
                //         className: 'text-center',
                //         orderable: true,
                //         title: 'Mid-Point',
                //         width: '80px',
                //         render: function(data, type, row) {
                //             return data ? data : '-';
                //         }
                //     },
                //     { 
                //         data: null, 
                //         name: 'actions',
                //         className: 'text-center',
                //         orderable: false,
                //         title: 'Actions',
                //         width: '180px',
                //         render: function(data, type, row) {
                //             let actions = '';

                //             // View Notes button
                //             let safeNotes = (row.internalNotes || '').replace(/`/g, '\\`').replace(/\\/g, '\\\\');
                //             actions += `<img src="{{ asset('assets/admin/img/icons/view.png') }}" 
                //                             alt="View" 
                //                             class="row-icons" 
                //                             style="cursor: pointer;" 
                //                             data-bs-toggle="tooltip" 
                //                             data-bs-placement="top" 
                //                             data-bs-title="View Details"
                //                             onclick="showActualModal('${row.orderNo || ''}', '${row.customerNo || ''}', \`${safeNotes}\`, '${row.carrierNo || ''}')">`;
                            
                //             // Collection Check-In
                //             if (row.collectionCheckIn === true || row.collectionCheckIn === 1) {
                //                 actions += `<img src="{{ asset('assets/admin/img/icons/complete.png') }}" alt="Done" class="row-icons" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Collection Done">`;
                //             } else {
                //                 actions += `<img onclick="confirmAction('Collection Check-In', ${row.id}, 'collection-checkin')" src="{{ asset('assets/admin/img/icons/check-in.png') }}" alt="Check-In" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Collection Check-in" class="row-icons" style="cursor: pointer;">`;
                //             }
                            
                //             // Driver Confirmed ETA
                //             if (row.driverConfirmedETA === true || row.driverConfirmedETA === 1) {
                //                 actions += `<img src="{{ asset('assets/admin/img/icons/complete.png') }}" alt="Done" class="row-icons" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Driver Confirmed">`;
                //             } else {
                //                 let isDisabled = !(row.collectionCheckIn === true || row.collectionCheckIn === 1);
                //                 let tooltip = isDisabled ? "Collection first" : "Confirm ETA";
                //                 let disabledAttr = isDisabled ? 'style="cursor: not-allowed; opacity: 0.5;"' : 'style="cursor: pointer;"';
                //                 let onclickAttr = isDisabled ? '' : `onclick="confirmAction('Driver ETA', ${row.id}, 'driver-eta')"`;
                                
                //                 actions += `<img src="{{ asset('assets/admin/img/icons/driver-confirmed.png') }}" alt="ETA" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="${tooltip}" class="row-icons" ${disabledAttr} ${onclickAttr}>`;
                //             }
                            
                //             // Mid-Point Check
                //             if (row.midpointCheckComplete === true || row.midpointCheckComplete === 1) {
                //                 actions += `<img src="{{ asset('assets/admin/img/icons/complete.png') }}" alt="Done" class="row-icons" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Mid-Point Done">`;
                //             } else {
                //                 let isDisabled = !(row.driverConfirmedETA === true || row.driverConfirmedETA === 1);
                //                 let tooltip = isDisabled ? "ETA first" : "Mid-Point";
                //                 let disabledAttr = isDisabled ? 'style="cursor: not-allowed; opacity: 0.5;"' : 'style="cursor: pointer;"';
                //                 let onclickAttr = isDisabled ? '' : `onclick="confirmAction('Mid-Point', ${row.id}, 'midpoint-check')"`;
                                
                //                 actions += `<img src="{{ asset('assets/admin/img/icons/mid-point-check.png') }}" alt="Mid-Point" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="${tooltip}" class="row-icons" ${disabledAttr} ${onclickAttr}>`;
                //             }
                            
                //             // Delivered
                //             const deliveredStatus = parseInt(row.delivered);
                //             if (deliveredStatus === 1) {
                //                 actions += `<img src="{{ asset('assets/admin/img/icons/complete.png') }}" alt="Done" class="row-icons" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Delivered">`;
                //             } else if (row.delivered === null || row.delivered === undefined || row.delivered === '' || isNaN(deliveredStatus)) {
                //                 let isDisabled = !(row.driverConfirmedETA === true || row.driverConfirmedETA === 1);
                //                 let tooltip = isDisabled ? "ETA first" : "Deliver";
                //                 let disabledAttr = isDisabled ? 'style="cursor: not-allowed; opacity: 0.5;"' : 'style="cursor: pointer;"';
                //                 let onclickAttr = isDisabled ? '' : `onclick="confirmAction('Delivery', ${row.id}, 'delivered')"`;
                                
                //                 actions += `<img src="{{ asset('assets/admin/img/icons/delivered.png') }}" alt="Deliver" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="${tooltip}" class="row-icons" ${disabledAttr} ${onclickAttr}>`;
                //             }
                            
                //             return actions;
                //         }
                //     }
                // ],
                initComplete: function() {
                    $('.dataTables_wrapper').css({
                        'width': '100%'
                    });
                    // Force table layout
                    $('#datatable').css('table-layout', 'fixed');
                },
                drawCallback: function(settings) {
                    $('.dataTables_wrapper').css({
                        'width': '100%'
                    });
                    // Re-initialize tooltips
                    $('[data-bs-toggle="tooltip"]').tooltip();
                    // Force table layout
                    $('#datatable').css('table-layout', 'fixed');
                }
            });

            // Card click event handlers
            $('.stats-card').on('click', function() {
                const filterType = $(this).data('filter');
                filterDataTable(filterType);
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

        // Filter function
        function filterDataTable(filterType) {
            // Remove active class from all cards
            $('.stats-card').removeClass('active');
            
            // Add active class to clicked card
            $(`[data-filter="${filterType}"]`).addClass('active');
            
            // Set global filter
            window.currentFilter = filterType;
            
            // Update filter status
            updateFilterStatus(filterType);
            
            // Refresh DataTable
            table.ajax.reload();
        }

        // Clear filter function
        function clearFilter() {
            // Remove active class from all cards
            $('.stats-card').removeClass('active');
            
            // Reset filter
            window.currentFilter = 'all';
            
            // Hide filter status
            $('#filterStatus').removeClass('active');
            
            // Refresh DataTable
            table.ajax.reload();
        }

        // Update filter status indicator
        function updateFilterStatus(filterType) {
            const filterTexts = {
                'all': 'Showing all jobs',
                'collections-overdue': 'Showing only collections overdue',
                'deliveries-overdue': 'Showing only deliveries overdue', 
                'midpoint-overdue': 'Showing only mid-point check overdue',
                'delivered': 'Showing only delivered jobs'
            };
            
            if (filterType !== 'all') {
                $('#filterText').text(filterTexts[filterType] || 'Showing filtered jobs');
                $('#filterStatus').addClass('active');
            } else {
                $('#filterStatus').removeClass('active');
            }
        }

        // Updated confirmAction function with special delivery handling
        function confirmAction(actionName, orderId, actionType, midpointComplete = false) {
            // Special handling for delivery action
            if (actionType === 'delivered') {
                showDeliveryPopup(orderId, midpointComplete);
                return;
            }
            
            // Regular confirmation for other actions
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
                    processAction(orderId, actionType);
                }
            });
        }

        // Special popup for delivery with 3 options
        // function showDeliveryPopup(orderId) {
        //     Swal.fire({
        //         title: 'Delivery Status',
        //         text: 'Are you sure the item has been delivered?',
        //         icon: 'question',
        //         showCancelButton: true,
        //         confirmButtonText: 'Yes',
        //         cancelButtonText: 'No',
        //         confirmButtonColor: '#28a745',
        //         cancelButtonColor: '#dc3545',
        //         reverseButtons: true
        //     }).then((result) => {
        //         if (result.isConfirmed) {
        //             // "Not Required" - Set delivered = 1
        //             processAction(orderId, 'delivered', { deliveredStatus: 1 });
        //         } 
        //     });
        // }

        function showDeliveryPopup(orderId, midpointComplete) {
            let title, text;
            console.log(orderId);
            console.log(midpointComplete);
            
            if (midpointComplete === 'true' || midpointComplete === true) {
                // Mid-point check is TICKED
                title = 'Delivery Status';
                text = 'Are you sure the item has been delivered?';
            } else {
                // Mid-point check is NOT TICKED
                title = 'Continue Without Mid-Point Check?';
                text = 'Do you wish to continue without mid-point check complete?';
            }
            
            Swal.fire({
                title: title,
                text: text,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#dc3545',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    processAction(orderId, 'delivered', { deliveredStatus: 1 });
                } 
            });
        }

        // Process the action with AJAX
        function processAction(orderId, actionType, extraData = {}) {
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

            // Prepare data
            let requestData = {
                _token: '{{ csrf_token() }}',
                orderId: orderId,
                actionType: actionType,
                ...extraData // Merge any extra data (like deliveredStatus)
            };

            // Make AJAX call
            $.ajax({
                url: '{{ route("admin.schedular.current-jobs.update-status") }}',
                method: 'POST',
                data: requestData,
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
                    url: '{{ route("admin.schedular.current.getCustomer") }}',
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