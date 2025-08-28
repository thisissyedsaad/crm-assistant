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
    overflow-x: hidden !important;
}

.dataTables_scrollBody {
    overflow-x: hidden !important;
    overflow-y: visible !important;
}

/* Table should use full width and fit container */
#datatable {
    width: 100% !important;
    table-layout: fixed !important;
}
        
/* Optimized column widths for full width without scroll */
#datatable th:nth-child(1), #datatable td:nth-child(1) { 
    width: 50px !important; 
    max-width: 50px !important;
} /* Checkbox column */

#datatable th:nth-child(2), #datatable td:nth-child(2) { 
    width: 120px !important; 
    max-width: 120px !important;
} /* Order Number */

#datatable th:nth-child(3), #datatable td:nth-child(3) { 
    width: 110px !important; 
    max-width: 110px !important;
} /* Collection Time */

#datatable th:nth-child(4), #datatable td:nth-child(4) { 
    width: 110px !important; 
    max-width: 110px !important;
} /* Driver Loaded */

#datatable th:nth-child(5), #datatable td:nth-child(5) { 
    width: 110px !important; 
    max-width: 110px !important;
} /* ETA Delivery */

#datatable th:nth-child(6), #datatable td:nth-child(6) { 
    width: 120px !important; 
    max-width: 120px !important;
} /* Mid-Point Check */

#datatable th:nth-child(7), #datatable td:nth-child(7) { 
    width: auto !important; 
    min-width: 200px !important;
} /* Actions - takes remaining space */
        
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
        margin: 0 12px !important;
        display: inline-block;
        vertical-align: middle;
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

    /* NEW: Selection and bulk actions styling */
    .bulk-actions-bar {
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #fff;
        border: 2px solid #007bff;
        border-radius: 8px;
        padding: 12px 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1000;
        display: none;
        align-items: center;
        gap: 15px;
        animation: slideInRight 0.3s ease;
    }

    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    .bulk-actions-bar.active {
        display: flex;
    }

    .selected-count {
        color: #495057;
        font-weight: 500;
    }

    .btn-remove-selected {
        background-color: #dc3545;
        border-color: #dc3545;
        color: white;
        font-size: 14px;
        padding: 6px 16px;
    }

    .btn-remove-selected:hover {
        background-color: #c82333;
        border-color: #bd2130;
        color: white;
    }

    /* Checkbox styling */
    .order-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    #selectAllCheckbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    /* Make sure checkbox column is properly sized */
    #datatable th:first-child,
    #datatable td:first-child {
        min-width: 60px !important;
        max-width: 60px !important;
        width: 60px !important;
        text-align: center !important;
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

                <!-- NEW: Bulk Actions Bar -->
                <div class="bulk-actions-bar" id="bulkActionsBar">
                    <div class="selected-count">
                        <span id="selectedCount">0</span> orders selected
                    </div>
                    <button type="button" class="btn btn-remove-selected" onclick="removeSelectedOrders()">
                        <i class="bx bx-trash"></i> Remove Selected Orders
                    </button>
                </div>

                <div class="row g-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <table id="datatable" class="table table-bordered dt-responsive nowrap data-table-area">
                                    <thead>
                                        <tr>
                                            <th>
                                                <input type="checkbox" id="selectAllCheckbox" title="Select/Deselect All">
                                            </th>
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

        // Global variables for filtering - get from server-side stored value
        window.currentFilter = '{{ $storedFilter ?? "all" }}';
        let table;
        let selectedOrders = []; // Track selected order IDs

        $(function () {
            // Initialize DataTable
            if ($.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable().destroy();
            }
            
            table = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
scrollX: false, // Disable horizontal scrolling
                scrollCollapse: false,
                autoWidth: false,
                responsive: false,
                ordering: true,
                order: [[1, 'desc']], // Sort by Order Number column (second column now)
columnDefs: [
    { targets: [0], width: '50px', orderable: false }, // Checkbox column
    { targets: [1], width: '120px' }, // Order Number
    { targets: [2], width: '110px' }, // Collection Time
    { targets: [3], width: '110px' }, // Driver Loaded
    { targets: [4], width: '110px' }, // ETA Delivery
    { targets: [5], width: '120px' }, // Mid-Point Check
    { targets: [6], width: 'auto' }, // Actions - flexible width
    { className: "text-center", targets: "_all" }
],
                ajax: {
                    url: "{{ route('admin.schedular.current-jobs.index') }}",
                    data: function (d) {
                        d.fromDate = $('#fromDate').val();
                        d.toDate = $('#toDate').val();
                        d.filterType = window.currentFilter;
                    }
                },
                pageLength: 50,
                lengthMenu: [[50, 75, 100, 125, 150], [50, 75, 100, 125, 150]],
                columns: [
                    { 
                        data: null,
                        name: 'checkbox',
                        className: 'text-center',
                        orderable: false,
                        title: '<input type="checkbox" id="selectAllCheckbox" title="Select/Deselect All">',
                        render: function(data, type, row) {
                            return `<input type="checkbox" class="order-checkbox" value="${row.id}" data-order-no="${row.orderNo}">`;
                        }
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
                        width: '200px',
                        render: function(data, type, row) {
                            let actions = '';

                            // View Notes button
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
                                actions += `<img onclick="confirmAction('Collection Check-In', ${row.id}, 'collection-checkin')" src="{{ asset('assets/admin/img/icons/check-in.png') }}" alt="Check-In" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Collection Check-in" class="row-icons" style="cursor: pointer;">`;
                            }
                            
                            // Driver Confirmed ETA
                            if (row.driverConfirmedETA === true || row.driverConfirmedETA === 1) {
                                actions += `<img src="{{ asset('assets/admin/img/icons/complete.png') }}" alt="Driver Confirmed" class="row-icons" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Driver ETA Confirmed">`;
                            } else {
                                let isDisabled = !(row.collectionCheckIn === true || row.collectionCheckIn === 1);
                                let tooltip = isDisabled ? "Complete Collection Check-in" : "Confirm Driver ETA";
                                let disabledAttr = isDisabled ? 'style="cursor: not-allowed; opacity: 0.5;"' : 'style="cursor: pointer;"';
                                let onclickAttr = isDisabled ? '' : `onclick="confirmAction('Driver ETA Confirmation', ${row.id}, 'driver-eta')"`;
                                
                                actions += `<img src="{{ asset('assets/admin/img/icons/driver-confirmed.png') }}" alt="Confirm ETA" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="${tooltip}" class="row-icons" ${disabledAttr} ${onclickAttr}>`;
                            }
                            
                            // Mid-Point Check
                            if (row.midpointCheckComplete === true || row.midpointCheckComplete === 1) {
                                actions += `<img src="{{ asset('assets/admin/img/icons/complete.png') }}" alt="Mid-Point Complete" class="row-icons" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Mid-Point Check Completed">`;
                            } else {
                                let isDisabled = !(row.driverConfirmedETA === true || row.driverConfirmedETA === 1);
                                let tooltip = isDisabled ? "Complete Driver ETA" : "Mark Mid-Point check complete";
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
                                let tooltip = isDisabled ? "Complete Driver ETA" : "Mark as delivered";
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
                initComplete: function() {
                    $('.dataTables_wrapper').css({
                        'width': '100%'
                    });
                    $('#datatable').css('table-layout', 'fixed');
                },
                drawCallback: function(settings) {
                    $('.dataTables_wrapper').css({
                        'width': '100%'
                    });
                    $('[data-bs-toggle="tooltip"]').tooltip();
                    $('#datatable').css('table-layout', 'fixed');
                    
                    // Re-check previously selected orders
                    selectedOrders.forEach(function(orderId) {
                        $(`.order-checkbox[value="${orderId}"]`).prop('checked', true);
                    });
                    
                    updateBulkActionsBar();
                }
            });

            // RESTORE FILTER STATE ON PAGE LOAD
            if (window.currentFilter !== 'all') {
                $('.stats-card').removeClass('active');
                $(`[data-filter="${window.currentFilter}"]`).addClass('active');
                updateFilterStatus(window.currentFilter);
            }

            // Card click event handlers
            $('.stats-card').on('click', function() {
                const filterType = $(this).data('filter');
                filterDataTable(filterType);
            });

            // NEW: Selection event handlers
            
            // Select All checkbox
            $(document).on('change', '#selectAllCheckbox', function() {
                const isChecked = $(this).prop('checked');
                $('.order-checkbox').prop('checked', isChecked);
                
                if (isChecked) {
                    // Add all visible orders to selection
                    $('.order-checkbox').each(function() {
                        const orderId = $(this).val();
                        if (!selectedOrders.includes(orderId)) {
                            selectedOrders.push(orderId);
                        }
                    });
                } else {
                    // Remove all visible orders from selection
                    $('.order-checkbox').each(function() {
                        const orderId = $(this).val();
                        selectedOrders = selectedOrders.filter(id => id !== orderId);
                    });
                }
                
                updateBulkActionsBar();
            });

            // Individual checkbox change
            $(document).on('change', '.order-checkbox', function() {
                const orderId = $(this).val();
                const isChecked = $(this).prop('checked');
                
                if (isChecked) {
                    if (!selectedOrders.includes(orderId)) {
                        selectedOrders.push(orderId);
                    }
                } else {
                    selectedOrders = selectedOrders.filter(id => id !== orderId);
                }
                
                // Update select all checkbox state
                const totalCheckboxes = $('.order-checkbox').length;
                const checkedCheckboxes = $('.order-checkbox:checked').length;
                
                if (checkedCheckboxes === 0) {
                    $('#selectAllCheckbox').prop('indeterminate', false).prop('checked', false);
                } else if (checkedCheckboxes === totalCheckboxes) {
                    $('#selectAllCheckbox').prop('indeterminate', false).prop('checked', true);
                } else {
                    $('#selectAllCheckbox').prop('indeterminate', true);
                }
                
                updateBulkActionsBar();
            });
        });

        // Update bulk actions bar visibility and count
        function updateBulkActionsBar() {
            const selectedCount = selectedOrders.length;
            $('#selectedCount').text(selectedCount);
            
            if (selectedCount > 0) {
                $('#bulkActionsBar').addClass('active');
            } else {
                $('#bulkActionsBar').removeClass('active');
            }
        }

        // Remove selected orders function
        function removeSelectedOrders() {
            if (selectedOrders.length === 0) {
                return;
            }
            
            // Get order numbers for display
            const orderNumbers = [];
            selectedOrders.forEach(function(orderId) {
                const checkbox = $(`.order-checkbox[value="${orderId}"]`);
                const orderNo = checkbox.data('order-no');
                if (orderNo) {
                    orderNumbers.push(orderNo);
                }
            });
            
            const orderList = orderNumbers.length > 5 
                ? orderNumbers.slice(0, 5).join(', ') + ` and ${orderNumbers.length - 5} more...`
                : orderNumbers.join(', ');
            
            Swal.fire({
                title: 'Remove Orders?',
                html: `Are you sure you want to remove the following order(s) from this list?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    processRemoveOrders();
                }
            });
        }

        // Process the removal
        function processRemoveOrders() {
            // Show loading
            Swal.fire({
                title: 'Removing Orders...',
                text: 'Please wait while we update the list',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            // Make AJAX call to remove orders
            $.ajax({
                url: '{{ route("admin.schedular.current-jobs.remove-orders") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    orderIds: selectedOrders
                },
                success: function(response) {
                    if (response.success) {
                        // Clear selections
                        selectedOrders = [];
                        updateBulkActionsBar();
                        
                        // Show success message
                        Swal.fire({
                            title: 'Orders Removed!',
                            text: `${response.removedCount} order(s) have been removed from the current jobs list.`,
                            icon: 'success',
                            timer: 3000,
                            showConfirmButton: false
                        });
                        
                        // Refresh the DataTable
                        setTimeout(() => {
                            table.ajax.reload();
                        }, 1000);
                        
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.message || 'An error occurred while removing orders.',
                            icon: 'error'
                        });
                    }
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr.responseText);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Error occurred while removing orders. Please try again.',
                        icon: 'error'
                    });
                }
            });
        }

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
            
            // Clear selections when changing filter
            selectedOrders = [];
            updateBulkActionsBar();
            
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
            
            // Clear selections
            selectedOrders = [];
            updateBulkActionsBar();
            
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

        function showDeliveryPopup(orderId, midpointComplete) {
            let title, text;
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
                            message += ' - Job completed!';
                        }
                        
                        Swal.fire({
                            title: response.completed ? 'Job Completed!' : 'Success!',
                            text: message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        // Refresh DataTable with current filter
                        setTimeout(() => {
                            table.ajax.reload();
                        }, 1000);
                        
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