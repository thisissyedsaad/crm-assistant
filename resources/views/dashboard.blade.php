@extends('admin.layouts.app')

@section('title', 'Dashboard')

@push('links')
<style>
/* Simple dashboard card animations */
.dashboard-card {
    transition: all 0.3s ease;
    border: none;
    border-radius: 12px;
    animation: fadeInUp 0.5s ease forwards;
    opacity: 0;
    transform: translateY(20px);
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

/* Simple color themes */
.dashboard-card.new-orders-today {
    border-left: 4px solid #007bff;
}

.dashboard-card.existing-orders-today {
    border-left: 4px solid #28a745;
}

.dashboard-card.new-orders-month {
    border-left: 4px solid #17a2b8;
}

.dashboard-card.existing-orders-month {
    border-left: 4px solid #ffc107;
}

/* Widget styling */
.widget-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.new-orders-today .widget-icon {
    background: rgba(0, 123, 255, 0.1);
    color: #007bff;
}

.existing-orders-today .widget-icon {
    background: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

.new-orders-month .widget-icon {
    background: rgba(23, 162, 184, 0.1);
    color: #17a2b8;
}

.existing-orders-month .widget-icon {
    background: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

.dashboard-card:hover .widget-icon {
    transform: scale(1.05);
}

.widget-icon i {
    font-size: 1.5rem;
}

.widget-desc h5 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #495057;
}

.widget-desc p {
    color: #6c757d;
    font-size: 0.9rem;
    margin: 0;
}

/* Animation delays */
.dashboard-card:nth-child(1) { animation-delay: 0.1s; }
.dashboard-card:nth-child(2) { animation-delay: 0.2s; }
.dashboard-card:nth-child(3) { animation-delay: 0.3s; }
.dashboard-card:nth-child(4) { animation-delay: 0.4s; }

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard-card:hover {
        transform: translateY(-2px);
    }
}
</style>
@endpush

@section('content')
<!-- Main Content Area -->
<div class="main-content introduction-farm">
  <div class="content-wraper-area">
    <div class="dashboard-area">
      <div class="container-fluid">
        <div class="row g-4">

          {{-- New Business Orders Today --}}
          <div class="col-sm-6 col-lg-6 col-xxl-3">
            <div class="card dashboard-card new-orders-today">
              <div class="card-body">
                <div class="single-widget d-flex align-items-center justify-content-between">
                  <div>
                    <div class="widget-icon">
                      <i class="bx bx-cart"></i>
                    </div>
                    <div class="widget-desc">
                      <h5>{{ $newOrdersToday }}</h5>
                      <p class="mb-0">New Business Today</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Existing Orders Today --}}
          <div class="col-sm-6 col-lg-6 col-xxl-3">
            <div class="card dashboard-card existing-orders-today">
              <div class="card-body">
                <div class="single-widget d-flex align-items-center justify-content-between">
                  <div>
                    <div class="widget-icon">
                      <i class="bx bx-repeat"></i>
                    </div>
                    <div class="widget-desc">
                      <h5>{{ $existingOrdersToday }}</h5>
                      <p class="mb-0">Returning Customers Today</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- New Business Orders This Month --}}
          <div class="col-sm-6 col-lg-6 col-xxl-3">
            <div class="card dashboard-card new-orders-month">
              <div class="card-body">
                <div class="single-widget d-flex align-items-center justify-content-between">
                  <div>
                    <div class="widget-icon">
                      <i class="bx bx-calendar"></i>
                    </div>
                    <div class="widget-desc">
                      <h5>{{ $newOrdersMonth }}</h5>
                      <p class="mb-0">New Business This Month</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Existing Orders This Month --}}
          <div class="col-sm-6 col-lg-6 col-xxl-3">
            <div class="card dashboard-card existing-orders-month">
              <div class="card-body">
                <div class="single-widget d-flex align-items-center justify-content-between">
                  <div>
                    <div class="widget-icon">
                      <i class="bx bx-repeat"></i>
                    </div>
                    <div class="widget-desc">
                      <h5>{{ $existingOrdersMonth }}</h5>
                      <p class="mb-0">Returning This Month</p>
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