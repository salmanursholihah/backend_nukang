<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">

        <div class="sidebar-brand">
            <a href="{{ route('admin.dashboard') }}">Nukang Admin</a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="{{ route('admin.dashboard') }}">NA</a>
        </div>

        <ul class="sidebar-menu">

            {{-- Dashboard --}}
            <li class="menu-header">Dashboard</li>
            <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-fire"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            {{-- Master Data --}}
            <li class="menu-header">Master Data</li>
            <li class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.categories.index') }}">
                    <i class="fas fa-list"></i>
                    <span>Kategori</span>
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.services.index') }}">
                    <i class="fas fa-tools"></i>
                    <span>Services</span>
                </a>
            </li>

            {{-- Users --}}
            <li class="menu-header">Users</li>
            <li class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.users.index') }}">
                    <i class="fas fa-users"></i>
                    <span>Semua User</span>
                </a>
            </li>

            {{-- Transaksi --}}
            <li class="menu-header">Transaksi</li>
            <li class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.orders.index') }}">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Orders</span>
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.surveys.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.surveys.index') }}">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Survey</span>
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.earnings.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.earnings.index') }}">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Earnings</span>
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.withdrawals.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.withdrawals.index') }}">
                    <i class="fas fa-wallet"></i>
                    <span>Withdrawals</span>
                </a>
            </li>

            {{-- Monitoring --}}
            <li class="menu-header">Monitoring</li>
            <li class="{{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.reviews.index') }}">
                    <i class="fas fa-star"></i>
                    <span>Reviews</span>
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.reports.index') }}">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
            </li>

            {{-- Account --}}
            <li class="menu-header">Account</li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-danger btn-block text-left" type="submit">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </li>

        </ul>
    </aside>
</div>
