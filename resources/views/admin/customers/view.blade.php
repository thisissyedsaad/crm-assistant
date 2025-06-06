@extends('admin.layouts.app')

@section('title', 'Customer Detail | CRM Assistant')

@push('links')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/dataTables.bootstrap5.min.css') }}">
    <style>
        /* Original Card header styling */
        .card-header-cu {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 1.25rem;
        }
        
        .card-title h4 {
            margin: 0;
            color: #495057;
        }
        
        /* Customer details styling with animations */
        .customer-detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f3f4;
            transition: all 0.3s ease;
            position: relative;
            /* Animation properties */
            animation: slideInUp 0.6s ease forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        .customer-detail-item:hover {
            background: linear-gradient(90deg, rgba(13, 110, 253, 0.05), transparent);
            padding-left: 15px;
            border-radius: 8px;
            margin: 0 -15px;
            transform: translateY(-2px);
        }
        
        .customer-detail-item:last-child {
            border-bottom: none;
        }
        
        .customer-detail-label {
            font-weight: 600;
            color: #495057;
            min-width: 150px;
            transition: all 0.3s ease;
        }

        .customer-detail-item:hover .customer-detail-label {
            color: #0d6efd;
            transform: translateX(5px);
        }
        
        .customer-detail-value {
            color: #6c757d;
            flex: 1;
            text-align: right;
            transition: all 0.3s ease;
        }

        .customer-detail-item:hover .customer-detail-value {
            color: #495057;
        }

        /* Account tab styling with hover effects */
        .account-tab .nav-link {
            border-radius: 0.375rem;
            margin-bottom: 0.5rem;
            border: none;
            background: transparent;
            color: #6c757d;
            padding: 0.75rem 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .account-tab .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(13, 110, 253, 0.1), transparent);
            transition: left 0.6s;
        }

        .account-tab .nav-link:hover::before {
            left: 100%;
        }

        .account-tab .nav-link.active {
            background-color: #0d6efd;
            color: white;
            transform: translateX(5px);
        }

        .account-tab .nav-link:hover {
            background-color: #e9ecef;
            color: #495057;
            transform: translateX(3px);
        }

        .account-tab .nav-link.active:hover {
            background-color: #0d6efd;
            color: white;
            transform: translateX(5px);
        }

        /* Order Status Colors (original) */
        .status-quote { 
            background-color: #ffbc62 !important; 
            color: #000 !important; 
            transition: all 0.3s ease;
        }
        .status-open { 
            background-color: #e097fd !important; 
            color: #000 !important; 
            transition: all 0.3s ease;
        }
        .status-mainopen { 
            background-color: #ff8888 !important; 
            color: #fff !important; 
            transition: all 0.3s ease;
        }
        .status-planned { 
            background-color: #ffff69 !important; 
            color: #000 !important; 
            transition: all 0.3s ease;
        }
        .status-signed-off { 
            background-color: #88ccff !important; 
            color: #000 !important; 
            transition: all 0.3s ease;
        }
        .status-checked { 
            background-color: #88ff88 !important; 
            color: #000 !important; 
            transition: all 0.3s ease;
        }
        .status-invoiced { 
            background-color: #e0e1df !important; 
            color: #000 !important; 
            transition: all 0.3s ease;
        }

        /* Status badge hover effects */
        .badge {
            transition: all 0.3s ease;
        }

        .badge:hover {
            transform: scale(1.05);
        }

        /* DataTables styling */
        .dataTables_wrapper {
            animation: fadeInUp 0.8s ease forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }

        .dataTables_wrapper .dataTables_filter input {
            border-radius: 0.375rem;
            border: 1px solid #ced4da;
            padding: 0.375rem 0.75rem;
            margin-left: 0.5rem;
        }

        .dataTables_wrapper .dataTables_length select {
            border-radius: 0.375rem;
            border: 1px solid #ced4da;
            padding: 0.375rem 0.75rem;
            margin: 0 0.5rem;
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            margin-top: 1rem;
        }

        /* Table hover effects for DataTables */
        .dataTable tbody tr {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .dataTable tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.05) !important;
            transform: translateX(5px);
        }

        /* Card hover effects */
        .card {
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        /* Animation delays for staggered effect */
        .customer-detail-item:nth-child(1) { animation-delay: 0.1s; }
        .customer-detail-item:nth-child(2) { animation-delay: 0.2s; }
        .customer-detail-item:nth-child(3) { animation-delay: 0.3s; }
        .customer-detail-item:nth-child(4) { animation-delay: 0.4s; }
        .customer-detail-item:nth-child(5) { animation-delay: 0.5s; }
        .customer-detail-item:nth-child(6) { animation-delay: 0.6s; }
        .customer-detail-item:nth-child(7) { animation-delay: 0.7s; }
        .customer-detail-item:nth-child(8) { animation-delay: 0.8s; }

        @keyframes slideInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Breadcrumb link hover effects */
        .breadcrumb-item a {
            transition: color 0.3s ease;
        }

        .breadcrumb-item a:hover {
            color: #0d6efd !important;
        }

        /* Link hover effects */
        a {
            transition: all 0.3s ease;
        }

        a:hover {
            transform: translateX(2px);
        }

        /* Page load animation for cards */
        .card {
            animation: cardFadeIn 0.8s ease forwards;
            opacity: 0;
            transform: translateY(30px);
        }

        .col-lg-3 .card {
            animation-delay: 0.2s;
        }

        .col-lg-9 .card {
            animation-delay: 0.4s;
        }

        @keyframes cardFadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Smooth transitions for all interactive elements */
        button, .nav-link, .btn {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Enhanced focus states */
        .nav-link:focus {
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        /* Pagination styling */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 0.375rem !important;
            margin: 0 2px;
            transition: all 0.3s ease;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            transform: translateY(-2px);
        }

        /* No data styling */
        .dataTables_empty {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 2rem !important;
        }
    </style>
@endpush

@section('content')
<!-- Main Content Area -->
<div class="main-content introduction-farm">
    <div class="content-wraper-area">
        <div class="container-fluid">
            <div class="row g-4">
                <!-- Breadcrumb -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-body card-breadcrumb">
                            <div class="page-title-box d-flex align-items-center justify-content-between">
                                <h4 class="mb-0">Customer Details</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customers</a></li>
                                        <li class="breadcrumb-item active">Customer Detail</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Navigation -->
                <div class="col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="nav flex-column nav-pills me-3 account-tab" id="v-pills-tab"
                                role="tablist" aria-orientation="vertical">
                                <button class="nav-link active" id="v-pills-company-tab" data-bs-toggle="pill"
                                    data-bs-target="#v-pills-company" type="button" role="tab"
                                    aria-controls="v-pills-company" aria-selected="true">Company</button>
                                <button class="nav-link" id="v-pills-customers-tab" data-bs-toggle="pill"
                                    data-bs-target="#v-pills-customers" type="button" role="tab"
                                    aria-controls="v-pills-customers" aria-selected="false">Customers</button>
                                <button class="nav-link" id="v-pills-orders-tab" data-bs-toggle="pill"
                                    data-bs-target="#v-pills-orders" type="button" role="tab"
                                    aria-controls="v-pills-orders" aria-selected="false">Order History</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-lg-9">
                    <div class="tab-content" id="v-pills-tabContent">
                        <!-- Company Tab -->
                        <div class="tab-pane fade show active" id="v-pills-company" role="tabpanel"
                            aria-labelledby="v-pills-company-tab" tabindex="0">
                            <div class="card mb-4">
                                <div class="card-header-cu">
                                    <div class="card-title d-flex justify-content-between align-items-center">
                                        <h4>Company Information</h4>
                                        <!-- <a href="{{ route('admin.customers.index') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-arrow-left me-1"></i>All Customers
                                        </a> -->
                                    </div>                                                
                                </div>
                                <div class="card-body">
                                    <div class="customer-details">
                                        <!-- Company Name & Customer ID -->
                                        <div class="customer-detail-item">
                                            <span class="customer-detail-label">Company Name & ID:</span>
                                            <span class="customer-detail-value">
                                                <strong>{{ $customer['attributes']['companyName'] ?? '-' }}</strong>
                                                @if(!empty($customer['attributes']['customerNo']))
                                                    ({{ $customer['attributes']['customerNo'] }})
                                                @endif
                                            </span>
                                        </div>

                                        <!-- Company Address -->
                                        <div class="customer-detail-item">
                                            <span class="customer-detail-label">Company Address:</span>
                                            <span class="customer-detail-value">{{ $customer['attributes']['businessAddress']['address'] ?? '-' }}</span>
                                        </div>

                                        <!-- Website -->
                                        <div class="customer-detail-item">
                                            <span class="customer-detail-label">Website:</span>
                                            <span class="customer-detail-value">
                                                @if(!empty($customer['attributes']['website']) && trim($customer['attributes']['website']) !== '')
                                                    <a href="{{ $customer['attributes']['website'] }}" target="_blank">{{ $customer['attributes']['website'] }}</a>
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </div>

                                        <!-- Business Industry -->
                                        <div class="customer-detail-item">
                                            <span class="customer-detail-label">Business Industry:</span>
                                            <span class="customer-detail-value">
                                                @php
                                                    $industry = $customer['attributes']['additionalField1'] ?? '';
                                                    $excludedIndustries = ['SDT Contact Us', 'CSD Instant Quote', 'Quote', 'N/A', 'Aircall CSD', 'MSDC Instant Quote', 'Aircall SDT'];
                                                @endphp
                                                
                                                @if(!empty($industry) && !in_array($industry, $excludedIndustries))
                                                    {{ $industry }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </div>

                                        <!-- Comments -->
                                        <div class="customer-detail-item">
                                            <span class="customer-detail-label">Comments:</span>
                                            <span class="customer-detail-value">
                                                {{ !empty($customer['attributes']['comments']) ? $customer['attributes']['comments'] : '-' }}
                                            </span>
                                        </div>

                                        <!-- CRM Notes -->
                                        <div class="customer-detail-item">
                                            <span class="customer-detail-label">CRM Notes:</span>
                                            <span class="customer-detail-value">
                                                {{ !empty($customer['attributes']['crmNotes']) ? $customer['attributes']['crmNotes'] : '-' }}
                                            </span>
                                        </div>

                                        <!-- Date Created -->
                                        <div class="customer-detail-item">
                                            <span class="customer-detail-label">Date Created:</span>
                                            <span class="customer-detail-value">
                                                {{ \Carbon\Carbon::parse($customer['createdAt'])->format('d-m-Y H:i') }}
                                            </span>
                                        </div>

                                        <!-- Date Updated -->
                                        <div class="customer-detail-item">
                                            <span class="customer-detail-label">Date Updated:</span>
                                            <span class="customer-detail-value">
                                                @if(!empty($customer['updatedAt']))
                                                    {{ \Carbon\Carbon::parse($customer['updatedAt'])->format('d-m-Y H:i') }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </div>

                                        <!-- Number of Previous Orders -->
                                        <div class="customer-detail-item">
                                            <span class="customer-detail-label">Number of Previous Orders:</span>
                                            <span class="customer-detail-value">
                                                <strong>{{ count($orders) ?? 0 }}</strong> orders
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Customers Tab -->
                        <div class="tab-pane fade" id="v-pills-customers" role="tabpanel"
                            aria-labelledby="v-pills-customers-tab" tabindex="0">
                            <div class="card">
                                <div class="card-header-cu">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 d-flex align-items-center">
                                            <i class="bx bx-group me-2 text-primary"></i>
                                            Company Contacts
                                        </h6>
                                        <span class="badge bg-success rounded-pill">
                                            <i class="bx bx-user me-1"></i>
                                            @if(!empty($customer['attributes']['contacts']) && is_array($customer['attributes']['contacts']))
                                                {{ count($customer['attributes']['contacts']) }} Contacts
                                            @else
                                                0 Contacts
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="contactsTable" class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Phone</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(!empty($customer['attributes']['contacts']) && is_array($customer['attributes']['contacts']))
                                                    @foreach($customer['attributes']['contacts'] as $contact)
                                                        <tr>
                                                            <td>{{ $contact['name'] ?? '-' }}</td>
                                                            <td>{{ $contact['email'] ?? '-' }}</td>
                                                            <td>{{ $contact['phone'] ?? '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted">No contacts found</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order History Tab -->
                        <div class="tab-pane fade" id="v-pills-orders" role="tabpanel"
                            aria-labelledby="v-pills-orders-tab" tabindex="0">
                            <div class="card">
                                <div class="card-header-cu">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 d-flex align-items-center">
                                            <i class="bx bx-list-ul me-2 text-primary"></i>
                                            Order History
                                        </h6>
                                        <span class="badge bg-primary rounded-pill">
                                            <i class="bx bx-hash me-1"></i>
                                            {{count($orders) }} Orders
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="ordersTable" class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Order Date/Time</th>
                                                    <th>Order Number</th>
                                                    <th>Carrier/Vehicle</th>
                                                    <th>Sale Price</th>
                                                    <th>Purchase</th>
                                                    <th>Order Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(!empty($orders) && is_array($orders) && count($orders) > 0)
                                                    @foreach($orders as $order)
                                                        <tr>
                                                            <td>
                                                                @if(isset($order['createdAt']))
                                                                    {{ \Carbon\Carbon::parse($order['createdAt'])->format('d-m-Y H:i') }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if(!empty($order['attributes']['orderNo']))
                                                                    <a href="{{ route('admin.orders.show', $order['id'] ?? '#') }}" class="text-primary">
                                                                        {{ $order['attributes']['orderNo'] }}
                                                                    </a>
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td>
                                                                {{ $order['attributes']['vehicleTypeName'] ?? '-' }}
                                                            </td>
                                                            <td>
                                                                @if(isset($order['attributes']['orderPrice']))
                                                                    £{{ number_format($order['attributes']['orderPrice'], 2) }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if(isset($order['attributes']['orderPurchasePrice']))
                                                                    £{{ number_format($order['attributes']['orderPurchasePrice'], 2) }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if(!empty($order['attributes']['status']))
                                                                    @php
                                                                        $status = strtolower($order['attributes']['status']);
                                                                        $statusClass = 'badge ';
                                                                        
                                                                        switch($status) {
                                                                            case 'quote':
                                                                                $statusClass .= 'status-quote';
                                                                                break;
                                                                            case 'open':
                                                                                $statusClass .= 'status-open';
                                                                                break;
                                                                            case 'mainopen':
                                                                                $statusClass .= 'status-mainopen';
                                                                                break;
                                                                            case 'planned':
                                                                                $statusClass .= 'status-planned';
                                                                                break;
                                                                            case 'signedoff':
                                                                                $statusClass .= 'status-signed-off';
                                                                                break;
                                                                            case 'checked':
                                                                                $statusClass .= 'status-checked';
                                                                                break;
                                                                            case 'invoiced':
                                                                                $statusClass .= 'status-invoiced';
                                                                                break;
                                                                            default:
                                                                                $statusClass .= 'bg-secondary';
                                                                        }
                                                                    @endphp
                                                                    <span class="{{ $statusClass }}">
                                                                        {{ ucfirst($order['attributes']['status']) }}
                                                                    </span>
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted">No orders found for this customer</td>
                                                    </tr>
                                                @endif
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
    </div>
</div>
@endsection

@push('scripts')
    <!-- DataTables JS -->
    <script src="{{ asset('assets/admin/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/dataTables.bootstrap5.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Initialize Contacts DataTable
            $('#contactsTable').DataTable({
                pageLength: 10,
                lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
                responsive: true,
                language: {
                    search: "Search Contacts:",
                    lengthMenu: "Show _MENU_ contacts per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ contacts",
                    infoEmpty: "No contacts found",
                    infoFiltered: "(filtered from _MAX_ total contacts)",
                    emptyTable: "No contacts available",
                    zeroRecords: "No matching contacts found"
                },
                dom: '<"row"<"col-md-6"l><"col-md-6"f>>rtip',
                order: [[0, 'asc']], // Sort by name ascending
                columnDefs: [
                    {
                        targets: [0, 1, 2], // All columns
                        className: 'text-center'
                    }
                ]
            });

            // Initialize Orders DataTable
            $('#ordersTable').DataTable({
                pageLength: 10,
                lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
                responsive: true,
                language: {
                    search: "Search Orders:",
                    lengthMenu: "Show _MENU_ orders per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ orders",
                    infoEmpty: "No orders found",
                    infoFiltered: "(filtered from _MAX_ total orders)",
                    emptyTable: "No orders available",
                    zeroRecords: "No matching orders found"
                },
                dom: '<"row"<"col-md-6"l><"col-md-6"f>>rtip',
                order: [[0, 'desc']], // Sort by date descending (newest first)
                columnDefs: [
                    {
                        targets: [0], // Date column
                        type: 'date'
                    },
                    {
                        targets: [3, 4], // Price columns
                        className: 'text-end'
                    },
                    {
                        targets: [5], // Status column
                        className: 'text-center'
                    }
                ]
            });

            // Bootstrap tab functionality with smooth scroll
            const tabButtons = document.querySelectorAll('[data-bs-toggle="pill"]');
            tabButtons.forEach(button => {
                button.addEventListener('shown.bs.tab', function (e) {
                    // Smooth scroll to top of content
                    document.querySelector('.main-content').scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });

                    // Redraw DataTables when tab is shown (fixes column width issues)
                    setTimeout(function() {
                        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
                    }, 200);
                });
            });

            // Add animation to DataTables wrapper when tabs are shown
            $('#v-pills-customers-tab').on('shown.bs.tab', function() {
                setTimeout(function() {
                    $('#contactsTable_wrapper').addClass('animate__fadeInUp');
                }, 100);
            });

            $('#v-pills-orders-tab').on('shown.bs.tab', function() {
                setTimeout(function() {
                    $('#ordersTable_wrapper').addClass('animate__fadeInUp');
                }, 100);
            });
        });
    </script>
@endpush