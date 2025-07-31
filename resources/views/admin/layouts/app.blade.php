<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Title -->
    <title>@yield('title', 'Admin Dashboard | CSD Assistant')</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/admin/img/core-img/favicon.ico') }}" />

    <!-- Plugins File -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/admin/css/animate.css') }}" />
    <!-- <link rel="stylesheet" href="{{ asset('assets/admin/css/introjs.min.css') }}" /> -->

    <!-- Master Stylesheet [If you remove this CSS file, your file will be broken undoubtedly.] -->
    <link rel="stylesheet" href="{{ asset('assets/admin/style.css') }}" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Mobile Navigation CSS -->
    <style>
      /* Mobile Navigation Styles - Only for Mobile */
      @media (max-width: 767px) {
        /* Hide mobile logo on mobile */
        .mobile-logo {
          display: none !important;
        }
        
        /* Mobile Menu Button */
        .mobile-nav-btn {
          display: block !important;
          position: fixed;
          top: 20px;
          left: 15px;
          z-index: 10001;
          background: transparent;
          color: white;
          border: none;
          border-radius: 6px;
          padding: 10px;
          cursor: pointer;
        }
        
        /* Hide Sidebar by default on mobile */
        .flapt-sidemenu-wrapper {
          position: fixed;
          left: -100%;
          top: 0;
          height: 100vh;
          width: 280px;
          z-index: 10000;
          transition: left 0.3s ease;
          background: #1a1d29;
          overflow-y: auto;
        }
        
        /* Show Sidebar when active */
        .flapt-sidemenu-wrapper.mobile-active {
          left: 0;
        }
        
        /* Close Button in Sidebar */
        .mobile-close-btn {
          display: block !important;
          position: absolute;
          top: 15px;
          right: 15px;
          background: transparent;
          color: white;
          border: none;
          border-radius: 50%;
          width: 35px;
          height: 35px;
          cursor: pointer;
          font-size: 18px;
          line-height: 1;
        }
        
        /* Overlay when menu is open */
        .mobile-overlay {
          display: none;
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background: rgba(0,0,0,0.5);
          z-index: 9999;
        }
        
        .mobile-overlay.active {
          display: block;
        }
        
        /* Remove margin for page content on mobile */
        .flapt-page-content {
          margin-left: 0 !important;
          width: 100% !important;
        }
      }
      
      /* Desktop - Hide mobile elements */
      @media (min-width: 768px) {
        .mobile-text-logo,
        .mobile-nav-btn,
        .mobile-close-btn,
        .mobile-overlay {
          display: none !important;
        }
      }
      p.mobile-text-logo {
          padding-top: 17px;
          color: #fff;
          font-size: 20px;
      }

      /* Enhanced Profile Dropdown Styles */
      .dropdown-menu.profile {
        min-width: 250px;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        border: 1px solid #e9ecef;
        overflow: hidden;
      }

      .dropdown-menu.profile .dropdown-item {
        transition: all 0.2s ease;
        border-radius: 6px;
        margin: 2px 10px;
      }

      .dropdown-menu.profile .dropdown-item:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
      }

      .dropdown-menu.profile .dropdown-item.text-danger:hover {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545 !important;
      }

      .user-info-section {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white !important;
      }

      .user-info-section h6,
      .user-info-section p {
        color: white !important;
      }

      /* Mobile responsive */
      @media (max-width: 767px) {
        .dropdown-menu.profile {
          min-width: 220px;
          right: 10px !important;
        }
      }
    </style>
    
    @stack('links')
  </head>

  <body>
    <!-- Mobile Menu Button (Only visible on mobile) -->
    <button class="mobile-nav-btn d-none" id="mobileMenuBtn">
      <i class="bx bx-menu-alt-left" style="font-size: 25px;"></i>
    </button>
    
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>

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

    <div class="flapt-page-wrapper">
      <!-- Sidemenu Area -->
      <div class="flapt-sidemenu-wrapper">
        <!-- Mobile Close Button (Only visible on mobile) -->
        <button class="mobile-close-btn d-none" id="mobileCloseBtn">
          <i class="bx bx-x"></i>
        </button>
        
        <!-- Desktop Logo -->
        <div class="flapt-logo">
          <p class="side-logo"><a href="{{ route('login') }}">CSD Assistant </a></p>

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
        </div>

      <!-- Side navigation  -->
        @include('admin.layouts.sidebar') 
      <!-- Side navigation  -->
      
    </div>
      <!-- Page Content -->
      <div class="flapt-page-content">
        <!-- Top Header Area -->
        <header
          class="top-header-area d-flex align-items-center justify-content-between"
        >
          <div class="left-side-content-area d-flex align-items-center">
            <div class="mobile-logo">
              <a href="index.html"
                ><img src="{{ asset('assets/admin/img/core-img/small-logo.png' ) }}" alt="Mobile Logo"
              /></a>
            </div>
          </div>

          <div class="d-flex align-items-center">
            <p class = "mobile-text-logo">CSD Assistant</p>
          </div>

          <div class="right-side-navbar d-flex align-items-center justify-content-end">
            <!-- Mobile Trigger -->
            <div class="right-side-trigger" id="rightSideTrigger">
              <i class="bx bx-menu-alt-right"></i>
            </div>

            <!-- Top Bar Nav -->
            <ul class="right-side-content d-flex align-items-center">
              <li class="nav-item dropdown">
                <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="bx bx-bell bx-tada"></i>
                  <span class="active-status"></span>
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                  <div class="top-notifications-area">
                    <div class="notifications-heading">
                      <div class="heading-title">
                        <h6>Notifications</h6>
                      </div>
                      <span>11</span>
                    </div>

                    <div class="notifications-box" id="notificationsBox">
                      <a href="#" class="dropdown-item">
                        <i class="bx bx-shopping-bag"></i>
                        <div>
                          <span>Your order is placed</span>
                          <p class="mb-0 font-12">
                            Consectetur adipisicing elit. Ipsa, porro!
                          </p>
                        </div>
                      </a>

                      <a href="#" class="dropdown-item">
                        <i class="bx bx-wallet-alt"></i>
                        <div>
                          <span>Haslina Obeta</span>
                          <p class="mb-0 font-12">
                            Consectetur adipisicing elit. Ipsa, porro!
                          </p>
                        </div>
                      </a>

                      <a href="#" class="dropdown-item">
                        <i class="bx bx-dollar-circle"></i>
                        <div>
                          <span>Your order is Dollar</span>
                          <p class="mb-0 font-12">
                            Consectetur adipisicing elit. Ipsa, porro!
                          </p>
                        </div>
                      </a>

                      <a href="#" class="dropdown-item">
                        <i class="bx bx-wallet-alt"></i>
                        <div>
                          <span>Haslina Obeta</span>
                          <p class="mb-0 font-12">
                            Consectetur adipisicing elit. Ipsa, porro!
                          </p>
                        </div>
                      </a>

                      <a href="#" class="dropdown-item">
                        <i class="bx bx-border-all"></i>
                        <div>
                          <span>Your order is placed</span>
                          <p class="mb-0 font-12">
                            Consectetur adipisicing elit. Ipsa, porro!
                          </p>
                        </div>
                      </a>
                      
                      <a href="#" class="dropdown-item">
                        <i class="bx bx-wallet-alt"></i>
                        <div>
                          <span>Haslina Obeta</span>
                          <p class="mb-0 font-12">
                            Consectetur adipisicing elit. Ipsa, porro!
                          </p>
                        </div>
                      </a>
                    </div>
                  </div>
                </div>
              </li>

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
                    <!-- User Info Section -->
                    <div class="user-info-section" style="padding: 15px 20px; border-bottom: 1px solid #eee; text-align: center;">
                      <div class="user-avatar" style="margin-bottom: 10px;">
                        <img src="{{ asset('assets/admin/img/bg-img/person_1.jpg' ) }}" 
                            alt="User Avatar" 
                            style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 3px solid #007bff;">
                      </div>
                      <h6 style="margin: 0; font-weight: 600; color: #333; font-size: 16px;">
                        {{ Auth::user()->name }}
                      </h6>
                      <p style="margin: 5px 0 0 0; color: #6c757d; font-size: 13px; text-transform: capitalize;">
                        {{ Auth::user()->role }}
                      </p>
                    </div>

                    <!-- Menu Items -->
                    <div class="dropdown-menu-items" style="padding: 10px 0;">
                      <!-- <a href="#" class="dropdown-item" style="padding: 8px 20px; display: flex; align-items: center;">
                        <i class="bx bx-user me-2" style="font-size: 16px; color: #6c757d;"></i> 
                        Profile
                      </a>
                      <a href="#" class="dropdown-item" style="padding: 8px 20px; display: flex; align-items: center;">
                        <i class="bx bx-cog me-2" style="font-size: 16px; color: #6c757d;"></i> 
                        Settings
                      </a> -->
                      <div class="dropdown-divider" style="margin: 5px 0;"></div>
                      <form method="POST" action="{{ route('logout') }}" id="logout-form">
                        @csrf
                        <a href="{{ route('logout') }}" 
                          onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                          class="dropdown-item text-danger"
                          style="padding: 8px 20px; display: flex; align-items: center;">
                          <i class="bx bx-power-off me-2" style="font-size: 16px;"></i> 
                          Logout
                        </a>
                      </form>
                    </div>
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
                      <!-- Developed by &copy; <a href="#">Our Team</a> -->
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

    <!-- Mobile Navigation JavaScript -->
    <script>
      $(document).ready(function() {
        // Open mobile menu
        $('#mobileMenuBtn').on('click', function() {
          $('.flapt-sidemenu-wrapper').addClass('mobile-active');
          $('#mobileOverlay').addClass('active');
          $('body').css('overflow', 'hidden'); // Prevent background scroll
        });
        
        // Close mobile menu - Close button
        $('#mobileCloseBtn').on('click', function() {
          $('.flapt-sidemenu-wrapper').removeClass('mobile-active');
          $('#mobileOverlay').removeClass('active');
          $('body').css('overflow', 'auto'); // Restore scroll
        });
        
        // Close mobile menu - Click overlay
        $('#mobileOverlay').on('click', function() {
          $('.flapt-sidemenu-wrapper').removeClass('mobile-active');
          $('#mobileOverlay').removeClass('active');
          $('body').css('overflow', 'auto'); // Restore scroll
        });
        
        // Handle sidebar menu clicks (mobile only)
        $(document).on('click', '.flapt-sidemenu-wrapper a', function(e) {
          if ($(window).width() <= 767) {
            const $this = $(this);
            
            // Check if this link has a dropdown (has arrow or contains dropdown classes)
            const hasDropdown = $this.find('i.ti-angle-right').length > 0 || 
                               $this.hasClass('has-arrow') ||
                               $this.next('ul').length > 0 ||
                               $this.parent('li').find('ul').length > 0;
            
            // If it's a dropdown toggle, don't close sidebar - let dropdown work
            if (hasDropdown) {
              // Don't close sidebar, let the dropdown toggle
              return;
            } else {
              // If it's a regular link or dropdown item, close sidebar
              $('.flapt-sidemenu-wrapper').removeClass('mobile-active');
              $('#mobileOverlay').removeClass('active');
              $('body').css('overflow', 'auto');
            }
          }
        });
        
        // Handle window resize
        $(window).on('resize', function() {
          if ($(window).width() > 767) {
            $('.flapt-sidemenu-wrapper').removeClass('mobile-active');
            $('#mobileOverlay').removeClass('active');
            $('body').css('overflow', 'auto');
          }
        });
      });
    </script>

    @stack('scripts')

  </body>
</html>