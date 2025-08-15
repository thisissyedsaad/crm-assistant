@extends('admin.layouts.app')

@section('title', 'Staff Training Module | CSD Assistant')

@push('links')
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
            cursor: pointer;
            height: 150px;
            display: flex;
            flex-direction: column;
            justify-content: center;
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
        
        /* Card Gradient Colors */
        .stats-card.adr {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .stats-card.cancellations {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .stats-card.complaints {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .stats-card.double-manned {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .stats-card.breakdown {
            background: linear-gradient(135deg, #c20068 0%, #f9e738 100%);
        }

        .stats-card.driver-late {
            background: linear-gradient(135deg, #ff9a56 0%, #ff6b6b 100%);
        }

        .stats-card.duplicate-profiles {
            background: linear-gradient(135deg, #a8e6cf 0%, #88d8a3 100%);
        }

        .stats-card.fragile-items {
            background: linear-gradient(135deg, #ffd93d 0%, #ff6b6b 100%);
        }

        .stats-card.international-delivery {
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
        }

        .stats-card.potential-fraud {
            background: linear-gradient(135deg, #fd79a8 0%, #e84393 100%);
        }

        .stats-card.upselling-handballing {
            background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%);
        }

        .stats-card.upselling-insurance {
            background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%);
        }

        .stats-card.upselling-waiting {
            background: linear-gradient(135deg, #fd63a8 0%, #fc3da8 100%);
        }
        
        .stats-card .card-icon {
            display: none; /* Hide icons */
        }
        
        /* Link styling for cards */
        .stats-card {
            text-decoration: none !important;
            color: white !important;
        }
        
        .stats-card:hover {
            text-decoration: none !important;
            color: white !important;
        }

        .stats-card .card-content * {
            color: inherit !important;
        }
        
        .stats-card .card-title {
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            opacity: 0.95;
        }
        
        .stats-card .card-value {
            display: none; /* Hide counts */
        }
        
        .stats-card .card-subtitle {
            font-size: 0.85rem;
            opacity: 0.8;
            font-weight: 400;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .stats-card {
                padding: 20px;
                margin-bottom: 15px;
                height: 130px;
            }
            
            .stats-card .card-value {
                font-size: 2rem;
            }
            
            .stats-card .card-icon {
                font-size: 2.5rem;
            }
        }

        /* Responsive grid layout */
        @media (min-width: 1400px) {
            .training-card {
                flex: 0 0 auto;
                width: calc(100% / 5); /* 5 cards per row on very large screens */
            }
        }

        @media (min-width: 1200px) and (max-width: 1399.98px) {
            .training-card {
                flex: 0 0 auto;
                width: calc(100% / 4); /* 4 cards per row on large screens */
            }
        }

        @media (min-width: 992px) and (max-width: 1199.98px) {
            .training-card {
                width: calc(100% / 3); /* 3 cards per row on medium screens */
            }
        }

        @media (min-width: 768px) and (max-width: 991.98px) {
            .training-card {
                width: 50%; /* 2 cards per row on tablets */
            }
        }

        @media (max-width: 767.98px) {
            .training-card {
                width: 100%; /* 1 card per row on mobile */
            }
        }

        /* ADR Modal specific styling */
        .adr-modal .modal-content {
            border: none;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .adr-modal .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 15px 15px 0 0;
            padding: 20px 30px;
        }

        .adr-modal .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .adr-modal .btn-close {
            filter: brightness(0) invert(1);
            opacity: 0.8;
        }

        .adr-modal .modal-body {
            padding: 30px;
            background-color: #f8f9fa;
        }

        .adr-resource-card {
            background: white;
            border: none;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .adr-resource-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            text-decoration: none;
            color: inherit;
        }

        .adr-resource-card .resource-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }

        .adr-resource-card .resource-icon {
            width: 24px;
            height: 24px;
            margin-right: 12px;
            color: #667eea;
        }

        .adr-resource-card .resource-desc {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 10px;
        }

        .adr-resource-card .resource-link {
            font-size: 0.8rem;
            color: #667eea;
            display: flex;
            align-items: center;
        }

        .adr-resource-card .resource-link i {
            margin-left: 5px;
            font-size: 0.7rem;
        }

        .page-title {
            text-align: center;
            margin-bottom: 40px;
            color: #343a40;
        }

        .page-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .page-title p {
            font-size: 1.1rem;
            color: #6c757d;
            margin: 0;
        }
    </style>
@endpush

@section('content')
<div class="main-content introduction-farm">
    <div class="content-wraper-area">
        <div class="data-table-area">
            <div class="container-fluid">
                <!-- Page Title -->
                <div class="page-title">
                    <h2>Staff Training Module</h2>
                    <p>Select a training category to begin learning</p>
                </div>

                <!-- Dashboard Cards Row -->
                <div class="row g-4 dashboard-cards justify-content-center">
                    <!-- ADR -->
                    <div class="col training-card">
                        <div class="card stats-card adr" data-training="adr" onclick="openADRModal()">
                            <div class="card-content">
                                <div class="card-title">ADR</div>
                                <div class="card-subtitle">Dangerous Goods Training</div>
                            </div>
                        </div>
                    </div>

                    <!-- Cancellations -->
                    <div class="col training-card">
                        <a href="#" class="card stats-card cancellations" data-training="cancellations">
                            <div class="card-content">
                                <div class="card-title">Cancellations</div>
                                <div class="card-subtitle">Order Cancellation Process</div>
                            </div>
                        </a>
                    </div>

                    <!-- Customer Complaints -->
                    <div class="col training-card">
                        <a href="#" class="card stats-card complaints" data-training="complaints">
                            <div class="card-content">
                                <div class="card-title">Customer Complaints</div>
                                <div class="card-subtitle">Complaint Handling Procedures</div>
                            </div>
                        </a>
                    </div>

                    <!-- Double Manned Jobs -->
                    <div class="col training-card">
                        <a href="#" class="card stats-card double-manned" data-training="double-manned">
                            <div class="card-content">
                                <div class="card-title">Double Manned Jobs</div>
                                <div class="card-subtitle">Two-Person Delivery Training</div>
                            </div>
                        </a>
                    </div>

                    <!-- Driver Breakdown -->
                    <div class="col training-card">
                        <a href="#" class="card stats-card breakdown" data-training="breakdown">
                            <div class="card-content">
                                <div class="card-title">Driver Breakdown</div>
                                <div class="card-subtitle">Emergency Response Protocol</div>
                            </div>
                        </a>
                    </div>

                    <!-- Driver Late -->
                    <div class="col training-card">
                        <a href="#" class="card stats-card driver-late" data-training="driver-late">
                            <div class="card-content">
                                <div class="card-title">Driver Late</div>
                                <div class="card-subtitle">Delay Management Training</div>
                            </div>
                        </a>
                    </div>

                    <!-- Duplicate Profiles -->
                    <div class="col training-card">
                        <a href="#" class="card stats-card duplicate-profiles" data-training="duplicate-profiles">
                            <div class="card-content">
                                <div class="card-title">Duplicate Profiles</div>
                                <div class="card-subtitle">Profile Management Training</div>
                            </div>
                        </a>
                    </div>

                    <!-- Fragile Item(s) -->
                    <div class="col training-card">
                        <a href="#" class="card stats-card fragile-items" data-training="fragile-items">
                            <div class="card-content">
                                <div class="card-title">Fragile Item(s)</div>
                                <div class="card-subtitle">Fragile Handling Procedures</div>
                            </div>
                        </a>
                    </div>

                    <!-- International Delivery -->
                    <div class="col training-card">
                        <a href="#" class="card stats-card international-delivery" data-training="international-delivery">
                            <div class="card-content">
                                <div class="card-title">International Delivery</div>
                                <div class="card-subtitle">Cross-Border Logistics</div>
                            </div>
                        </a>
                    </div>

                    <!-- Potential Fraud -->
                    <div class="col training-card">
                        <a href="#" class="card stats-card potential-fraud" data-training="potential-fraud">
                            <div class="card-content">
                                <div class="card-title">Potential Fraud</div>
                                <div class="card-subtitle">Fraud Detection Training</div>
                            </div>
                        </a>
                    </div>

                    <!-- Up-selling Handballing -->
                    <div class="col training-card">
                        <a href="#" class="card stats-card upselling-handballing" data-training="upselling-handballing">
                            <div class="card-content">
                                <div class="card-title">Up-selling Handballing</div>
                                <div class="card-subtitle">Additional Service Sales</div>
                            </div>
                        </a>
                    </div>

                    <!-- Up-selling Insurance -->
                    <div class="col training-card">
                        <a href="#" class="card stats-card upselling-insurance" data-training="upselling-insurance">
                            <div class="card-content">
                                <div class="card-title">Up-selling Insurance</div>
                                <div class="card-subtitle">Insurance Product Training</div>
                            </div>
                        </a>
                    </div>

                    <!-- Up-selling Waiting Time -->
                    <div class="col training-card">
                        <a href="#" class="card stats-card upselling-waiting" data-training="upselling-waiting">
                            <div class="card-content">
                                <div class="card-title">Up-selling Waiting Time</div>
                                <div class="card-subtitle">Wait Time Service Training</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ADR Modal -->
<div class="modal fade adr-modal" id="adrModal" tabindex="-1" aria-labelledby="adrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adrModalLabel">
                    <i class="bx bx-shield-alt-2 me-2"></i>ADR Training Resources
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <p class="text-muted mb-4">Access comprehensive ADR (Dangerous Goods) training materials and documentation. Click on any resource to open in a new tab.</p>
                    </div>
                </div>
                
                <div class="row">
                    <!-- ADR Handout -->
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1YW_Kk95MSgU6WIbEFvXOw1owVFK_Pd3JA6ln-1AcF0U/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">
                                <i class="bx bx-file-doc resource-icon"></i>
                                ADR Handout
                            </div>
                            <div class="resource-desc">
                                Essential ADR guidelines and quick reference material for dangerous goods handling.
                            </div>
                            <div class="resource-link">
                                Open Document <i class="bx bx-external-link"></i>
                            </div>
                        </a>
                    </div>

                    <!-- ADR Process Map -->
                    <div class="col-md-6 mb-3">
                        <a href="https://drive.google.com/file/d/18eJqVxFcqtKuumBrHYjdIi-4XykWF44D/view" target="_blank" class="adr-resource-card">
                            <div class="resource-title">
                                <i class="bx bx-map resource-icon"></i>
                                ADR Process Map
                            </div>
                            <div class="resource-desc">
                                Visual flowchart showing ADR compliance processes and decision points.
                            </div>
                            <div class="resource-link">
                                View Process Map <i class="bx bx-external-link"></i>
                            </div>
                        </a>
                    </div>

                    <!-- ADR Text -->
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1wXXZ03_fGvXWxhpN9Wdn6Kln0BJmsLgtadaMdgMMHSE/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">
                                <i class="bx bx-text resource-icon"></i>
                                ADR Text
                            </div>
                            <div class="resource-desc">
                                Comprehensive ADR regulations and detailed text documentation.
                            </div>
                            <div class="resource-link">
                                Read Documentation <i class="bx bx-external-link"></i>
                            </div>
                        </a>
                    </div>

                    <!-- ADR Volume 1 2025 -->
                    <div class="col-md-6 mb-3">
                        <a href="https://drive.google.com/file/d/1-skpzsxbP4Eu-EgabC9aTo8Vtd6Sj0hZ/view" target="_blank" class="adr-resource-card">
                            <div class="resource-title">
                                <i class="bx bx-book resource-icon"></i>
                                ADR Volume 1 2025
                            </div>
                            <div class="resource-desc">
                                Official ADR Volume 1 - General provisions and definitions for 2025.
                            </div>
                            <div class="resource-link">
                                Download Volume 1 <i class="bx bx-external-link"></i>
                            </div>
                        </a>
                    </div>

                    <!-- ADR Volume 2 2025 -->
                    <div class="col-md-6 mb-3">
                        <a href="https://drive.google.com/file/d/1ShV6Od8NKWAJu5jEyi02h2VzBBxQHv2X/view" target="_blank" class="adr-resource-card">
                            <div class="resource-title">
                                <i class="bx bx-book-open resource-icon"></i>
                                ADR Volume 2 2025
                            </div>
                            <div class="resource-desc">
                                Official ADR Volume 2 - Classification and special provisions for 2025.
                            </div>
                            <div class="resource-link">
                                Download Volume 2 <i class="bx bx-external-link"></i>
                            </div>
                        </a>
                    </div>

                    <!-- Blank DGN -->
                    <div class="col-md-6 mb-3">
                        <a href="https://drive.google.com/file/d/1OIxMAnTrTdk8OnOl2Xgh9trIRC6Sk9_T/view" target="_blank" class="adr-resource-card">
                            <div class="resource-title">
                                <i class="bx bx-file-blank resource-icon"></i>
                                Blank DGN
                            </div>
                            <div class="resource-desc">
                                Blank Dangerous Goods Note template for documentation purposes.
                            </div>
                            <div class="resource-link">
                                Download Template <i class="bx bx-external-link"></i>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        // Function to open ADR Modal
        function openADRModal() {
            $('#adrModal').modal('show');
        }

        // Initialize any additional functionality here  
        $(document).ready(function() {
            console.log('Staff Training Module loaded successfully');
        });
    </script>
@endpush