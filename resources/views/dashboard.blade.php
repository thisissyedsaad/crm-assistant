@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<!-- Main Content Area -->
<div class="main-content introduction-farm">
  <div class="content-wraper-area">
    <div class="dashboard-area">
      <div class="container-fluid">
        <div class="row g-4">

          {{-- New Business Orders Today --}}
          <div class="col-sm-6 col-lg-6 col-xxl-3">
            <div class="card">
              <div class="card-body">
                <div class="single-widget d-flex align-items-center justify-content-between">
                  <div>
                    <div class="widget-icon">
                      <i class="bx bx-cart"></i>
                    </div>
                    <div class="widget-desc">
                      <h5>{{ $newOrdersToday }} New Orders</h5>
                      <p class="mb-0">New Business Today</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Existing Orders Today --}}
          <div class="col-sm-6 col-lg-6 col-xxl-3">
            <div class="card">
              <div class="card-body">
                <div class="single-widget d-flex align-items-center justify-content-between">
                  <div>
                    <div class="widget-icon">
                      <i class="bx bx-repeat"></i>
                    </div>
                    <div class="widget-desc">
                      <h5>{{ $existingOrdersToday }} Existing Orders</h5>
                      <p class="mb-0">Returning Customers Today</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- New Business Orders This Month --}}
          <div class="col-sm-6 col-lg-6 col-xxl-3">
            <div class="card">
              <div class="card-body">
                <div class="single-widget d-flex align-items-center justify-content-between">
                  <div>
                    <div class="widget-icon">
                      <i class="bx bx-calendar"></i>
                    </div>
                    <div class="widget-desc">
                      <h5>{{ $newOrdersMonth }} New Orders</h5>
                      <p class="mb-0">New Business This Month</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Existing Orders This Month --}}
          <div class="col-sm-6 col-lg-6 col-xxl-3">
            <div class="card">
              <div class="card-body">
                <div class="single-widget d-flex align-items-center justify-content-between">
                  <div>
                    <div class="widget-icon">
                      <i class="bx bx-repeat"></i>
                    </div>
                    <div class="widget-desc">
                      <h5>{{ $existingOrdersMonth }} Existing Orders</h5>
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
@endsection
