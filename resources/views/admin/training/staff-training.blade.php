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
        
        /* Card Gradient Colors - All Blue */
        .stats-card.adr,
        .stats-card.cancellations,
        .stats-card.complaints,
        .stats-card.double-manned,
        .stats-card.breakdown,
        .stats-card.driver-late,
        .stats-card.duplicate-profiles,
        .stats-card.fragile-items,
        .stats-card.international-delivery,
        .stats-card.potential-fraud,
        .stats-card.upselling-handballing,
        .stats-card.upselling-insurance,
        .stats-card.upselling-waiting {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
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
        
        .stats-card .card-content {
            position: relative;
            z-index: 2;
            height: 100%;
            width: 100%;
        }
        
        .stats-card .card-title {
            font-size: 0.9rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            margin-bottom: 0;
            opacity: 0.95;
            text-align: center;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: calc(100% - 20px);
            transition: all 0.3s ease;
            line-height: 1;
            word-break: break-word;
            overflow-wrap: break-word;
            white-space: normal;
            display: flex;
            align-items: center;
            justify-content: center;
            height: auto;
            max-height: 60px;
        }
        
        .stats-card:hover .card-title {
            top: 30%;
            transform: translate(-50%, -50%);
            font-size: 0.8rem;
            max-height: 40px;
        }
        
        .stats-card .card-value {
            display: none; /* Hide counts */
        }
        
        .stats-card .card-subtitle {
            font-size: 0.8rem;
            opacity: 0;
            font-weight: 400;
            transition: opacity 0.3s ease;
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
            text-align: center;
            line-height: 1.3;
        }
        
        .stats-card:hover .card-subtitle {
            opacity: 0.85;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .stats-card {
                padding: 15px;
                margin-bottom: 15px;
                height: 130px;
            }
            
            .stats-card .card-title {
                font-size: 0.7rem;
                letter-spacing: 0.1px;
                max-height: 50px;
            }
            
            .stats-card:hover .card-title {
                font-size: 0.65rem;
                max-height: 35px;
            }
            
            .stats-card .card-value {
                font-size: 2rem;
            }
            
            .stats-card .card-icon {
                font-size: 2.5rem;
            }
        }

        @media (min-width: 769px) and (max-width: 1199px) {
            .stats-card .card-title {
                font-size: 0.8rem;
                letter-spacing: 0.1px;
                max-height: 55px;
            }
            
            .stats-card:hover .card-title {
                font-size: 0.7rem;
                max-height: 38px;
            }
        }

        @media (min-width: 1200px) and (max-width: 1399px) {
            .stats-card .card-title {
                font-size: 0.85rem;
                max-height: 58px;
            }
            
            .stats-card:hover .card-title {
                font-size: 0.75rem;
                max-height: 38px;
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

        /* Modal specific styling */
        .adr-modal .modal-content {
            border: none;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .adr-modal .modal-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
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
            height: 120px;
            position: relative;
            overflow: hidden;
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
            margin-bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: calc(100% - 40px);
            text-align: center;
            transition: all 0.3s ease;
        }

        .adr-resource-card:hover .resource-title {
            top: 25%;
            transform: translate(-50%, -50%);
            font-size: 1rem;
        }

        .adr-resource-card .resource-icon {
            display: none;
        }

        .adr-resource-card .resource-desc {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 0;
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
            text-align: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            line-height: 1.3;
        }

        .adr-resource-card:hover .resource-desc {
            opacity: 0.8;
        }

        .adr-resource-card .resource-link {
            display: none;
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
                        <div class="card stats-card cancellations" data-training="cancellations" onclick="openCancellationsModal()">
                            <div class="card-content">
                                <div class="card-title">Cancellations</div>
                                <div class="card-subtitle">Order Cancellation Process</div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Complaints -->
                    <div class="col training-card">
                        <div class="card stats-card complaints" data-training="complaints" onclick="openComplaintsModal()">
                            <div class="card-content">
                                <div class="card-title">Customer Complaints</div>
                                <div class="card-subtitle">Complaint Handling Procedures</div>
                            </div>
                        </div>
                    </div>

                    <!-- Double Manned Jobs -->
                    <div class="col training-card">
                        <div class="card stats-card double-manned" data-training="double-manned" onclick="openDoubleMannedModal()">
                            <div class="card-content">
                                <div class="card-title">Double Manned Jobs</div>
                                <div class="card-subtitle">Two-Person Delivery Training</div>
                            </div>
                        </div>
                    </div>

                    <!-- Driver Breakdown -->
                    <div class="col training-card">
                        <div class="card stats-card breakdown" data-training="breakdown" onclick="openDriverBreakdownModal()">
                            <div class="card-content">
                                <div class="card-title">Driver Breakdown</div>
                                <div class="card-subtitle">Emergency Response Protocol</div>
                            </div>
                        </div>
                    </div>

                    <!-- Driver Late -->
                    <div class="col training-card">
                        <div class="card stats-card driver-late" data-training="driver-late" onclick="openDriverLateModal()">
                            <div class="card-content">
                                <div class="card-title">Driver Late</div>
                                <div class="card-subtitle">Delay Management Training</div>
                            </div>
                        </div>
                    </div>

                    <!-- Duplicate Profiles -->
                    <div class="col training-card">
                        <div class="card stats-card duplicate-profiles" data-training="duplicate-profiles" onclick="openDuplicateProfilesModal()">
                            <div class="card-content">
                                <div class="card-title">Duplicate Profiles</div>
                                <div class="card-subtitle">Profile Management Training</div>
                            </div>
                        </div>
                    </div>

                    <!-- Fragile Item(s) -->
                    <div class="col training-card">
                        <div class="card stats-card fragile-items" data-training="fragile-items" onclick="openFragileItemsModal()">
                            <div class="card-content">
                                <div class="card-title">Fragile Item(s)</div>
                                <div class="card-subtitle">Fragile Handling Procedures</div>
                            </div>
                        </div>
                    </div>

                    <!-- International Delivery -->
                    <div class="col training-card">
                        <div class="card stats-card international-delivery" data-training="international-delivery" onclick="openInternationalDeliveryModal()">
                            <div class="card-content">
                                <div class="card-title">International Delivery</div>
                                <div class="card-subtitle">Cross-Border Logistics</div>
                            </div>
                        </div>
                    </div>

                    <!-- Potential Fraud -->
                    <div class="col training-card">
                        <div class="card stats-card potential-fraud" data-training="potential-fraud" onclick="openPotentialFraudModal()">
                            <div class="card-content">
                                <div class="card-title">Potential Fraud</div>
                                <div class="card-subtitle">Fraud Detection Training</div>
                            </div>
                        </div>
                    </div>

                    <!-- Up-selling Handballing -->
                    <div class="col training-card">
                        <div class="card stats-card upselling-handballing" data-training="upselling-handballing" onclick="openHandballingModal()">
                            <div class="card-content">
                                <div class="card-title">Up-selling Handballing</div>
                                <div class="card-subtitle">Additional Service Sales</div>
                            </div>
                        </div>
                    </div>

                    <!-- Up-selling Insurance -->
                    <div class="col training-card">
                        <div class="card stats-card upselling-insurance" data-training="upselling-insurance" onclick="openInsuranceModal()">
                            <div class="card-content">
                                <div class="card-title">Up-selling Insurance</div>
                                <div class="card-subtitle">Insurance Product Training</div>
                            </div>
                        </div>
                    </div>

                    <!-- Up-selling Waiting Time -->
                    <div class="col training-card">
                        <div class="card stats-card upselling-waiting" data-training="upselling-waiting" onclick="openWaitingTimeModal()">
                            <div class="card-content">
                                <div class="card-title">Up-selling Waiting Time</div>
                                <div class="card-subtitle">Wait Time Service Training</div>
                            </div>
                        </div>
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
                                ADR Handout
                            </div>
                            <div class="resource-desc">
                                Essential ADR guidelines and quick reference material for dangerous goods handling.
                            </div>
                        </a>
                    </div>

                    <!-- ADR Process Map -->
                    <div class="col-md-6 mb-3">
                        <a href="https://drive.google.com/file/d/18eJqVxFcqtKuumBrHYjdIi-4XykWF44D/view" target="_blank" class="adr-resource-card">
                            <div class="resource-title">
                                ADR Process Map
                            </div>
                            <div class="resource-desc">
                                Visual flowchart showing ADR compliance processes and decision points.
                            </div>
                        </a>
                    </div>

                    <!-- ADR Text -->
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1wXXZ03_fGvXWxhpN9Wdn6Kln0BJmsLgtadaMdgMMHSE/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">
                                ADR Text
                            </div>
                            <div class="resource-desc">
                                Comprehensive ADR regulations and detailed text documentation.
                            </div>
                        </a>
                    </div>

                    <!-- ADR Volume 1 2025 -->
                    <div class="col-md-6 mb-3">
                        <a href="https://drive.google.com/file/d/1-skpzsxbP4Eu-EgabC9aTo8Vtd6Sj0hZ/view" target="_blank" class="adr-resource-card">
                            <div class="resource-title">
                                ADR Volume 1 2025
                            </div>
                            <div class="resource-desc">
                                Official ADR Volume 1 - General provisions and definitions for 2025.
                            </div>
                        </a>
                    </div>

                    <!-- ADR Volume 2 2025 -->
                    <div class="col-md-6 mb-3">
                        <a href="https://drive.google.com/file/d/1ShV6Od8NKWAJu5jEyi02h2VzBBxQHv2X/view" target="_blank" class="adr-resource-card">
                            <div class="resource-title">
                                ADR Volume 2 2025
                            </div>
                            <div class="resource-desc">
                                Official ADR Volume 2 - Classification and special provisions for 2025.
                            </div>
                        </a>
                    </div>

                    <!-- Blank DGN -->
                    <div class="col-md-6 mb-3">
                        <a href="https://drive.google.com/file/d/1OIxMAnTrTdk8OnOl2Xgh9trIRC6Sk9_T/view" target="_blank" class="adr-resource-card">
                            <div class="resource-title">
                                Blank DGN
                            </div>
                            <div class="resource-desc">
                                Blank Dangerous Goods Note template for documentation purposes.
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancellations Modal -->
<div class="modal fade adr-modal" id="cancellationsModal" tabindex="-1" aria-labelledby="cancellationsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancellationsModalLabel">
                    <i class="bx bx-x-circle me-2"></i>Cancellations Training Resources
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <p class="text-muted mb-4">Access comprehensive cancellation process training materials and documentation.</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1leJXXfGFSWCuAcfmez2eA_omept22YsdseUQnEyQ4sQ/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Cancellations and Amendments Handout</div>
                            <div class="resource-desc">Essential guidelines for handling cancellations and amendments.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://drive.google.com/file/d/1lTrEtbokB2j7parpqWcgKBQWonNx-QR8/view" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Cancellations Process Map</div>
                            <div class="resource-desc">Visual flowchart showing cancellation procedures.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1UF2G_EZLCYwUVWuN1A-jTyYQg6aHHqfrsy3lW3fH_lQ/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Customer Cancellation</div>
                            <div class="resource-desc">Guidelines for customer-initiated cancellations.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1x9Wsi3kAI1XkFQhVc1pZ91AS2dFzt14tXuK3zoeJ6FY/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Driver Breakdown</div>
                            <div class="resource-desc">Procedures for driver breakdown cancellations.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1z9LHbSp-D-5nS-hmks8E8ZZnNRxbUrd61j3f0kfGW3M/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Driver Error-Driver Cancellation</div>
                            <div class="resource-desc">Handling driver error related cancellations.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1TzlLRzoQabx76fpo1wXHMdIhB04J-CLOD_mOCPWWgfI/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Internal Cancellation</div>
                            <div class="resource-desc">Internal cancellation procedures and guidelines.</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Customer Complaints Modal -->
<div class="modal fade adr-modal" id="complaintsModal" tabindex="-1" aria-labelledby="complaintsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="complaintsModalLabel">
                    <i class="bx bx-message-square-error me-2"></i>Customer Complaints Training Resources
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <p class="text-muted mb-4">Learn effective customer complaint handling techniques and procedures.</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <a href="https://drive.google.com/file/d/1lLiliDStAd_2669Cj9ED5hZfHwsW2TPZ/view" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Complaint Process Map</div>
                            <div class="resource-desc">Visual guide for complaint resolution workflow.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1Uai-2gM-wjtshd2tpbEI1saPJrauqOiF/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Customer Complaints Handling - Text</div>
                            <div class="resource-desc">Comprehensive text on complaint handling procedures.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1npmjlpM8KLjvGuAEe61lOjeDLK3apZ2_/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Customer Complaints Handling Handout</div>
                            <div class="resource-desc">Quick reference guide for complaint resolution.</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Double Manned Jobs Modal -->
<div class="modal fade adr-modal" id="doubleMannedModal" tabindex="-1" aria-labelledby="doubleMannedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="doubleMannedModalLabel">
                    <i class="bx bx-group me-2"></i>Double Manned Jobs Training Resources
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <p class="text-muted mb-4">Training materials for two-person delivery operations and coordination.</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <a href="https://drive.google.com/file/d/1giOOuur7LCI8TplEjwBMdGA1eF_BHpgm/view" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Double Manned - Two man job process map</div>
                            <div class="resource-desc">Process map for coordinating two-person deliveries.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/18wADkRl6cfQFGk6WqjW_tP2D4qv3Z-hI/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Double-Manned Job Handout</div>
                            <div class="resource-desc">Essential guidelines for double-manned operations.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1xBNrVrMN3TLY0Jr0nuNRqpxZ0xkYZD49/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Double-Manned Job Text</div>
                            <div class="resource-desc">Detailed procedures for two-person delivery jobs.</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Driver Breakdown Modal -->
<div class="modal fade adr-modal" id="driverBreakdownModal" tabindex="-1" aria-labelledby="driverBreakdownModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="driverBreakdownModalLabel">
                    <i class="bx bx-wrench me-2"></i>Driver Breakdown Training Resources
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <p class="text-muted mb-4">Emergency response protocols for driver breakdown situations.</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <a href="https://drive.google.com/file/d/1D6oEJKxj2Gz9wW6N2ubYFNEzUwyI02kU/view" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Cancellations Process Map</div>
                            <div class="resource-desc">Process map for breakdown-related cancellations.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1Of68MY0y0yEQtuyPxl3OV8ChKR-MZBBX/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Driver Breakdown Handout</div>
                            <div class="resource-desc">Quick reference for driver breakdown procedures.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1pxMKVWtzRNCb6cTd6XgTG5p5uEwN3pd7/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Driver Breakdown Text</div>
                            <div class="resource-desc">Comprehensive breakdown response procedures.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1j3YMOfXpye_9CokYKzLJRHBdF0lw7UA_/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Driver Breakdown</div>
                            <div class="resource-desc">Additional breakdown handling documentation.</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Driver Late Modal -->
<div class="modal fade adr-modal" id="driverLateModal" tabindex="-1" aria-labelledby="driverLateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="driverLateModalLabel">
                    <i class="bx bx-time me-2"></i>Driver Late Training Resources
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <p class="text-muted mb-4">Managing and responding to driver lateness situations.</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <a href="https://drive.google.com/file/d/1wWdZ4xRrJMICrGnFS6BQE7ya3UTDpmbk/view" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Cancellations Process Map</div>
                            <div class="resource-desc">Process for handling late driver cancellations.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1ZKYXVSYUm40CC8ymvR15sUJVC42mADiL/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Driver Late Handout</div>
                            <div class="resource-desc">Guidelines for managing driver lateness.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1lYuuYa-8dRVA2gYULZc47f92ZhREE-fW/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Driver Late Text</div>
                            <div class="resource-desc">Comprehensive procedures for driver lateness.</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Duplicate Profiles Modal -->
<div class="modal fade adr-modal" id="duplicateProfilesModal" tabindex="-1" aria-labelledby="duplicateProfilesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="duplicateProfilesModalLabel">
                    <i class="bx bx-duplicate me-2"></i>Duplicate Profiles Training Resources
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <p class="text-muted mb-4">Managing and resolving duplicate customer profiles and accounts.</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <a href="https://drive.google.com/file/d/1dDGGEsdDbIrZQoSU9uTSavxwG1GlOozb/view" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Duplicate Account Process Map</div>
                            <div class="resource-desc">Process for identifying and merging duplicate accounts.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1eDUygUD0MmKFGPWimqe0EhqEiWf_pwyW/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Duplicate Profile Handout</div>
                            <div class="resource-desc">Quick guide for handling duplicate profiles.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1KcmUX8_Y6G2T3GDDjQXjr6_1ldBVHaKo/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Duplicate Profile Text</div>
                            <div class="resource-desc">Detailed procedures for profile management.</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fragile Items Modal -->
<div class="modal fade adr-modal" id="fragileItemsModal" tabindex="-1" aria-labelledby="fragileItemsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fragileItemsModalLabel">
                    <i class="bx bx-package me-2"></i>Fragile Items Training Resources
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <p class="text-muted mb-4">Proper handling and care procedures for fragile items and packages.</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1_mEIdru466g_-pr1Y_gIYZPaiFOYAW3H/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Fragile Item(s)</div>
                            <div class="resource-desc">Guidelines for handling fragile items safely.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://drive.google.com/file/d/1zFOSSSTcKCJ8xdMrApgEgND2nJs9RcOt/view" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Fragile Items Process Map</div>
                            <div class="resource-desc">Process map for fragile item handling procedures.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1DQoxUs1teBO6eV1b90mG79zRlxjh8Loq/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Fragile Item(s) Text</div>
                            <div class="resource-desc">Comprehensive fragile item handling procedures.</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- International Delivery Modal -->
<div class="modal fade adr-modal" id="internationalDeliveryModal" tabindex="-1" aria-labelledby="internationalDeliveryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="internationalDeliveryModalLabel">
                    <i class="bx bx-world me-2"></i>International Delivery Training Resources
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <p class="text-muted mb-4">Cross-border logistics and international delivery procedures.</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1L_GUkE7o9xnBj9n6uvNDP_9JWJa5SoRu/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">International Collection or Delivery Text</div>
                            <div class="resource-desc">Comprehensive guide for international operations.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://drive.google.com/file/d/1eXxrtm1wHcQgDOqaDF4PbJk8LuJ6gqbk/view" target="_blank" class="adr-resource-card">
                            <div class="resource-title">International Delivery Process Map</div>
                            <div class="resource-desc">Visual guide for international delivery processes.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1zHoAlcKl5a8lY-svmyxuf5GF3JVCMCyH/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">International Handout</div>
                            <div class="resource-desc">Quick reference for international deliveries.</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Potential Fraud Modal -->
<div class="modal fade adr-modal" id="potentialFraudModal" tabindex="-1" aria-labelledby="potentialFraudModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="potentialFraudModalLabel">
                    <i class="bx bx-shield-x me-2"></i>Potential Fraud Training Resources
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <p class="text-muted mb-4">Fraud detection and prevention training materials.</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1cd6U3zAltKjvnHUETrnIEH2TaZ4J13qO/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Potential Fraud Handout</div>
                            <div class="resource-desc">Guidelines for identifying potential fraud.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://drive.google.com/file/d/1nlIzb6ksA7xnOuMYFHp85sN2iyppUV-r/view" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Potential Fraud Process Map</div>
                            <div class="resource-desc">Process for handling suspected fraud cases.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1s340qTL50zxXx6QJbgbszFyseIMCMxfI/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Potential Fraud Text</div>
                            <div class="resource-desc">Detailed fraud prevention procedures.</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Handballing Modal -->
<div class="modal fade adr-modal" id="handballingModal" tabindex="-1" aria-labelledby="handballingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="handballingModalLabel">
                    <i class="bx bx-trending-up me-2"></i>Up-selling Handballing Training Resources
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <p class="text-muted mb-4">Training for up-selling handballing services to customers.</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/128yZTL_5GISKuZRnbjcIbxheOB3CjYP-/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Handballing Text</div>
                            <div class="resource-desc">Comprehensive handballing service information.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1oGyOMnZTQvP9rvv-HvPp1I3zhrBwPeCU/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Handballing Handout</div>
                            <div class="resource-desc">Quick reference for handballing services.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://drive.google.com/file/d/1sbRyAI-Gk1SX1Bt0d9u19p9aZRGvl3-9/view" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Handballing Process Map</div>
                            <div class="resource-desc">Process map for handballing service workflow.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/18J_amU-2KhnEUXWypyPrK_jg6ekbZgnJ/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Handballing Text</div>
                            <div class="resource-desc">Additional handballing documentation.</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Insurance Modal -->
<div class="modal fade adr-modal" id="insuranceModal" tabindex="-1" aria-labelledby="insuranceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="insuranceModalLabel">
                    <i class="bx bx-shield me-2"></i>Up-selling Insurance Training Resources
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <p class="text-muted mb-4">Insurance product training and up-selling techniques.</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <a href="https://drive.google.com/file/d/1OHOUNQyrKYuzGdy-peuoQ73kdo5DpFBn/view" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Insurance Handout</div>
                            <div class="resource-desc">Insurance product information and guidelines.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1x1fKpCaK5JnGMI5QiCAW7uyXVpVFq68Y/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">CSD INSURANCE RATES v2.0</div>
                            <div class="resource-desc">Current insurance rates and pricing structure.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1RuJTfmgiC25X7htg1_XmKNe72NSXmyBj/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Insurance Handout</div>
                            <div class="resource-desc">Additional insurance product documentation.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://drive.google.com/file/d/1TkIj3tYk7OXfXroi73PxcOqMDbPDVvij/view" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Insurance Process Map</div>
                            <div class="resource-desc">Process map for insurance sales workflow.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1-QeTiuxoyBdsTWtsowvkgu0-oZtxUD0A/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Insurance Text</div>
                            <div class="resource-desc">Comprehensive insurance product information.</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Waiting Time Modal -->
<div class="modal fade adr-modal" id="waitingTimeModal" tabindex="-1" aria-labelledby="waitingTimeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="waitingTimeModalLabel">
                    <i class="bx bx-time-five me-2"></i>Up-selling Waiting Time Training Resources
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <p class="text-muted mb-4">Training for up-selling waiting time services to customers.</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1Cyy1k2W665VkFU8bmWXMq3KpW2F4FQJ8/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Waiting - Time Handout</div>
                            <div class="resource-desc">Guidelines for waiting time service offerings.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://drive.google.com/file/d/1Dy-s015wZcz4yigJqv5Ys_xcSzZMIxAE/view" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Waiting Time Process Map</div>
                            <div class="resource-desc">Process map for waiting time service workflow.</div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="https://docs.google.com/document/d/1pP3wTL2obQELW6J4hRSXGSLK-TRKPpof/edit?tab=t.0" target="_blank" class="adr-resource-card">
                            <div class="resource-title">Waiting-Time Text</div>
                            <div class="resource-desc">Comprehensive waiting time service procedures.</div>
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

        // Function to open Cancellations Modal
        function openCancellationsModal() {
            $('#cancellationsModal').modal('show');
        }

        // Function to open Complaints Modal
        function openComplaintsModal() {
            $('#complaintsModal').modal('show');
        }

        // Function to open Double Manned Modal
        function openDoubleMannedModal() {
            $('#doubleMannedModal').modal('show');
        }

        // Function to open Driver Breakdown Modal
        function openDriverBreakdownModal() {
            $('#driverBreakdownModal').modal('show');
        }

        // Function to open Driver Late Modal
        function openDriverLateModal() {
            $('#driverLateModal').modal('show');
        }

        // Function to open Duplicate Profiles Modal
        function openDuplicateProfilesModal() {
            $('#duplicateProfilesModal').modal('show');
        }

        // Function to open Fragile Items Modal
        function openFragileItemsModal() {
            $('#fragileItemsModal').modal('show');
        }

        // Function to open International Delivery Modal
        function openInternationalDeliveryModal() {
            $('#internationalDeliveryModal').modal('show');
        }

        // Function to open Potential Fraud Modal
        function openPotentialFraudModal() {
            $('#potentialFraudModal').modal('show');
        }

        // Function to open Handballing Modal
        function openHandballingModal() {
            $('#handballingModal').modal('show');
        }

        // Function to open Insurance Modal
        function openInsuranceModal() {
            $('#insuranceModal').modal('show');
        }

        // Function to open Waiting Time Modal
        function openWaitingTimeModal() {
            $('#waitingTimeModal').modal('show');
        }

        // Initialize any additional functionality here  
        $(document).ready(function() {
            console.log('Staff Training Module loaded successfully');
        });
    </script>
@endpush