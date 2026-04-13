@extends('layouts.app')

@section('title', 'Dashboard')

@section('main')
    <div class="main-content">
        <section class="section">

            {{-- Page Header --}}
            <div class="section-header">
                <h1>Dashboard</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active">Dashboard</div>
                </div>
            </div>

            {{-- Alert sukses / error --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            {{-- ── Stat Cards ── --}}
            <div class="row">

                {{-- Total Customer --}}
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Customer</h4>
                            </div>
                            <div class="card-body">
                                {{ number_format($totalCustomers) }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total Tukang --}}
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-warning">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Tukang</h4>
                            </div>
                            <div class="card-body">
                                {{ number_format($totalTukangs) }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total Order --}}
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-success">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Order</h4>
                            </div>
                            <div class="card-body">
                                {{ number_format($orderStats->total) }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pendapatan Bulan Ini --}}
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-danger">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Pendapatan Bulan Ini</h4>
                            </div>
                            <div class="card-body">
                                Rp {{ number_format($revenueThisMonth, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── Row 2: Order Status + Pending Actions ── --}}
            <div class="row">

                {{-- Status Order --}}
                <div class="col-lg-8 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Status Order</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.orders.index') }}" class="btn btn-primary btn-sm">
                                    Lihat Semua
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 col-lg-4 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bullet bullet-warning mr-2"></div>
                                        <div>
                                            <div class="text-muted text-small">Pending</div>
                                            <div class="font-weight-bold">{{ $orderStats->pending }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-lg-4 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bullet bullet-primary mr-2"></div>
                                        <div>
                                            <div class="text-muted text-small">Accepted</div>
                                            <div class="font-weight-bold">{{ $orderStats->accepted }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-lg-4 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bullet bullet-info mr-2"></div>
                                        <div>
                                            <div class="text-muted text-small">On Progress</div>
                                            <div class="font-weight-bold">{{ $orderStats->on_progress }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-lg-4 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bullet bullet-success mr-2"></div>
                                        <div>
                                            <div class="text-muted text-small">Completed</div>
                                            <div class="font-weight-bold">{{ $orderStats->completed }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-lg-4 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bullet bullet-danger mr-2"></div>
                                        <div>
                                            <div class="text-muted text-small">Cancelled</div>
                                            <div class="font-weight-bold">{{ $orderStats->cancelled }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Chart Order 7 Hari --}}
                            <canvas id="orderChart" height="120"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Pending Actions --}}
                <div class="col-lg-4 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Perlu Tindakan</h4>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">

                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-wallet text-warning mr-2"></i>
                                        <span>Withdrawal Pending</span>
                                    </div>
                                    <a href="{{ route('admin.withdrawals.index') }}"
                                        class="badge badge-warning badge-pill">
                                        {{ $pendingWithdrawals }}
                                    </a>
                                </li>

                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-clipboard-list text-info mr-2"></i>
                                        <span>Survey Menunggu</span>
                                    </div>
                                    <a href="{{ route('admin.surveys.index') }}" class="badge badge-info badge-pill">
                                        {{ $pendingSurveys }}
                                    </a>
                                </li>

                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-user-check text-success mr-2"></i>
                                        <span>Tukang Belum Verifikasi</span>
                                    </div>
                                    <a href="{{ route('admin.users.index', ['role' => 'tukang', 'is_verified' => 0]) }}"
                                        class="badge badge-success badge-pill">
                                        {{ $unverifiedTukangs }}
                                    </a>
                                </li>

                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-users text-primary mr-2"></i>
                                        <span>User Baru Hari Ini</span>
                                    </div>
                                    <span class="badge badge-primary badge-pill">
                                        {{ $newUsersToday }}
                                    </span>
                                </li>

                            </ul>
                        </div>
                    </div>

                    {{-- Pendapatan Hari Ini --}}
                    <div class="card">
                        <div class="card-header">
                            <h4>Pendapatan Hari Ini</h4>
                        </div>
                        <div class="card-body text-center">
                            <h3 class="text-success font-weight-bold">
                                Rp {{ number_format($revenueToday, 0, ',', '.') }}
                            </h3>
                            <p class="text-muted mb-0">
                                Total: Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── Row 3: Recent Orders + Recent Withdrawals ── --}}
            <div class="row">

                {{-- Recent Orders --}}
                <div class="col-lg-7 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Order Terbaru</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.orders.index') }}" class="btn btn-primary btn-sm">
                                    Lihat Semua
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>No. Order</th>
                                            <th>Customer</th>
                                            <th>Tukang</th>
                                            <th>Status</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentOrders as $order)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('admin.orders.show', $order) }}">
                                                        {{ $order->order_number }}
                                                    </a>
                                                </td>
                                                <td>{{ $order->customer?->name ?? '-' }}</td>
                                                <td>{{ $order->tukang?->name ?? '-' }}</td>
                                                <td>
                                                    @php
                                                        $badges = [
                                                            'pending' => 'warning',
                                                            'accepted' => 'primary',
                                                            'on_progress' => 'info',
                                                            'completed' => 'success',
                                                            'cancelled' => 'danger',
                                                        ];
                                                        $badge = $badges[$order->status] ?? 'secondary';
                                                    @endphp
                                                    <span class="badge badge-{{ $badge }}">
                                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                                    </span>
                                                </td>
                                                <td>Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">Belum ada order</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Recent Withdrawals --}}
                <div class="col-lg-5 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Withdrawal Pending</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-warning btn-sm">
                                    Lihat Semua
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Tukang</th>
                                            <th>Jumlah</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentWithdrawals as $withdrawal)
                                            <tr>
                                                <td>{{ $withdrawal->tukang?->name ?? '-' }}</td>
                                                <td>Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</td>
                                                <td>
                                                    <a href="{{ route('admin.withdrawals.show', $withdrawal) }}"
                                                        class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">Tidak ada</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
        // Chart Order 7 Hari Terakhir
        const orderChartData = @json($chartOrders);
        const revenueChartData = @json($chartRevenue);

        // Siapkan label 7 hari terakhir
        const labels = [];
        for (let i = 6; i >= 0; i--) {
            const d = new Date();
            d.setDate(d.getDate() - i);
            labels.push(d.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short'
            }));
        }

        // Format tanggal untuk match key dari PHP
        const dateKeys = [];
        for (let i = 6; i >= 0; i--) {
            const d = new Date();
            d.setDate(d.getDate() - i);
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            dateKeys.push(`${yyyy}-${mm}-${dd}`);
        }

        const orderValues = dateKeys.map(k => orderChartData[k] || 0);

        const ctx = document.getElementById('orderChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Order',
                    data: orderValues,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
@endpush
