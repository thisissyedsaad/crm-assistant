@extends('admin.layouts.app')

@section('title', 'Admin Dashboard | CSD Assistant')

@push('links')
<style>
/* Dashboard card animations */
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

/* Row heading styles */
.row-heading {
    text-align: center;
    margin: 2.5rem 0 1.5rem 0;
    animation: fadeIn 0.6s ease forwards;
}

.row-heading h3 {
    color: #495057;
    font-weight: 600;
    font-size: 1.5rem;
    margin: 0;
    position: relative;
    display: inline-block;
}

.row-heading h3:before {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 3px;
    background: #007bff;
    border-radius: 2px;
}

/* Card color themes - Simple border left style like original */
/* THIS WEEK - Blue themes */
.new-business-week {
    border-left: 4px solid #007bff;
}

.existing-business-week {
    border-left: 4px solid #28a745;
}

.quotes-week {
    border-left: 4px solid #17a2b8;
}

.jobs-week {
    border-left: 4px solid #ffc107;
}

/* LAST WEEK - Different colors */
.new-business-last-week {
    border-left: 4px solid #6f42c1;
}

.existing-business-last-week {
    border-left: 4px solid #e83e8c;
}

.quotes-last-week {
    border-left: 4px solid #fd7e14;
}

.jobs-last-week {
    border-left: 4px solid #20c997;
}

/* THIS MONTH - More colors */
.new-business-month {
    border-left: 4px solid #dc3545;
}

.existing-business-month {
    border-left: 4px solid #6610f2;
}

.quotes-month {
    border-left: 4px solid #198754;
}

.jobs-month {
    border-left: 4px solid #0dcaf0;
}

/* Widget styling - Original style */
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

/* Icon colors based on border colors */
.new-business-week .widget-icon,
.new-business-last-week .widget-icon,
.new-business-month .widget-icon {
    background: rgba(0, 123, 255, 0.1);
    color: #007bff;
}

.existing-business-week .widget-icon {
    background: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

.existing-business-last-week .widget-icon {
    background: rgba(232, 62, 140, 0.1);
    color: #e83e8c;
}

.existing-business-month .widget-icon {
    background: rgba(102, 16, 242, 0.1);
    color: #6610f2;
}

.quotes-week .widget-icon {
    background: rgba(23, 162, 184, 0.1);
    color: #17a2b8;
}

.quotes-last-week .widget-icon {
    background: rgba(253, 126, 20, 0.1);
    color: #fd7e14;
}

.quotes-month .widget-icon {
    background: rgba(25, 135, 84, 0.1);
    color: #198754;
}

.jobs-week .widget-icon {
    background: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

.jobs-last-week .widget-icon {
    background: rgba(32, 201, 151, 0.1);
    color: #20c997;
}

.jobs-month .widget-icon {
    background: rgba(13, 202, 240, 0.1);
    color: #0dcaf0;
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

@keyframes fadeIn {
    to {
        opacity: 1;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .row-heading h3 {
        font-size: 1.3rem;
    }
    
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

        {{-- THIS WEEK ROW --}}
        <div class="row-heading">
          <h3>This Week</h3>
        </div>
        <div class="row g-4 mb-4">
          {{-- New Business This Week --}}
          <div class="col-sm-6 col-lg-3">
            <div class="card dashboard-card new-business-week">
              <div class="card-body">
                <div class="single-widget">
                  <div class="widget-icon">
                    <i class="bx bx-trending-up"></i>
                  </div>
                  <div class="widget-desc">
                    <h5>47</h5>
                    <p class="mb-0">New Business</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Existing Business This Week --}}
          <div class="col-sm-6 col-lg-3">
            <div class="card dashboard-card existing-business-week">
              <div class="card-body">
                <div class="single-widget">
                  <div class="widget-icon">
                    <i class="bx bx-repeat"></i>
                  </div>
                  <div class="widget-desc">
                    <h5>132</h5>
                    <p class="mb-0">Existing Business</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Quotes This Week --}}
          <div class="col-sm-6 col-lg-3">
            <div class="card dashboard-card quotes-week">
              <div class="card-body">
                <div class="single-widget">
                  <div class="widget-icon">
                    <i class="bx bx-file-blank"></i>
                  </div>
                  <div class="widget-desc">
                    <h5>89</h5>
                    <p class="mb-0"># of Quotes</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Jobs This Week --}}
          <div class="col-sm-6 col-lg-3">
            <div class="card dashboard-card jobs-week">
              <div class="card-body">
                <div class="single-widget">
                  <div class="widget-icon">
                    <i class="bx bx-briefcase"></i>
                  </div>
                  <div class="widget-desc">
                    <h5>156</h5>
                    <p class="mb-0"># of Jobs</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- LAST WEEK ROW --}}
        <div class="row-heading">
          <h3>Last Week</h3>
        </div>
        <div class="row g-4 mb-4">
          {{-- New Business Last Week --}}
          <div class="col-sm-6 col-lg-3">
            <div class="card dashboard-card new-business-last-week">
              <div class="card-body">
                <div class="single-widget">
                  <div class="widget-icon">
                    <i class="bx bx-trending-up"></i>
                  </div>
                  <div class="widget-desc">
                    <h5>38</h5>
                    <p class="mb-0">New Business</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Existing Business Last Week --}}
          <div class="col-sm-6 col-lg-3">
            <div class="card dashboard-card existing-business-last-week">
              <div class="card-body">
                <div class="single-widget">
                  <div class="widget-icon">
                    <i class="bx bx-repeat"></i>
                  </div>
                  <div class="widget-desc">
                    <h5>94</h5>
                    <p class="mb-0">Existing Business</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Quotes Last Week --}}
          <div class="col-sm-6 col-lg-3">
            <div class="card dashboard-card quotes-last-week">
              <div class="card-body">
                <div class="single-widget">
                  <div class="widget-icon">
                    <i class="bx bx-file-blank"></i>
                  </div>
                  <div class="widget-desc">
                    <h5>67</h5>
                    <p class="mb-0"># of Quotes</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Jobs Last Week --}}
          <div class="col-sm-6 col-lg-3">
            <div class="card dashboard-card jobs-last-week">
              <div class="card-body">
                <div class="single-widget">
                  <div class="widget-icon">
                    <i class="bx bx-briefcase"></i>
                  </div>
                  <div class="widget-desc">
                    <h5>121</h5>
                    <p class="mb-0"># of Jobs</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- THIS MONTH ROW --}}
        <div class="row-heading">
          <h3>This Month</h3>
        </div>
        <div class="row g-4 mb-4">
          {{-- New Business This Month --}}
          <div class="col-sm-6 col-lg-3">
            <div class="card dashboard-card new-business-month">
              <div class="card-body">
                <div class="single-widget">
                  <div class="widget-icon">
                    <i class="bx bx-trending-up"></i>
                  </div>
                  <div class="widget-desc">
                    <h5>203</h5>
                    <p class="mb-0">New Business</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Existing Business This Month --}}
          <div class="col-sm-6 col-lg-3">
            <div class="card dashboard-card existing-business-month">
              <div class="card-body">
                <div class="single-widget">
                  <div class="widget-icon">
                    <i class="bx bx-repeat"></i>
                  </div>
                  <div class="widget-desc">
                    <h5>567</h5>
                    <p class="mb-0">Existing Business</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Quotes This Month --}}
          <div class="col-sm-6 col-lg-3">
            <div class="card dashboard-card quotes-month">
              <div class="card-body">
                <div class="single-widget">
                  <div class="widget-icon">
                    <i class="bx bx-file-blank"></i>
                  </div>
                  <div class="widget-desc">
                    <h5>342</h5>
                    <p class="mb-0"># of Quotes</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Jobs This Month --}}
          <div class="col-sm-6 col-lg-3">
            <div class="card dashboard-card jobs-month">
              <div class="card-body">
                <div class="single-widget">
                  <div class="widget-icon">
                    <i class="bx bx-briefcase"></i>
                  </div>
                  <div class="widget-desc">
                    <h5>689</h5>
                    <p class="mb-0"># of Jobs</p>
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