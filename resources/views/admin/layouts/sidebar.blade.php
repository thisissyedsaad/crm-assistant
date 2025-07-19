<!-- Side Nav -->
<div class="flapt-sidenav" id="flaptSideNav">
  <!-- Side Menu Area -->
  <div class="side-menu-area">
    <!-- Sidebar Menu -->
    <nav>
      <ul class="sidebar-menu" data-widget="tree">
        <li class="menu-header-title">Main</li>

        <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
          <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bx bx-home-heart"></i> Dashboard 
          </a>
        </li>

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
        
        @if (auth()->user()->role === 'admin')
        <li class="treeview {{ request()->routeIs('admin.users.*') ? 'menu-open active' : '' }}">
          <a href="javascript:void(0)" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="bx bx-user-circle"></i>
            <span>User Management</span>
            <i class="fa fa-angle-right"></i>
          </a>
          <ul class="treeview-menu" style="{{ request()->routeIs('admin.users.*') ? 'display: block;' : '' }}">
            <li class="{{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
              <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                All Users
              </a>
            </li>
            <li class="{{ request()->routeIs('admin.users.create') ? 'active' : '' }}">
              <a href="{{ route('admin.users.create') }}" class="{{ request()->routeIs('admin.users.create') ? 'active' : '' }}">
                Add New User
              </a>
            </li>
          </ul>
        </li>

        <li class="treeview {{ request()->routeIs('admin.schedular.*') ? 'menu-open active' : '' }}">
          <a href="javascript:void(0)" class="">
            <i class="bx bx-user-circle"></i>
            <span>Schedular</span>
            <i class="fa fa-angle-right"></i>
          </a>
          <ul class="treeview-menu" style="">
            <li class="{{ request()->routeIs('admin.schedular.current-jobs.index') ? 'active' : '' }}">
              <a href="{{ route('admin.schedular.current-jobs.index') }}" class="{{ request()->routeIs('admin.schedular.current-jobs.index') ? 'active' : '' }}">
                Current Jobs
              </a>
            </li>
            <li class="">
              <a href="" class="">
                Completed Jobs
              </a>
            </li>
            <li class="">
              <a href="" class="">
                Notifications
              </a>
            </li>
          </ul>
        </li>
        @endif
        
      </ul>
    </nav>
  </div>
</div>

<style>
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

.sidebar-menu li {
    position: relative;
}
</style>