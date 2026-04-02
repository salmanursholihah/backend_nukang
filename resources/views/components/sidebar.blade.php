<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">

        <div class="sidebar-brand">
            <a href="{{ route('dashboard') }}">Nukang Admin</a>
        </div>

        <div class="sidebar-brand sidebar-brand-sm">
            <a href="{{ route('dashboard') }}">NA</a>
        </div>

        <ul class="sidebar-menu">

            <li class="menu-header">Dashboard</li>

            <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="fas fa-fire"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="menu-header">Master Data</li>

            <li class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('categories.index') }}">
                    <i class="fas fa-list"></i>
                    <span>Categories</span>
                </a>
            </li>

            <li class="{{ request()->routeIs('services.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('services.index') }}">
                    <i class="fas fa-tools"></i>
                    <span>Services</span>
                </a>
            </li>

            <li class="menu-header">Users</li>

            <li class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('users.index') }}">
                    <i class="fas fa-users"></i>
                    <span>User Management</span>
                </a>
            </li>

            <li class="{{ request()->routeIs('tukangs.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('tukangs.index') }}">
                    <i class="fas fa-user-cog"></i>
                    <span>Tukang Management</span>
                </a>
            </li>

            <li class="menu-header">Transactions</li>

            <li class="{{ request()->routeIs('orders.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('orders.index') }}">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Orders</span>
                </a>
            </li>

            <li class="{{ request()->routeIs('earnings.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('earnings.index') }}">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Earnings</span>
                </a>
            </li>

            <li class="menu-header">Monitoring</li>

            <li class="{{ request()->routeIs('reviews.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('reviews.index') }}">
                    <i class="fas fa-star"></i>
                    <span>Reviews</span>
                </a>
            </li>

            <li class="{{ request()->routeIs('surveys.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('surveys.index') }}">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Surveys</span>
                </a>
            </li>

            <li class="menu-header">Account</li>

            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-danger btn-block text-left">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </li>

        </ul>
    </aside>
</div>
