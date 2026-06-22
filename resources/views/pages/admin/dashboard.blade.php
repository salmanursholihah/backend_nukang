@extends('layouts.app')

@section('title', 'Dashboard')

@section('main')

<section class="section">

    <div class="section-header">
        <h1>Dashboard</h1>
    </div>

    <div class="section-body">

        <div class="row">

            <!-- Total Users -->
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total Users</h4>
                        </div>
                        <div class="card-body">
                            {{ $totalUsers ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Tukang -->
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success">
                        <i class="fas fa-user-hard-hat"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total Tukang</h4>
                        </div>
                        <div class="card-body">
                            {{ $totalTukang ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Orders -->
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total Orders</h4>
                        </div>
                        <div class="card-body">
                            {{ $totalOrders ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Earnings -->
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-danger">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total Earnings</h4>
                        </div>
                        <div class="card-body">
                            Rp {{ number_format($totalEarnings ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Recent Orders -->
        <div class="card">
            <div class="card-header">
                <h4>Recent Orders</h4>
            </div>
            <div class="card-body table-responsive">

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Tukang</th>
                            <th>Service</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders ?? [] as $order)
                            <tr>
                                <td>{{ $order->customer->name ?? '-' }}</td>
                                <td>{{ $order->tukang->name ?? '-' }}</td>
                                <td>{{ $order->service->name ?? '-' }}</td>
                                <td>
                                    <span class="badge badge-primary">
                                        {{ $order->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </div>

    </div>

</section>

@endsection
