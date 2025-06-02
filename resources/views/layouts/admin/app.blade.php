<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Title -->
    <title>@yield('title', 'Admin Dashboard')</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/admin/img/core-img/favicon.ico') }}" />

    <!-- Plugins File -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/admin/css/animate.css') }}" />
    <!-- <link rel="stylesheet" href="{{ asset('assets/admin/css/introjs.min.css') }}" /> -->

    <!-- Master Stylesheet [If you remove this CSS file, your file will be broken undoubtedly.] -->
    <link rel="stylesheet" href="{{ asset('assets/admin/style.css') }}" />
  </head>

  <body>
    <!-- Preloader -->
    <div id="preloader">
      <div class="preloader-book">
        <div class="inner">
          <div class="left"></div>
          <div class="middle"></div>
          <div class="right"></div>
        </div>
        <ul>
          <li></li>
          <li></li>
          <li></li>
          <li></li>
          <li></li>
          <li></li>
          <li></li>
          <li></li>
          <li></li>
          <li></li>
        </ul>
      </div>
    </div>
    <!-- /Preloader -->

    <!-- Choose Layout -->
    <!-- <div class="choose-layout-area">
      <div class="setting-trigger-icon" id="settingTrigger">
        <i class="ti-settings"></i>
      </div>
      <div class="choose-layout" id="chooseLayout">
        <div class="quick-setting-tab">
          <div class="widgets-todo-list-area">
            <h4 class="todo-title">Todo List:</h4>
            <form id="form-add-todo" class="form-add-todo">
              <input
                type="text"
                id="new-todo-item"
                class="new-todo-item form-control"
                name="todo"
                placeholder="Add New"
              />
              <input
                type="submit"
                id="add-todo-item"
                class="add-todo-item"
                value="+"
              />
            </form>

            <form id="form-todo-list">
              <ul id="flaptToDo-list" class="todo-list">
                <li>
                  <label class="ckbox"
                    ><input
                      type="checkbox"
                      name="todo-item-done"
                      class="todo-item-done"
                      value="test" /><span></span></label
                  >Go to Market
                  <i class="todo-item-delete ti-close"></i>
                </li>

                <li>
                  <label class="ckbox"
                    ><input
                      type="checkbox"
                      name="todo-item-done"
                      class="todo-item-done"
                      value="hello" /><span></span></label
                  >Meeting with AD
                  <i class="todo-item-delete ti-close"></i>
                </li>

                <li>
                  <label class="ckbox"
                    ><input
                      type="checkbox"
                      name="todo-item-done"
                      class="todo-item-done"
                      value="hello" /><span></span></label
                  >Check Mail
                  <i class="todo-item-delete ti-close"></i>
                </li>

                <li>
                  <label class="ckbox"
                    ><input
                      type="checkbox"
                      name="todo-item-done"
                      class="todo-item-done"
                      value="hello" /><span></span></label
                  >Work for Theme
                  <i class="todo-item-delete ti-close"></i>
                </li>

                <li>
                  <label class="ckbox"
                    ><input
                      type="checkbox"
                      name="todo-item-done"
                      class="todo-item-done"
                      value="hello" /><span></span></label
                  >Create a Plugin
                  <i class="todo-item-delete ti-close"></i>
                </li>

                <li>
                  <label class="ckbox"
                    ><input
                      type="checkbox"
                      name="todo-item-done"
                      class="todo-item-done"
                      value="hello" /><span></span></label
                  >Fixed Template Issues
                  <i class="todo-item-delete ti-close"></i>
                </li>
              </ul>
            </form>
          </div>
        </div>
      </div>
    </div> -->

    <!-- ======================================
    ******* Page Wrapper Area Start **********
    ======================================= -->
    <div class="flapt-page-wrapper">
      <!-- Sidemenu Area -->
      <div class="flapt-sidemenu-wrapper">
        <!-- Desktop Logo -->
        <div class="flapt-logo">
          <p class="side-logo"><a href="{{ route('login') }}">
            CRM Assistant
            <!-- 
              <img
              class="desktop-logo"
              src="{{ asset('assets/admin/img/core-img/logo.png' ) }}"
              alt="Desktop Logo" />
              <img
              class="small-logo"
              src="{{ asset('assets/admin/img/core-img/small-logo.png' ) }}"
              alt="Mobile Logo"/> 
            -->
          </a></p>
        </div>

      <!-- Side navigation  -->
        @include('layouts.admin.sidebar') 
      <!-- Side navigation  -->
      
    </div>
      <!-- Page Content -->
      <div class="flapt-page-content">
        <!-- Top Header Area -->
        <header
          class="top-header-area d-flex align-items-center justify-content-between"
        >
          <div class="left-side-content-area d-flex align-items-center">
            <!-- Mobile Logo -->
            <div class="mobile-logo">
              <a href="index.html"
                ><img src="{{ asset('assets/admin/img/core-img/small-logo.png' ) }}" alt="Mobile Logo"
              /></a>
            </div>

            <!-- Triggers -->
            <div class="flapt-triggers">
              <div class="menu-collasped" id="menuCollasped">
                <i class="bx bx-grid-alt"></i>
              </div>
              <div class="mobile-menu-open" id="mobileMenuOpen">
                <i class="bx bx-grid-alt"></i>
              </div>
            </div>

            <!-- Left Side Nav -->
            <ul class="left-side-navbar d-flex align-items-center">
              <li class="hide-phone app-search">
                <input
                  type="text"
                  class="form-control"
                  placeholder="Search..."
                />
                <span class="bx bx-search-alt"></span>
              </li>
            </ul>
          </div>

          <div
            class="right-side-navbar d-flex align-items-center justify-content-end"
          >
            <!-- Mobile Trigger -->
            <div class="right-side-trigger" id="rightSideTrigger">
              <i class="bx bx-menu-alt-right"></i>
            </div>

            <!-- Top Bar Nav -->
            <ul class="right-side-content d-flex align-items-center">
              <strong>{{ Auth::user()->name }}</strong>
              <li class="nav-item dropdown">
                <button
                  type="button"
                  class="btn dropdown-toggle"
                  data-bs-toggle="dropdown"
                  aria-haspopup="true"
                  aria-expanded="false"
                >
                  <img src="{{ asset('assets/admin/img/bg-img/person_1.jpg' ) }}" alt="" />
                </button>
                <div class="dropdown-menu profile dropdown-menu-right">

                  <div class="user-profile-area">
                    <a href="#" class="dropdown-item"
                      ><i class="bx bx-user font-15" aria-hidden="true"></i> My
                      profile</a
                    >
                    <!-- <a href="#" class="dropdown-item"
                      ><i class="bx bx-wrench font-15" aria-hidden="true"></i>
                      settings</a
                    > -->
                    <form method="POST" action="{{ route('logout') }}" id="logout-form">
                        @csrf
                        <a href="{{ route('logout') }}" 
                          onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                          class="dropdown-item text-danger">
                            <i class="bx bx-power-off me-2"></i> Logout
                        </a>
                    </form>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </header>

        @yield('content')

          <!-- Footer Area -->
          <div class="container-fluid">
            <div class="row">
              <div class="col-12">
                <!-- Footer Area -->
                <footer
                  class="footer-area d-sm-flex justify-content-center align-items-center justify-content-between"
                >
                  <!-- Copywrite Text -->
                  <div class="copywrite-text">
                    <p class="font-13">
                      Developed by &copy; <a href="#">Our Team</a>
                    </p>
                  </div>
                  <div class="fotter-icon text-center">
                    <p class="mb-0 font-13">2025 &copy; CRM Assistant</p>
                  </div>
                </footer>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ======================================
    ********* Page Wrapper Area End ***********
    ======================================= -->

    <!-- Must needed plugins to the run this Template -->
    <script src="{{ asset('assets/admin/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/default-assets/setting.js') }}"></script>
    <script src="{{ asset('assets/admin/js/default-assets/scrool-bar.js') }}"></script>
    <script src="{{ asset('assets/admin/js/todo-list.js') }}"></script>

    <!-- Active JS -->
    <script src="{{ asset('assets/admin/js/default-assets/active.js') }}"></script>

    <!-- These plugins only need for the run this page -->
    <script src="{{ asset('assets/admin/js/apexcharts.min.js') }}"></script>
    <!-- <script src="{{ asset('assets/admin/js/intro.min.js') }}"></script> -->
    <script src="{{ asset('assets/admin/js/dashboard-custom.js') }}"></script>
    <!-- <script src="{{ asset('assets/admin/js/intro-active.js') }}"></script> -->
  </body>
</html>
