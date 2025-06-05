@extends('admin.layouts.app')

@section('title', 'Customers Overview | CRM Assistant')

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
        
        .customer-info {
            font-weight: 500;
            color: #495057;
        }
        
        .last-order-info {
            font-size: 0.9em;
            color: #6c757d;
            margin-top: 2px;
        }

        /* Show button styling */
        .show-last-order {
            color: #007bff;
            cursor: pointer;
            text-decoration: underline;
            font-size: 0.85em;
        }

        .show-last-order:hover {
            color: #0056b3;
        }

        .order-loading {
            color: #6c757d;
            font-style: italic;
            font-size: 0.85em;
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
                                    <h4 class="mb-0">Customers Overview</h4>
                                    <div class="page-title-right">
                                    </div>
                                </div>
                                
                                <!-- Autocomplete Search Section moved here -->
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="autocomplete-container" style="max-width: 100%;">
                                            <input type="text" 
                                                   id="customerSearch" 
                                                   class="autocomplete-input" 
                                                   placeholder="Type company name to search..."
                                                   autocomplete="off">
                                            <div id="autocompleteResults" class="autocomplete-results"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Table Section -->
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
                ordering: true, // Enable column sorting
                order: [[0, 'desc']], // Default sort by first column (Created Date/Time) descending
                ajax: {
                    url: "{{ route('admin.customers.index') }}",
                    data: function (d) {
                        d.fromDate = $('#fromDate').val();
                        d.toDate = $('#toDate').val();
                    }
                },
                pageLength: 25, // 25 records per page
                lengthMenu: [[25, 50, 100], [25, 50, 100]], // Limit options to max 100
                columns: [
                    { 
                        data: 'createdAt', 
                        name: 'createdAt',
                        className: 'text-nowrap',
                        orderable: true
                    },
                    { 
                        data: 'customerNo', 
                        name: 'customerNo',
                        className: 'text-nowrap',
                        orderable: true,
                        render: function(data, type, row) {
                            return `<a href="/admin/customers/${row.id}">${data ?? '-'}</a>`; 
                        }
                    },
                    { 
                        data: 'companyName', 
                        name: 'companyName',
                        className: 'text-wrap',
                        orderable: true,
                        render: function(data, type, row) {
                            return `<a href="/admin/customers/${row.id}">${data ?? '-'}</a>`; 
                        }
                    },  
                    { 
                        data: 'address', 
                        name: 'address',
                        className: 'text-wrap',
                        orderable: true,
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
                        orderable: true,
                        render: function(data, type, row) {
                            return data || '-';
                        }
                    }
                ],
                initComplete: function() {
                    $('.dataTables_wrapper').css({
                        'width': '100%'
                    });
                },
                drawCallback: function(settings) {
                    // Ensure proper styling after each draw
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

            // Autocomplete Search Functionality
            let searchTimeout;
            let currentRequest;

            $('#customerSearch').on('input', function() {
                const query = $(this).val().trim();
                const resultsContainer = $('#autocompleteResults');

                // Clear previous timeout
                clearTimeout(searchTimeout);

                // Cancel previous request if still pending
                if (currentRequest) {
                    currentRequest.abort();
                }

                if (query.length < 2) {
                    resultsContainer.hide().empty();
                    return;
                }

                // Show loading state immediately
                resultsContainer.html('<div class="autocomplete-loading">Searching...</div>').show();

                // Debounce search - wait 1 second after user stops typing
                searchTimeout = setTimeout(function() {
                    currentRequest = $.ajax({
                        url: "{{ route('admin.customers.autocomplete') }}",
                        method: 'GET',
                        data: {
                            query: query
                        },
                        success: function(response) {
                            resultsContainer.empty();

                            if (response.data && response.data.length > 0) {
                                response.data.forEach(function(customer) {
                                    const item = $(`
                                        <div class="autocomplete-item" data-id="${customer.id}">
                                            <div class="customer-info">${customer.companyName || customer.customerNo} ${customer.companyName && customer.customerNo ? '(' + customer.customerNo + ')' : ''}</div>
                                            <div class="last-order-info">
                                                Last Order: <span class="show-last-order" data-customer-id="${customer.id}">Show</span>
                                            </div>
                                        </div>
                                    `);

                                    resultsContainer.append(item);
                                });
                            } else {
                                resultsContainer.html('<div class="autocomplete-no-results">No customers found</div>');
                            }

                            resultsContainer.show();
                        },
                        error: function(xhr) {
                            if (xhr.statusText !== 'abort') {
                                resultsContainer.html('<div class="autocomplete-no-results">Error searching customers</div>');
                            }
                        },
                        complete: function() {
                            currentRequest = null;
                        }
                    });
                }, 1000); // 1 second delay
            });

            // Handle "Show" click for last order date
            $(document).on('click', '.show-last-order', function(e) {
                e.stopPropagation(); // Prevent autocomplete item click
                
                const $this = $(this);
                const customerId = $this.data('customer-id');
                
                // Show loading state
                $this.text('Loading...').addClass('order-loading').removeClass('show-last-order');
                
                // Fetch last order date
                $.ajax({
                    url: "{{ route('admin.customers.lastorder') }}",
                    method: 'GET',
                    data: {
                        customer_id: customerId
                    },
                    success: function(response) {
                        if (response.success) {
                            $this.text(response.last_order_date);
                            $this.removeClass('order-loading');
                            $this.css('cursor', 'default');
                        } else {
                            $this.text('Error').removeClass('order-loading');
                        }
                    },
                    error: function() {
                        $this.text('Error').removeClass('order-loading');
                    }
                });
            });

            // Handle autocomplete item click
            $(document).on('click', '.autocomplete-item', function(e) {
                // Don't redirect if clicking on "Show" button
                if ($(e.target).hasClass('show-last-order') || $(e.target).hasClass('order-loading')) {
                    return;
                }
                
                const customerId = $(this).data('id');
                const customerInfo = $(this).find('.customer-info').text();
                
                $('#customerSearch').val(customerInfo);
                $('#autocompleteResults').hide();
                
                // Redirect to customer detail page
                window.location.href = `/admin/customers/${customerId}`;
            });

            // Handle keyboard navigation
            $(document).on('keydown', '#customerSearch', function(e) {
                const resultsContainer = $('#autocompleteResults');
                const items = resultsContainer.find('.autocomplete-item');
                const activeItem = items.filter('.active');

                if (e.keyCode === 40) { // Down arrow
                    e.preventDefault();
                    if (activeItem.length === 0) {
                        items.first().addClass('active');
                    } else {
                        activeItem.removeClass('active');
                        const next = activeItem.next('.autocomplete-item');
                        if (next.length > 0) {
                            next.addClass('active');
                        } else {
                            items.first().addClass('active');
                        }
                    }
                } else if (e.keyCode === 38) { // Up arrow
                    e.preventDefault();
                    if (activeItem.length === 0) {
                        items.last().addClass('active');
                    } else {
                        activeItem.removeClass('active');
                        const prev = activeItem.prev('.autocomplete-item');
                        if (prev.length > 0) {
                            prev.addClass('active');
                        } else {
                            items.last().addClass('active');
                        }
                    }
                } else if (e.keyCode === 13) { // Enter
                    e.preventDefault();
                    if (activeItem.length > 0) {
                        activeItem.click();
                    }
                } else if (e.keyCode === 27) { // Escape
                    resultsContainer.hide();
                }
            });

            // Hide results when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.autocomplete-container').length) {
                    $('#autocompleteResults').hide();
                }
            });
        });
    </script>
@endpush