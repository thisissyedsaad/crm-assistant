@extends('admin.layouts.app')

@section('title', 'Order Detail | CRM Assistant')

@push('links')
    <style>
        /* Status badge colors - same as orders list */
        .status-quote { background-color: #ffbc62 !important; color: #000 !important; }
        .status-open { background-color: #e097fd !important; color: #000 !important; }
        .status-mainopen { background-color: #ff8888 !important; color: #fff !important; }
        .status-planned { background-color: #ffff69 !important; color: #000 !important; }
        .status-signed-off { background-color: #88ccff !important; color: #000 !important; }
        .status-checked { background-color: #88ff88 !important; color: #000 !important; }
        .status-invoiced { background-color: #e0e1df !important; color: #000 !important; }
        
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
            min-width: 150px;
        }
        
        .order-detail-value {
            color: #6c757d;
            flex: 1;
            text-align: right;
        }
        
        /* Status badge specific styling */
        .status-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            font-weight: 500;
            font-size: 0.875rem;
        }
        
        /* Comments section */
        .comments-section {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.375rem;
            border: 1px solid #e9ecef;
            margin-top: 0.5rem;
        }
        
        .comments-text {
            white-space: pre-wrap;
            word-wrap: break-word;
            margin: 0;
            color: #495057;
        }
    </style>
@endpush

@section('content')
<!-- Main Content Area -->
<div class="main-content introduction-farm">
    <div class="content-wraper-area">
        <div class="container-fluid">
            <div class="row g-4">
                <div class="col-lg-12">
                    <div class="tab-content" id="v-pills-tabContent">
                        <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel"
                            aria-labelledby="v-pills-home-tab" tabindex="0">
                            <div class="card mb-4">
                                <div class="card-header-cu">
                                    <div class="card-title d-flex justify-content-between align-items-center">
                                        <h4>Order Details</h4>
                                        <a href="{{ route('admin.orders.index') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-arrow-left me-1"></i>All Orders
                                        </a>
                                    </div>                                                
                                </div>
                                <div class="card-body">
                                    <div class="order-details">
                                        <!-- Order Number -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Order Number:</span>
                                            <span class="order-detail-value">
                                                <strong>{{ $order['attributes']['orderNo'] ?? '-' }}</strong>
                                            </span>
                                        </div>

                                        <!-- Customer Number -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Customer Number:</span>
                                            <span class="order-detail-value">{{ $order['attributes']['customerNo'] ?? '-' }}</span>
                                        </div>

                                        <!-- Vehicle Type -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Carrier/Vehicle:</span>
                                            <span class="order-detail-value">{{ $order['attributes']['vehicleTypeName'] ?? '-' }}</span>
                                        </div>

                                        <!-- Sale Price -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Sale Price:</span>
                                            <span class="order-detail-value">
                                                <strong>{{ $order['attributes']['orderPrice'] ?? '-' }}</strong>
                                            </span>
                                        </div>

                                        <!-- Purchase Price -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Purchase Price:</span>
                                            <span class="order-detail-value">{{ $order['attributes']['orderPurchasePrice'] ?? '-' }}</span>
                                        </div>

                                        <!-- Order Status with Color Badge -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Order Status:</span>
                                            <span class="order-detail-value">
                                                @php
                                                    $status = $order['attributes']['status'] ?? '';
                                                    $statusClass = 'badge bg-secondary'; // Default
                                                    
                                                    if ($status) {
                                                        $cleanStatus = strtolower(preg_replace('/[^a-z0-9]/i', '', $status));
                                                        
                                                        switch($cleanStatus) {
                                                            case 'quote':
                                                                $statusClass = 'badge status-quote';
                                                                break;
                                                            case 'open':
                                                                $statusClass = 'badge status-open';
                                                                break;
                                                            case 'mainopen':
                                                                $statusClass = 'badge status-mainopen';
                                                                break;
                                                            case 'planned':
                                                                $statusClass = 'badge status-planned';
                                                                break;
                                                            case 'signedoff':
                                                                $statusClass = 'badge status-signed-off';
                                                                break;
                                                            case 'checked':
                                                                $statusClass = 'badge status-checked';
                                                                break;
                                                            case 'invoiced':
                                                                $statusClass = 'badge status-invoiced';
                                                                break;
                                                            default:
                                                                $statusClass = 'badge bg-secondary';
                                                        }
                                                    }
                                                @endphp
                                                
                                                @if($status)
                                                    <span class="{{ $statusClass }} status-badge">{{ $status }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </span>
                                        </div>

                                        <!-- Order Date -->
                                        <div class="order-detail-item">
                                            <span class="order-detail-label">Order Date:</span>
                                            <span class="order-detail-value">
                                                {{ isset($order['createdAt']) ? \Carbon\Carbon::parse($order['createdAt'])->format('d-m-Y H:i') : '-' }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Internal Comments Section -->
                                    @if(!empty($order['attributes']['internalNotes']))
                                        <div class="mt-4">
                                            <h5 class="mb-3">
                                                <i class="fas fa-comment-alt me-2 text-primary"></i>Internal Comments
                                            </h5>
                                            <div class="comments-section">
                                                <p class="comments-text">{{ $order['attributes']['internalNotes'] }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <div class="mt-4">
                                            <h5 class="mb-3">
                                                <i class="fas fa-comment-alt me-2 text-muted"></i>Internal Comments
                                            </h5>
                                            <div class="comments-section">
                                                <p class="comments-text text-muted">No internal comments available for this order.</p>
                                            </div>
                                        </div>
                                    @endif
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

@endpush