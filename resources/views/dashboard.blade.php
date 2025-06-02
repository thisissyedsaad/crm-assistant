@extends('layouts.admin.app')

@section('title', 'Dashboard')

@section('content')

        <!-- Main Content Area -->
        <div class="main-content introduction-farm">
          <div class="content-wraper-area">
            <div class="dashboard-area">
              <div class="container-fluid">
                <div class="row g-4">
                  <div class="col-12">
                    <div
                      class="d-flex align-items-center justify-content-between"
                    >
                      <!-- <div class="dashboard-header-title">
                        <h5 class="mb-0">Congratulations</h5>
                        <p class="mb-0">
                          You have earns
                          <span class="text-success">$785</span> today.
                        </p>
                      </div> -->

                      <!-- <div class="dashboard-infor-mation">
                        <div
                          class="dashboard-btn-group d-flex align-items-center"
                        >
                          <a href="#" class="dash-btn ms-2"
                            ><i class="ti-settings"></i
                          ></a>
                          <a href="#" class="dash-btn ms-2"
                            ><i class="ti-plus"></i
                          ></a>
                        </div>
                      </div> -->
                    </div>
                  </div>

                  <div class="col-sm-6 col-lg-6 col-xxl-3">
                    <div class="card">
                      <div class="card-body" data-intro="New Orders">
                        <div
                          class="single-widget d-flex align-items-center justify-content-between"
                        >
                          <div>
                            <div class="widget-icon">
                              <i class="bx bx-mouse-alt"></i>
                            </div>
                            <div class="widget-desc">
                              <h5>35 new orders</h5>
                              <p class="mb-0">Awating Processing</p>
                            </div>
                          </div>
                          <div
                            class="progress-report"
                            data-title="progress"
                            data-intro="And this is the last step!"
                          >
                            <p>+ 3.56%</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-sm-6 col-lg-6 col-xxl-3">
                    <div class="card">
                      <div class="card-body" data-intro="New Customers">
                        <div
                          class="single-widget d-flex align-items-center justify-content-between"
                        >
                          <div>
                            <div class="widget-icon">
                              <i class="bx bx-user-voice"></i>
                            </div>
                            <div class="widget-desc">
                              <h5>Customers</h5>
                              <p class="mb-0">Awating Processing</p>
                            </div>
                          </div>
                          <div class="progress-report" data-title="progress">
                            <p>+ 4.56%</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-sm-6 col-lg-6 col-xxl-3">
                    <div class="card">
                      <div class="card-body" data-intro="Revenue">
                        <div
                          class="single-widget d-flex align-items-center justify-content-between"
                        >
                          <div>
                            <div class="widget-icon">
                              <i class="bx bx-wallet"></i>
                            </div>
                            <div class="widget-desc">
                              <h5>Revenue</h5>
                              <p class="mb-0">Awating Processing</p>
                            </div>
                          </div>
                          <div
                            class="progress-report"
                            data-title="progress"
                            data-intro="And this is the last step!"
                          >
                            <p>- 2.56%</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-sm-6 col-lg-6 col-xxl-3">
                    <div class="card">
                      <div class="card-body" data-intro="Growth">
                        <div
                          class="single-widget d-flex align-items-center justify-content-between"
                        >
                          <div>
                            <div class="widget-icon">
                              <i class="bx bx-bar-chart-alt-2"></i>
                            </div>
                            <div class="widget-desc">
                              <h5>Growth</h5>
                              <p class="mb-0">Awating Processing</p>
                            </div>
                          </div>
                          <div
                            class="progress-report"
                            data-title="progress"
                            data-intro="And this is the last step!"
                          >
                            <p>+ 2.56%</p>
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