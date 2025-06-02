<!-- Side Nav -->
<div class="flapt-sidenav" id="flaptSideNav">
  <!-- Side Menu Area -->
  <div class="side-menu-area">
    <!-- Sidebar Menu -->
    <nav>
      <ul class="sidebar-menu" data-widget="tree">
        <li class="menu-header-title">Main</li>

        <li><a href="{{ route('dashboard') }}"><i class="bx bx-home-heart"></i> Dashboard </a></li>

        <li class="menu-header-title">CRM Modules</li>
        <li><a href="{{ route('admin.customers.index') }}"><i class="bx bx-group"></i> Customer Overview</a></li>
        <li><a href="{{ route('admin.orders.index') }}"><i class="bx bxs-cart"></i> Orders Overview</a></li>
        @if (auth()->user()->role === 'admin')
        <li class="treeview">
          <a href="javascript:void(0)">
            <i class="bx bx-user-circle"></i>
            <span>User Management</span>
            <i class="fa fa-angle-right"></i>
          </a>
          <ul class="treeview-menu">
            <li><a href="{{ route('admin.users.index') }}">All Users</a></li>
            <li><a href="{{ route('admin.users.create') }}">Add New User</a></li>
          </ul>
        </li>
        @endif
<!--         
        <li class="treeview">
          <a href="javascript:void(0)">
            <i class="bx bx-group"></i>
            <span>Customers</span>
            <i class="fa fa-angle-right"></i>
          </a>
          <ul class="treeview-menu">
            <li><a href="{{ route('admin.customers.index') }}">Customer List</a></li>
          </ul>
        </li>

        <li class="treeview">
          <a href="javascript:void(0)">
            <i class="bx bxs-cart"></i>
            <span>Orders</span>
            <i class="fa fa-angle-right"></i>
          </a>
          <ul class="treeview-menu">
            <li><a href="{{ route('admin.orders.index') }}">All Orders</a></li>
          </ul>
        </li> -->

        <!-- <li class="menu-header-title">Account</li>
        <li>
          <a href="account.html">
            <i class="bx bx-cog"></i>
            <span>Account Settings</span>
          </a>
        </li>
        <li>
          <a href="logout.html">
            <i class="bx bx-power-off"></i>
            <span>Logout</span>
          </a>
        </li> -->
      </ul>
    </nav>
  </div>
</div>
