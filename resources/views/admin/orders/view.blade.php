@extends('admin.layouts.app')

@section('title', 'Order Detail | CRM Assistant')

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
        
        /* Order details styling */
        .order-detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f3f4;
        }
        
        .order-detail-item:last-child {
            border-bottom: none;
        }
        
        .order-detail-label {
            font-weight: 600;
            color: #495057;
            min-width: 180px;
        }
        
        .order-detail-value {
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

        /* Status badge colors */
        .status-quote { background-color: #ffbc62 !important; color: #000 !important; }
        .status-open { background-color: #e097fd !important; color: #000 !important; }
        .status-mainopen { background-color: #ff8888 !important; color: #fff !important; }
        .status-planned { background-color: #ffff69 !important; color: #000 !important; }
        .status-signed-off { background-color: #88ccff !important; color: #000 !important; }
        .status-checked { background-color: #88ff88 !important; color: #000 !important; }
        .status-invoiced { background-color: #e0e1df !important; color: #000 !important; }

        /* Section headers */
        .section-header {
            background-color: #e9ecef;
            padding: 0.75rem 1rem;
            margin: 1rem 0 0.5rem 0;
            border-radius: 0.375rem;
            font-weight: 600;
            color: #495057;
        }

        /* Comments section */
        .comments-section {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.375rem;
            border: 1px solid #e9ecef;
            white-space: pre-wrap;
            word-wrap: break-word;
            max-height: 200px;
            overflow-y: auto;
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
                                <h4 class="mb-0">Order Details</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Orders</a></li>
                                        <li class="breadcrumb-item active">Order Detail</li>
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
                                <button class="nav-link active" id="v-pills-order-tab" data-bs-toggle="pill"
                                    data-bs-target="#v-pills-order" type="button" role="tab"
                                    aria-controls="v-pills-order" aria-selected="true">Order Details</button>
                                <button class="nav-link" id="v-pills-company-tab" data-bs-toggle="pill"
                                    data-bs-target="#v-pills-company" type="button" role="tab"
                                    aria-controls="v-pills-company" aria-selected="false">Company</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-lg-9">
                    <div class="tab-content" id="v-pills-tabContent">
                        <!-- Order Details Tab -->
                        <div class="tab-pane fade show active" id="v-pills-order" role="tabpanel"
                            aria-labelledby="v-pills-order-tab" tabindex="0">
                            
                            <!-- Basic Order Information -->
                            <div class="card mb-4">
                                <div class="card-header-cu">
                                    <div class="card-title d-flex justify-content-between align-items-center">
                                        <h4>Order Information</h4>
                                        <!-- <a href="{{ route('admin.orders.index') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-arrow-left me-1"></i>All Orders
                                        </a> -->
                                    </div>                                                
                                </div>
                                <div class="card-body">
                                    <div class="order-details">
                                        <!-- Order Number -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Order Number:</span>
                                            <span class="order-detail-value">
                                                <strong>{{ $order['orderNo'] ?? $order['orderNo'] ?? '-' }}</strong>
                                            </span>
                                        </div>

                                        <!-- Order Date -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Order Date:</span>
                                            <span class="order-detail-value">
                                                {{ isset($order['createdAt']) ? \Carbon\Carbon::parse($order['createdAt'])->format('d-m-Y H:i') : '-' }}
                                            </span>
                                        </div>

                                        <!-- Order Status -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Order Status:</span>
                                            <span class="order-detail-value">
                                                @if(!empty($order['status']))
                                                    @php
                                                        $status = strtolower($order['status']);
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
                                                        {{ ucfirst($order['status']) }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </div>

                                        <!-- Company Name & Customer Number -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Company Name:</span>
                                            <span class="order-detail-value">
                                                {{ $order['companyName'] ?? '-' }}
                                            </span>
                                        </div>

                                        <!-- Contact Name -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Contact Name:</span>
                                            <span class="order-detail-value">
                                                {{ !empty($customer['contacts']) && isset($customer['contacts'][0]['name']) ? $customer['contacts'][0]['name'] : '-' }}
                                            </span>
                                        </div>

                                        <!-- Carrier/Vehicle -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Carrier/Vehicle:</span>
                                            <span class="order-detail-value">
                                                {{ $order['vehicleTypeName'] ?? '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Collection Details -->
                            <div class="card mb-4">
                                <div class="card-header-cu">
                                    <h6 class="mb-0">Collection Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="order-details">
                                        <!-- Collection Address -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Company Contact Address:</span>
                                            <span class="order-detail-value">{{ $order['collectionAddress'] ?? '-' }}</span>
                                        </div>

                                        <!-- Address 2 -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Address 2:</span>
                                            <span class="order-detail-value">{{ $order['collectionAddress2'] ?? '-' }}</span>
                                        </div>

                                        <!-- Postcode -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Postcode:</span>
                                            <span class="order-detail-value">{{ $order['collectionPostcode'] ?? '-' }}</span>
                                        </div>

                                        <!-- City -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">City:</span>
                                            <span class="order-detail-value">{{ $order['collectionCity'] ?? '-' }}</span>
                                        </div>

                                        <!-- Country -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Country:</span>
                                            <span class="order-detail-value">{{ $order['collectionCountry'] ?? '-' }}</span>
                                        </div>

                                        <!-- Phone -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Phone:</span>
                                            <span class="order-detail-value">{{ $order['collectionPhone'] ?? '-' }}</span>
                                        </div>

                                        <!-- Collection Date -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Date:</span>
                                            <span class="order-detail-value">
                                                {{ isset($order['collectionDate']) ? \Carbon\Carbon::parse($order['collectionDate'])->format('d-m-Y') : '-' }}
                                            </span>
                                        </div>

                                        <!-- Collection Time -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Delivery Time:</span>
                                            <span class="order-detail-value">{{ $order['collectionTime'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Delivery Details -->
                            <div class="card mb-4">
                                <div class="card-header-cu">
                                    <h6 class="mb-0">Delivery Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="order-details">
                                        <!-- Delivery Address -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Company Contact Address:</span>
                                            <span class="order-detail-value">{{ $order['deliveryAddress'] ?? '-' }}</span>
                                        </div>

                                        <!-- Address 2 -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Address 2:</span>
                                            <span class="order-detail-value">{{ $order['deliveryAddress2'] ?? '-' }}</span>
                                        </div>

                                        <!-- Postcode -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Postcode:</span>
                                            <span class="order-detail-value">{{ $order['deliveryPostcode'] ?? '-' }}</span>
                                        </div>

                                        <!-- City -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">City:</span>
                                            <span class="order-detail-value">{{ $order['deliveryCity'] ?? '-' }}</span>
                                        </div>

                                        <!-- Country -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Country:</span>
                                            <span class="order-detail-value">{{ $order['deliveryCountry'] ?? '-' }}</span>
                                        </div>

                                        <!-- Phone -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Phone:</span>
                                            <span class="order-detail-value">{{ $order['deliveryPhone'] ?? '-' }}</span>
                                        </div>

                                        <!-- Delivery Date -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Date:</span>
                                            <span class="order-detail-value">
                                                {{ isset($order['deliveryDate']) ? \Carbon\Carbon::parse($order['deliveryDate'])->format('d-m-Y') : '-' }}
                                            </span>
                                        </div>

                                        <!-- Delivery Time -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">To Time:</span>
                                            <span class="order-detail-value">{{ $order['deliveryToTime'] ?? $order['deliveryTime'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cost Details -->
                            <div class="card mb-4">
                                <div class="card-header-cu">
                                    <h6 class="mb-0">Cost Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="order-details">
                                        <!-- Carrier/Vehicle -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Carrier/Vehicle:</span>
                                            <span class="order-detail-value">
                                                {{ $order['vehicleTypeName'] ?? '-' }}
                                            </span>
                                        </div>

                                        <!-- Sale Price -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Sale Price:</span>
                                            <span class="order-detail-value">
                                                @if(isset($order['orderPrice']))
                                                    £{{ number_format($order['orderPrice'] ?? $order['orderPrice'], 2) }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </div>

                                        <!-- Purchase Price -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Driver Cost:</span>
                                            <span class="order-detail-value">
                                                @if(isset($order['orderPurchasePrice']))
                                                    £{{ number_format($order['orderPurchasePrice'] ?? $order['orderPurchasePrice'], 2) }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </div>

                                        <!-- Distance -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Distance:</span>
                                            <span class="order-detail-value">
                                                {{ $order['distance'] ?? '-' }}
                                                @if(!empty($order['distance']))
                                                    miles
                                                @endif
                                            </span>
                                        </div>

                                        <!-- Last Updated -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Last Updated:</span>
                                            <span class="order-detail-value">
                                                {{ isset($order['updatedAt']) ? \Carbon\Carbon::parse($order['updatedAt'])->format('d-m-Y H:i') : '-' }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Internal Comments -->
                                    @if(!empty($order['internalComments']) || !empty($order['internalNotes']))
                                        <div class="section-header mt-3">
                                            Internal Comments
                                        </div>
                                        <div class="comments-section">
                                            {{ $order['internalComments'] ?? $order['internalNotes'] ?? 'No comments available' }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Company Tab -->
                        <div class="tab-pane fade" id="v-pills-company" role="tabpanel"
                            aria-labelledby="v-pills-company-tab" tabindex="0">
                            <div class="card mb-4">
                                <div class="card-header-cu">
                                    <h6 class="mb-0">Company Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="order-details">
                                        <!-- Company Name & Customer ID -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Company Name & ID:</span>
                                            <span class="order-detail-value">
                                                <strong>{{ $customer['companyName'] ?? '-' }}</strong>
                                                @if(!empty($customer['customerNo']))
                                                    ({{ $customer['customerNo'] }})
                                                @endif
                                            </span>
                                        </div>

                                        <!-- Company Address -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Company Address:</span>
                                            <span class="order-detail-value">{{ !empty($customer['businessAddress']['address']) ? $customer['businessAddress']['address'] : '-' }}</span>
                                        </div>

                                        <!-- Website -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Website:</span>
                                            <span class="order-detail-value">
                                                @if(!empty($customer['website']) && trim($customer['website']) !== '')
                                                    <a href="{{ $customer['website'] }}" target="_blank">{{ $customer['website'] }}</a>
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </div>

                                        <!-- Business Industry -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Business Industry:</span>
                                            <span class="order-detail-value">
                                                @php
                                                    $industry = $customer['additionalField1'] ?? '';
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
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Comments:</span>
                                            <span class="order-detail-value">
                                                {{ !empty($customer['notes']) ? $customer['notes'] : '-' }}
                                            </span>
                                        </div>

                                        <!-- CRM Notes -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">CRM Notes:</span>
                                            <span class="order-detail-value">
                                                {{ !empty($customer['crmNotes']) ? $customer['crmNotes'] : '-' }}
                                            </span>
                                        </div>

                                        <!-- Date Created -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Date Created:</span>
                                            <span class="order-detail-value">
                                                {{ isset($customer['createdAt']) ? \Carbon\Carbon::parse($customer['createdAt'])->format('d-m-Y H:i') : '-' }}
                                            </span>
                                        </div>

                                        <!-- Date Updated -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Date Updated:</span>
                                            <span class="order-detail-value">
                                                {{ isset($customer['updatedAt']) ? \Carbon\Carbon::parse($customer['updatedAt'])->format('d-m-Y H:i') : '-' }}
                                            </span>
                                        </div>

                                        <!-- Number of Previous Orders -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Number of Previous Orders:</span>
                                            <span class="order-detail-value">
                                                <strong>{{ $totalOrders ?? 0 }}</strong> orders
                                            </span>
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
</div>
@endsection

@push('scripts')
<script>
    // Bootstrap tab functionality is automatically handled
    // You can add custom JavaScript here if needed
</script>
@endpush