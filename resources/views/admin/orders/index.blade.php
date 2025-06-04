@extends('admin.layouts.app')

@section('title', 'Orders Overview | CRM Assistant')

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
            width: 12%; /* Flexible width */
        } /* Order Date/Time */
        
        #datatable th:nth-child(2), #datatable td:nth-child(2) { 
            min-width: 120px; 
            width: 10%; /* Flexible width */
        } /* Order Number */
        
        #datatable th:nth-child(3), #datatable td:nth-child(3) { 
            min-width: 180px; 
            width: 18%; /* Flexible width */
        } /* Company Name */
        
        #datatable th:nth-child(4), #datatable td:nth-child(4) { 
            min-width: 130px; 
            width: 13%; /* Flexible width */
        } /* Carrier/Vehicle */
        
        #datatable th:nth-child(5), #datatable td:nth-child(5) { 
            min-width: 100px; 
            width: 10%; /* Flexible width */
        } /* Sale Price */
        
        #datatable th:nth-child(6), #datatable td:nth-child(6) { 
            min-width: 100px; 
            width: 10%; /* Flexible width */
        } /* Purchase */
        
        #datatable th:nth-child(7), #datatable td:nth-child(7) { 
            min-width: 150px; 
            width: 15%; /* Flexible width */
        } /* Internal Comments */
        
        #datatable th:nth-child(8), #datatable td:nth-child(8) { 
            min-width: 120px; 
            width: 12%; /* Flexible width */
        } /* Order Status */
        
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
                                    <h4 class="mb-0">Orders Overview</h4>
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
                                            <th>Order Created</th>
                                            <th>Order Number</th>
                                            <th>Carrier/Vehicle</th>
                                            <th>Sale Price</th>
                                            <th>Drivers Cost</th>
                                            <th>Order Status</th>
                                            <th>Details</th>
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
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commentsModalLabel">
                    <i class="fas fa-comment-alt me-2"></i>Internal Comments
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Order Number: </strong><span id="modalOrderNumber">-</span>
                </div>
                <div class="mb-3">
                    <strong>Company: </strong><span id="modalCompanyName">-</span>
                </div>
                <div class="mb-3">
                    <label class="form-label"><strong>Comments:</strong></label>
                    <div id="modalCommentsContent" class="comments-content">
                        No comments available
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                    url: "{{ route('admin.orders.index') }}",
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
                        data: 'orderNo', 
                        name: 'orderNo',
                        className: 'text-nowrap',
                        render: function(data, type, row) {
                            return `<a href="/admin/orders/${row.id}">${data ?? '-'}</a>`; 
                        }
                    },
                    { 
                        data: 'vehicleTypeName', 
                        name: 'vehicleTypeName',
                        className: 'text-nowrap',
                        render: function(data, type, row) {
                            return data ? data : '-';
                        }
                    },
                    { 
                        data: 'orderPrice', 
                        name: 'orderPrice',
                        className: 'text-nowrap text-end',
                        render: function(data, type, row) {
                            return data ? '£' + data : '-';
                        }
                    },
                    { 
                        data: 'orderPurchasePrice', 
                        name: 'orderPurchasePrice',
                        className: 'text-nowrap text-end',
                        render: function(data, type, row) {
                            return data ? '£' + data : '-';
                        }
                    },
                    { 
                        data: 'status', 
                        name: 'status',
                        className: 'text-nowrap',
                        render: function(data, type, row) {
                            if (!data) return '-';
                            
                            // Capitalize first letter of each word in status
                            const capitalizedStatus = data.split(' ')
                                .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                                .join(' ');
                            
                            // Status color mapping
                            let statusClass = 'badge ';
                            const status = data.toLowerCase().replace(/[^a-z0-9]/g, ''); // Clean status name
                            
                            switch(status) {
                                case 'quote':
                                    statusClass += 'status-quote';
                                    break;
                                case 'open':
                                    statusClass += 'status-open';
                                    break;
                                case 'mainopen':
                                    statusClass += 'status-mainopen';
                                    break;
                                case 'planned':
                                    statusClass += 'status-planned';
                                    break;
                                case 'signedoff':
                                    statusClass += 'status-signed-off';
                                    break;
                                case 'checked':
                                    statusClass += 'status-checked';
                                    break;
                                case 'invoiced':
                                    statusClass += 'status-invoiced';
                                    break;
                                default:
                                    statusClass += 'bg-secondary';
                            }
                            
                            return `<span class="badge ${statusClass}">${capitalizedStatus}</span>`;
                        }
                    },
                    { 
                        data: null,
                        name: 'internalNotes', 
                        className: 'text-center',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            // Get the actual notes from the row
                            const notes = row.internalNotes;
                            
                            if (notes && notes.trim() !== '') {
                                return `<button type="button" class="btn btn-sm btn-outline-primary btn-view-comments" 
                                               onclick="showActualModal('${row.orderNo || ''}', '${row.customerNo || ''}', \`${notes.replace(/`/g, '\\`').replace(/\\/g, '\\\\')}\`)">
                                            <i class="fas fa-eye me-1"></i>View More
                                        </button>`;
                            } else {
                                return '<span class="text-muted">-</span>';
                            }
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

        function showActualModal(orderNo, customerNo, comments) {
            // Show modal immediately with available data
            $('#modalOrderNumber').text(orderNo || '-');
            $('#modalCompanyName').text('Loading...');
            $('#modalCommentsContent').text(comments || 'No comments available');
            $('#commentsModal').modal('show');
            
            // API call for company name
            if (customerNo) {
                $.ajax({
                    url: '{{ route("admin.getCustomer") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        customerNo: customerNo
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#modalCompanyName').text(response.companyName);
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
            
        // Improved function to show comments modal using data attributes
        function openCommentsModal(button) {
            try {
                const orderNo = button.getAttribute('data-order-no') || '-';
                const customerNo = button.getAttribute('data-customer-no') || '-';
                const encodedComments = button.getAttribute('data-comments') || '';
                
                let comments = 'No comments available';
                if (encodedComments) {
                    try {
                        comments = decodeURIComponent(escape(atob(encodedComments)));
                    } catch (e) {
                        console.error('Error decoding comments:', e);
                        comments = 'Error loading comments';
                    }
                }
                
                $('#modalOrderNumber').text(orderNo);
                $('#modalCompanyName').text(customerNo);
                $('#modalCommentsContent').text(comments);
                
                // Use Bootstrap 5 modal API
                const modal = new bootstrap.Modal(document.getElementById('commentsModal'));
                modal.show();
            } catch (error) {
                console.error('Error opening modal:', error);
                alert('Error opening comments modal');
            }
        }
        
        // Backup function (keep this for compatibility)
        function showCommentsModal(orderNo, customerNo, comments) {
            $('#modalOrderNumber').text(orderNo || '-');
            $('#modalCompanyName').text(customerNo || '-');
            $('#modalCommentsContent').text(comments || 'No comments available');
            
            // Try both Bootstrap 4 and 5 APIs
            if (typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(document.getElementById('commentsModal'));
                modal.show();
            } else {
                $('#commentsModal').modal('show');
            }
        }
    </script>
@endpush