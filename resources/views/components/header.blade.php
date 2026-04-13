<div class="navbar-bg"></div>
<nav class="navbar navbar-expand-lg main-navbar">
    <form class="form-inline mr-auto">
        <ul class="navbar-nav mr-3">
            <li>
                <a href="#" data-toggle="sidebar" class="nav-link nav-link-lg">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
        </ul>
    </form>
    <ul class="navbar-nav navbar-right">

        {{-- Notifikasi --}}
        <li class="dropdown dropdown-list-toggle">
            <a href="#" data-toggle="dropdown" class="nav-link notification-toggle nav-link-lg beep">
                <i class="far fa-bell"></i>
            </a>
            <div class="dropdown-menu dropdown-list dropdown-menu-right">
                <div class="dropdown-header">
                    Notifikasi
                    <div class="float-right">
                        <a href="#">Tandai Semua Dibaca</a>
                    </div>
                </div>
                <div class="dropdown-list-content dropdown-list-icons">
                    <a href="{{ route('admin.orders.index') }}" class="dropdown-item dropdown-item-unread">
                        <div class="dropdown-item-icon bg-primary text-white">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="dropdown-item-desc">
                            Ada order baru masuk
                            <div class="time text-primary">Baru saja</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.withdrawals.index') }}" class="dropdown-item dropdown-item-unread">
                        <div class="dropdown-item-icon bg-warning text-white">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="dropdown-item-desc">
                            Ada permintaan penarikan baru
                            <div class="time">10 Menit lalu</div>
                        </div>
                    </a>
                </div>
                <div class="dropdown-footer text-center">
                    <a href="#">Lihat Semua <i class="fas fa-chevron-right"></i></a>
                </div>
            </div>
        </li>

        {{-- Profile --}}
        <li class="dropdown">
            <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                @if (auth()->user()->avatar)
                    <img alt="avatar" src="{{ asset(auth()->user()->avatar) }}" class="rounded-circle mr-1"
                        width="30" height="30">
                @else
                    <img alt="avatar" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="rounded-circle mr-1"
                        width="30" height="30">
                @endif
                <div class="d-sm-none d-lg-inline-block">Hi, {{ auth()->user()->name }}</div>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <div class="dropdown-title">{{ auth()->user()->email }}</div>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item has-icon text-danger"
                        style="border:none;background:none;width:100%;text-align:left;cursor:pointer;">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </li>

    </ul>
</nav>
