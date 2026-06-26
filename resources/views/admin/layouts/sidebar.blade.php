<!-- Side Nav -->
<div class="flapt-sidenav" id="flaptSideNav">
  <!-- Side Menu Area -->
  <div class="side-menu-area">
    <!-- Sidebar Menu -->
    <nav>
      <ul class="sidebar-menu" data-widget="tree">

        <li class="menu-header-title">Main</li>

        <!-- Dashboard - Role-based -->
        @if (auth()->user()->role === 'staff')
        <!-- Staff users see their personal dashboard -->
        <li class="{{ request()->routeIs('admin.staff-sales-dashboard.*') ? 'active' : '' }}">
          <a href="{{ route('admin.staff-sales-dashboard.index') }}" class="{{ request()->routeIs('admin.staff-sales-dashboard.*') ? 'active' : '' }}">
            <i class="bx bx-home-heart"></i> Dashboard
          </a>
        </li>
        @else
        <!-- Admin/Super-admin see Team Dashboard -->
        <li class="{{ request()->routeIs('admin.sales-dashboard.*') ? 'active' : '' }}">
          <a href="{{ route('admin.sales-dashboard.index') }}" class="{{ request()->routeIs('admin.sales-dashboard.*') ? 'active' : '' }}">
            <i class="bx bx-home-heart"></i> Dashboard
          </a>
        </li>
        @endif

        <li class="menu-header-title">CRM Modules</li>

        <li class="{{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
          <a href="{{ route('admin.customers.index') }}" class="{{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
            <i class="bx bx-group"></i> Companies Overview
          </a>
        </li>

        <li class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
          <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
            <i class="bx bxs-cart"></i> Orders Overview
          </a>
        </li>

        <!-- 4. Scheduler -->
        <li class="treeview {{ request()->routeIs('admin.schedular.*') ? 'menu-open active' : '' }}">
          <a href="javascript:void(0)" class="">
            <i class="bx bx-calendar"></i>
            <span>Scheduler</span>
            <i class="fa fa-angle-right"></i>
          </a>
          <ul class="treeview-menu" style="{{ request()->routeIs('admin.schedular.*') ? 'display: block;' : '' }}">
            <li class="{{ request()->routeIs('admin.schedular.current-jobs.index') ? 'active' : '' }}">
              <a href="{{ route('admin.schedular.current-jobs.index') }}" class="{{ request()->routeIs('admin.schedular.current-jobs.index') ? 'active' : '' }}">
                Current Jobs
              </a>
            </li>
          </ul>
        </li>

        <!-- 5. Training (renamed from Staff Training) -->
        <li class="{{ request()->routeIs('admin.trainings.*') ? 'active' : '' }}">
          <a href="{{ route('admin.trainings.index') }}" class="{{ request()->routeIs('admin.trainings.*') ? 'active' : '' }}">
            <i class="bx bx-chalkboard"></i> Training
          </a>
        </li>

        <!-- 6. Settings (Admin/Super-Admin only) -->
        @if (auth()->user()->role === 'admin' || auth()->user()->role === 'super-admin')
        <li class="treeview {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.targets.*') || request()->routeIs('admin.ip-whitelist.*') || request()->routeIs('admin.api-logs.*') ? 'menu-open active' : '' }}">
          <a href="javascript:void(0)" class="{{ request()->routeIs('admin.users.*') || request()->routeIs('admin.targets.*') || request()->routeIs('admin.ip-whitelist.*') || request()->routeIs('admin.api-logs.*') ? 'active' : '' }}">
            <i class="bx bx-cog"></i>
            <span>Settings</span>
            <i class="fa fa-angle-right"></i>
          </a>
          <ul class="treeview-menu" style="{{ request()->routeIs('admin.users.*') || request()->routeIs('admin.targets.*') || request()->routeIs('admin.ip-whitelist.*') || request()->routeIs('admin.api-logs.*') ? 'display: block;' : '' }}">
            <!-- Users -->
            <li class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
              <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="bx bx-user-circle"></i> Users
              </a>
            </li>
            <!-- Targets -->
            <li class="{{ request()->routeIs('admin.targets.*') ? 'active' : '' }}">
              <a href="{{ route('admin.targets.index') }}" class="{{ request()->routeIs('admin.targets.*') ? 'active' : '' }}">
                <i class="bx bx-target-lock"></i> Targets
              </a>
            </li>
            <!-- Whitelist IPs (Super-Admin only) -->
            @if (auth()->user()->role === 'super-admin')
            <li class="{{ request()->routeIs('admin.ip-whitelist.*') ? 'active' : '' }}">
              <a href="{{ route('admin.ip-whitelist.index') }}" class="{{ request()->routeIs('admin.ip-whitelist.*') ? 'active' : '' }}">
                <i class="bx bx-shield-alt-2"></i> Whitelist IPs
              </a>
            </li>
            <!-- API Monitor (Super-Admin only) -->
            <li class="{{ request()->routeIs('admin.api-logs.*') ? 'active' : '' }}">
              <a href="{{ route('admin.api-logs.index') }}" class="{{ request()->routeIs('admin.api-logs.*') ? 'active' : '' }}">
                <i class="bx bx-pulse"></i> API Monitor
              </a>
            </li>
            @endif
          </ul>
        </li>
        @endif

      </ul>
    </nav>

    <!-- Two-Factor Auth (Fixed at Bottom) -->
    <div class="sidebar-bottom-fixed">
      <a href="{{ route('2fa.show') }}" class="security-link {{ request()->routeIs('2fa.*') ? 'active' : '' }}">
        <i class="bx bx-shield"></i> Two-Factor Auth
        <div class="security-header">
          <!-- Security -->
          @if(auth()->user()->google2fa_enabled)
            <i class="bx bx-check-circle" style="color: #28a745; font-size: 15px; margin-left: 5px; vertical-align: middle;"></i>
          @else
            <i class="bx bx-x-circle" style="color: #dc3545; font-size: 15px; margin-left: 5px; vertical-align: middle;"></i>
          @endif
        </div>
      </a>
    </div>
  </div>
</div>

<style>
/* DISABLE SIDEBAR SCROLLING */
.flapt-sidenav,
.side-menu-area,
.side-menu-area > nav {
    overflow: hidden !important;
    overflow-y: hidden !important;
    overflow-x: hidden !important;
}

.side-menu-area {
    position: relative;
    height: 100%;
}

/* Enhanced Active States for Sidebar */
.sidebar-menu li.active > a,
.sidebar-menu li > a.active {
    background-color: #007bff !important;
    color: #ffffff !important;
    border-radius: 6px;
    margin: 2px 8px;
    font-weight: 500;
}

.sidebar-menu li.active > a i,
.sidebar-menu li > a.active i {
    color: #ffffff !important;
}

/* Treeview Active States */
.sidebar-menu li.treeview.active > a {
    background-color: #e3f2fd !important;
    color: #1976d2 !important;
    border-radius: 6px;
    margin: 2px 8px;
    font-weight: 500;
}

.sidebar-menu li.treeview.active > a i {
    color: #1976d2 !important;
}

/* Submenu Active States */
.sidebar-menu .treeview-menu li.active > a,
.sidebar-menu .treeview-menu li > a.active {
    background-color: #007bff !important;
    color: #ffffff !important;
    border-radius: 4px;
    margin: 1px 4px;
    padding-left: 20px !important;
    font-weight: 500;
}

/* Hover Effects */
.sidebar-menu li > a:hover {
    background-color: #f8f9fa !important;
    color: #007bff !important;
    border-radius: 6px;
    margin: 2px 8px;
    transition: all 0.3s ease;
}

.sidebar-menu li > a:hover i {
    color: #007bff !important;
}

/* Prevent hover on active items */
.sidebar-menu li.active > a:hover,
.sidebar-menu li > a.active:hover {
    background-color: #007bff !important;
    color: #ffffff !important;
}

.sidebar-menu li.active > a:hover i,
.sidebar-menu li > a.active:hover i {
    color: #ffffff !important;
}

/* Treeview menu open animation */
.sidebar-menu .treeview-menu {
    transition: all 0.3s ease;
}

/* Menu header styling */
.sidebar-menu .menu-header-title {
    color: #6c757d;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 15px 15px 10px 15px;
}

/* Better spacing */
.sidebar-menu li {
    margin-bottom: 2px;
    position: relative;
}

.sidebar-menu li > a {
    padding: 12px 15px;
    display: flex;
    align-items: center;
    text-decoration: none;
    transition: all 0.3s ease;
}

.sidebar-menu li > a i {
    margin-right: 10px;
    font-size: 18px;
    width: 20px;
    text-align: center;
}

/* Treeview arrow rotation */
.sidebar-menu .treeview > a .fa-angle-right {
    transition: transform 0.3s ease;
    margin-left: auto;
}

.sidebar-menu .treeview.menu-open > a .fa-angle-right {
    transform: rotate(90deg);
}

/* Active state indicators */
.sidebar-menu li.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 60%;
    background-color: #007bff;
    border-radius: 0 4px 4px 0;
}

/* Submenu icons styling */
.sidebar-menu .treeview-menu li > a i {
    font-size: 14px;
    margin-right: 8px;
}

/* ========================================
   FIXED BOTTOM 2FA SECTION
   ======================================== */
.sidebar-bottom-fixed {
    position: absolute;
    bottom: 70px;
    left: 0;
    right: 0;
    padding: 0 10px;
}


.sidebar-bottom-fixed .security-header {
    color: #6c757d;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0 5px 10px 5px;
}

.sidebar-bottom-fixed .security-link {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    text-decoration: none;
    color: inherit;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.sidebar-bottom-fixed .security-link i {
    margin-right: 10px;
    font-size: 18px;
    width: 20px;
    text-align: center;
}

.sidebar-bottom-fixed .security-link:hover {
    background-color: #f8f9fa !important;
    color: #007bff !important;
}

.sidebar-bottom-fixed .security-link.active {
    background-color: #007bff !important;
    color: #ffffff !important;
}

.sidebar-bottom-fixed .security-link.active i {
    color: #ffffff !important;
}
</style>
