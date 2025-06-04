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
                                        <h4>Customer Details</h4>
                                        <a href="{{ route('admin.customers.index') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-arrow-left me-1"></i>All Customers
                                        </a>
                                    </div>                                                
                                </div>
                                <div class="card-body">
                                    <div class="customer-details">
                                        <!-- Customer Number -->
                                        <div class="customer-detail-item">
                                            <span class="customer-detail-label">Customer Number:</span>
                                            <span class="customer-detail-value">
                                                <strong>{{ $customer['attributes']['customerNo'] ?? '-' }}</strong>
                                            </span>
                                        </div>

                                        <!-- Company Name -->
                                        <div class="customer-detail-item">
                                            <span class="customer-detail-label">Company Name:</span>
                                            <span class="customer-detail-value">{{ $customer['attributes']['companyName'] ?? '-' }}</span>
                                        </div>

                                        <!-- Business Address -->
                                        <div class="customer-detail-item">
                                            <span class="customer-detail-label">Business Address:</span>
                                            <span class="customer-detail-value">{{ $customer['attributes']['businessAddress']['address'] ?? '-' }}</span>
                                        </div>

                                        <!-- Industry -->
                                        <div class="customer-detail-item">
                                            <span class="customer-detail-label">Industry:</span>
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

                                        <!-- Created Date -->
                                        <div class="customer-detail-item">
                                            <span class="customer-detail-label">Customer Since:</span>
                                            <span class="customer-detail-value">
                                                {{ \Carbon\Carbon::parse($customer['createdAt'])->format('d-m-Y H:i') }}
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

@endpush