@extends('admin.layouts.app')

@section('title', 'Order Detail | CSD Assistant')

@push('links')
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
        
        /* Order details styling with animations */
        .order-detail-item {
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

        .order-detail-item:hover {
            background: linear-gradient(90deg, rgba(13, 110, 253, 0.05), transparent);
            padding-left: 15px;
            border-radius: 8px;
            margin: 0 -15px;
            transform: translateY(-2px);
        }
        
        .order-detail-item:last-child {
            border-bottom: none;
        }
        
        .order-detail-label {
            font-weight: 600;
            color: #495057;
            min-width: 180px;
            transition: all 0.3s ease;
        }

        .order-detail-item:hover .order-detail-label {
            color: #0d6efd;
            transform: translateX(5px);
        }
        
        .order-detail-value {
            color: #6c757d;
            flex: 1;
            text-align: right;
            transition: all 0.3s ease;
        }

        .order-detail-item:hover .order-detail-value {
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

        /* Status badge colors (original) with hover effects */
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

        /* Section headers with animation */
        .section-header {
            background-color: #e9ecef;
            padding: 0.75rem 1rem;
            margin: 1rem 0 0.5rem 0;
            border-radius: 0.375rem;
            font-weight: 600;
            color: #495057;
            transition: all 0.3s ease;
            animation: slideInUp 0.6s ease forwards;
            opacity: 0;
            transform: translateY(20px);
            animation-delay: 0.5s;
        }

        .section-header:hover {
            background-color: #dee2e6;
            transform: translateX(5px);
        }

        /* Comments section with animation */
        .comments-section {
          /* background-color: #f8f9fa; */
            padding: 1rem;
            border-radius: 0.375rem;
            border: 1px solid #e9ecef;
            /* white-space: pre-wrap; */
            word-wrap: break-word;
            max-height: 200px;
            overflow-y: auto;
            transition: all 0.3s ease;
            animation: slideInUp 0.6s ease forwards;
            opacity: 0;
            transform: translateY(20px);
            animation-delay: 0.6s;
        }

        .comments-section:hover {
            /* background-color: #f1f3f4; */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Card hover effects */
        .card {
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        /* Animation delays for staggered effect - Basic Order Information */
        .card:nth-child(2) .order-detail-item:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) .order-detail-item:nth-child(2) { animation-delay: 0.2s; }
        .card:nth-child(2) .order-detail-item:nth-child(3) { animation-delay: 0.3s; }
        .card:nth-child(2) .order-detail-item:nth-child(4) { animation-delay: 0.4s; }
        .card:nth-child(2) .order-detail-item:nth-child(5) { animation-delay: 0.5s; }
        .card:nth-child(2) .order-detail-item:nth-child(6) { animation-delay: 0.6s; }

        /* Animation delays for Collection Details */
        .card:nth-child(3) .order-detail-item:nth-child(1) { animation-delay: 0.7s; }
        .card:nth-child(3) .order-detail-item:nth-child(2) { animation-delay: 0.8s; }
        .card:nth-child(3) .order-detail-item:nth-child(3) { animation-delay: 0.9s; }
        .card:nth-child(3) .order-detail-item:nth-child(4) { animation-delay: 1.0s; }
        .card:nth-child(3) .order-detail-item:nth-child(5) { animation-delay: 1.1s; }
        .card:nth-child(3) .order-detail-item:nth-child(6) { animation-delay: 1.2s; }
        .card:nth-child(3) .order-detail-item:nth-child(7) { animation-delay: 1.3s; }
        .card:nth-child(3) .order-detail-item:nth-child(8) { animation-delay: 1.4s; }

        /* Animation delays for Delivery Details */
        .card:nth-child(4) .order-detail-item:nth-child(1) { animation-delay: 1.5s; }
        .card:nth-child(4) .order-detail-item:nth-child(2) { animation-delay: 1.6s; }
        .card:nth-child(4) .order-detail-item:nth-child(3) { animation-delay: 1.7s; }
        .card:nth-child(4) .order-detail-item:nth-child(4) { animation-delay: 1.8s; }
        .card:nth-child(4) .order-detail-item:nth-child(5) { animation-delay: 1.9s; }
        .card:nth-child(4) .order-detail-item:nth-child(6) { animation-delay: 2.0s; }
        .card:nth-child(4) .order-detail-item:nth-child(7) { animation-delay: 2.1s; }
        .card:nth-child(4) .order-detail-item:nth-child(8) { animation-delay: 2.2s; }

        /* Animation delays for Cost Details */
        .card:nth-child(5) .order-detail-item:nth-child(1) { animation-delay: 2.3s; }
        .card:nth-child(5) .order-detail-item:nth-child(2) { animation-delay: 2.4s; }
        .card:nth-child(5) .order-detail-item:nth-child(3) { animation-delay: 2.5s; }
        .card:nth-child(5) .order-detail-item:nth-child(4) { animation-delay: 2.6s; }

        @keyframes slideInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Company tab animation delays */
        #v-pills-company .order-detail-item:nth-child(1) { animation-delay: 0.1s; }
        #v-pills-company .order-detail-item:nth-child(2) { animation-delay: 0.2s; }
        #v-pills-company .order-detail-item:nth-child(3) { animation-delay: 0.3s; }
        #v-pills-company .order-detail-item:nth-child(4) { animation-delay: 0.4s; }
        #v-pills-company .order-detail-item:nth-child(5) { animation-delay: 0.5s; }
        #v-pills-company .order-detail-item:nth-child(6) { animation-delay: 0.6s; }
        #v-pills-company .order-detail-item:nth-child(7) { animation-delay: 0.7s; }
        #v-pills-company .order-detail-item:nth-child(8) { animation-delay: 0.8s; }
        #v-pills-company .order-detail-item:nth-child(9) { animation-delay: 0.9s; }

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

        .col-lg-9 .card:nth-child(1) {
            animation-delay: 0.4s;
        }

        .col-lg-9 .card:nth-child(2) {
            animation-delay: 0.6s;
        }

        .col-lg-9 .card:nth-child(3) {
            animation-delay: 0.8s;
        }

        .col-lg-9 .card:nth-child(4) {
            animation-delay: 1.0s;
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

        /* Comments section scrollbar styling */
        .comments-section::-webkit-scrollbar {
            width: 6px;
        }

        .comments-section::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .comments-section::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        .comments-section::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Price highlighting on hover */
        .order-detail-value:has-text('£') {
            transition: all 0.3s ease;
        }

        .order-detail-item:hover .order-detail-value:has-text('£') {
            color: #28a745 !important;
            font-weight: 600;
        }

/* Add this CSS to your existing styles */

/* Equal height row for Order Information and Cost Details */
.equal-height-row {
    display: flex;
    flex-wrap: wrap;
}

.equal-height-row .col-lg-6 {
    display: flex;
    flex-direction: column;
}

/* Ensure cards take full height */
.equal-height-row .card {
    display: flex;
    flex-direction: column;
    height: 100%;
}

/* Make card body flexible */
.equal-height-row .card-body {
    display: flex;
    flex-direction: column;
    flex: 1;
}

/* Ensure order-details fills available space */
.equal-height-row .order-details {
    flex: 1;
    display: flex;
    flex-direction: column;
}

/* Optional: Add minimum height for consistency */
.equal-height-row .card {
    min-height: 400px;
}

/* Ensure equal spacing between items */
.equal-height-row .order-detail-item {
    flex: 0 0 auto;
}

/* If one card has fewer items, this will distribute space evenly */
.equal-height-row .order-details.flex-grow-1 {
    justify-content: flex-start;
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
                            
<!-- Replace the Order Information & Cost Details section with this -->

<div class="row equal-height-row">
    <div class="col-lg-6">
        <!-- Basic Order Information -->
        <div class="card mb-4 h-100">
            <div class="card-header-cu">
                <h6 class="mb-0">Order Information</h6>
            </div>
            <div class="card-body d-flex flex-column">
                <div class="order-details flex-grow-1">
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
    </div>

    <div class="col-lg-6">
        <!-- Cost Details -->
        <div class="card mb-4 h-100">
            <div class="card-header-cu">
                <h6 class="mb-0">Cost Details</h6>
            </div>
            <div class="card-body d-flex flex-column">
                <div class="order-details flex-grow-1">
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
            </div>
        </div>
    </div>
</div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- Internal Comments -->
                                    <div class="card mb-4">
                                        <div class="card-header-cu">
                                            <h6 class="mb-0">Internal Comments</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="order-details comments-section">
                                                @if(!empty($order['internalComments']) || !empty($order['internalNotes']))
                                                        {{ $order['internalComments'] ?? $order['internalNotes'] ?? 'No comments available' }}
                                                @endif     
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
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
                                                    <span class="order-detail-value">{{ $destinations['collections']['address'] ?? '-' }}</span>
                                                </div>

                                                <!-- Address 2 -->
                                                <div class="order-detail-item">
                                                    <span class="order-detail-label">Address 2:</span>
                                                    <span class="order-detail-value">{{ $destinations['collections']['address2'] ?? '-' }}</span>
                                                </div>

                                                <!-- Postcode -->
                                                <div class="order-detail-item">
                                                    <span class="order-detail-label">Postcode:</span>
                                                    <span class="order-detail-value">{{ $destinations['collections']['postcode'] ?? '-' }}</span>
                                                </div>

                                                <!-- City -->
                                                <div class="order-detail-item">
                                                    <span class="order-detail-label">City:</span>
                                                    <span class="order-detail-value">{{ $destinations['collections']['city'] ?? '-' }}</span>
                                                </div>

                                                <!-- Country -->
                                                <div class="order-detail-item">
                                                    <span class="order-detail-label">Country:</span>
                                                    <span class="order-detail-value">{{ $destinations['collections']['country'] ?? '-' }}</span>
                                                </div>

                                                <!-- Phone -->
                                                <div class="order-detail-item">
                                                    <span class="order-detail-label">Phone:</span>
                                                    <span class="order-detail-value">{{ $destinations['collections']['phone'] ?? '-' }}</span>
                                                </div>

                                                <!-- Collection Date -->
                                                <div class="order-detail-item">
                                                    <span class="order-detail-label">Collection Date:</span>
                                                    <span class="order-detail-value">
                                                        {{ isset($destinations['collections']) ? \Carbon\Carbon::parse($destinations['collections']['date'])->format('d-m-Y') : '-' }}
                                                    </span>
                                                </div>

                                                <!-- Collection Time -->
                                                <div class="order-detail-item">
                                                    <span class="order-detail-label">Collection Time:</span>
                                                    <span class="order-detail-value">{{ $destinations['collections']['deliveryTime'] ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
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
                                                    <span class="order-detail-value">{{ $destinations['delivery']['address'] ?? '-' }}</span>
                                                </div>

                                                <!-- Address 2 -->
                                                <div class="order-detail-item">
                                                    <span class="order-detail-label">Address 2:</span>
                                                    <span class="order-detail-value">{{ $destinations['delivery']['address2'] ?? '-' }}</span>
                                                </div>

                                                <!-- Postcode -->
                                                <div class="order-detail-item">
                                                    <span class="order-detail-label">Postcode:</span>
                                                    <span class="order-detail-value">{{ $destinations['delivery']['postcode'] ?? '-' }}</span>
                                                </div>

                                                <!-- City -->
                                                <div class="order-detail-item">
                                                    <span class="order-detail-label">City:</span>
                                                    <span class="order-detail-value">{{ $destinations['delivery']['city'] ?? '-' }}</span>
                                                </div>

                                                <!-- Country -->
                                                <div class="order-detail-item">
                                                    <span class="order-detail-label">Country:</span>
                                                    <span class="order-detail-value">{{ $destinations['delivery']['country'] ?? '-' }}</span>
                                                </div>

                                                <!-- Phone -->
                                                <div class="order-detail-item">
                                                    <span class="order-detail-label">Phone:</span>
                                                    <span class="order-detail-value">{{ $destinations['delivery']['phone'] ?? '-' }}</span>
                                                </div>

                                                <!-- Delivery Date -->
                                                <div class="order-detail-item">
                                                    <span class="order-detail-label">Delivery Date:</span>
                                                    <span class="order-detail-value">
                                                        {{ isset($destinations['delivery']) ? \Carbon\Carbon::parse($destinations['delivery']['date'])->format('d-m-Y') : '-' }}
                                                    </span>
                                                </div>

                                                <!-- Delivery Time -->
                                                <div class="order-detail-item">
                                                    <span class="order-detail-label">Delivery Time:</span>
                                                    <span class="order-detail-value">{{ $destinations['delivery']['toTime'] ?? $destinations['delivery']['toTime'] ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Company Tab -->
                        @if(isset($customer) && count($customer) > 0)
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
                                                
                                                <a href="/admin/customers/{{$customer['customerNo']}}">
                                                    <span class="order-detail-value">
                                                        <strong>{{ $customer['companyName'] ?? '-' }}</strong>
                                                        @if(!empty($customer['customerNo']))
                                                            ({{ $customer['customerNo'] }})
                                                        @endif
                                                    </span>
                                                </a>
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
                                                <span class="customer-detail-label">Number of Previous Orders:</span>
                                                <span class="order-detail-value">
                                                    <strong>{{ $totalOrders ?? 0 }}</strong> orders
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
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
                                                <span class="order-detail-label">{{$order['customerNo']}} - Customer Not Found.</span>
                                                <span class="order-detail-value">
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
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
    // Add smooth scroll to top when switching tabs
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('[data-bs-toggle="pill"]');
        tabButtons.forEach(button => {
            button.addEventListener('shown.bs.tab', function (e) {
                // Smooth scroll to top of content
                document.querySelector('.main-content').scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });
    });
</script>
@endpush