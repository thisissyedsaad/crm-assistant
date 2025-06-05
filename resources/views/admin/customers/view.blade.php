@extends('admin.layouts.app')

@section('title', 'Customer Detail | CRM Assistant')

@push('links')
    <style>
        /* Card header styling */
        .card-header-cu {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 1.25rem;
        }
        
        .card-title h4 {
            margin: 0;
            color: #495057;
        }
        
        /* Customer details styling */
        .customer-detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f3f4;
        }
        
        .customer-detail-item:last-child {
            border-bottom: none;
        }
        
        .customer-detail-label {
            font-weight: 600;
            color: #495057;
            min-width: 150px;
        }
        
        .customer-detail-value {
            color: #6c757d;
            flex: 1;
            text-align: right;
        }

        /* Account tab styling */
        .account-tab .nav-link {
            border-radius: 0.375rem;
            margin-bottom: 0.5rem;
            border: none;
            background: transparent;
            color: #6c757d;
            padding: 0.75rem 1rem;
        }

        .account-tab .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }

        .account-tab .nav-link:hover {
            background-color: #e9ecef;
            color: #495057;
        }

        .account-tab .nav-link.active:hover {
            background-color: #0d6efd;
            color: white;
        }

        /* Order Status Colors */
        .status-quote { 
            background-color: #ffbc62 !important; 
            color: #000 !important; 
        }
        .status-open { 
            background-color: #e097fd !important; 
            color: #000 !important; 
        }
        .status-mainopen { 
            background-color: #ff8888 !important; 
            color: #fff !important; 
        }
        .status-planned { 
            background-color: #ffff69 !important; 
            color: #000 !important; 
        }
        .status-signed-off { 
            background-color: #88ccff !important; 
            color: #000 !important; 
        }
        .status-checked { 
            background-color: #88ff88 !important; 
            color: #000 !important; 
        }
        .status-invoiced { 
            background-color: #e0e1df !important; 
            color: #000 !important; 
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
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Customers Tab -->
                        <div class="tab-pane fade" id="v-pills-customers" role="tabpanel"
                            aria-labelledby="v-pills-customers-tab" tabindex="0">
                            <div class="card">
                                <div class="card-header-cu">
                                    <h6 class="mb-0">Company Contacts</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
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
                                    <h6 class="mb-0">Order History (Total: {{count($orders) }})</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
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
<script>
    // Bootstrap tab functionality is automatically handled
    // You can add custom JavaScript here if needed
</script>
@endpush