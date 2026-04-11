{{-- ============================================================ --}}
{{-- resources/views/admin/earnings/index.blade.php             --}}
{{-- ============================================================ --}}
@extends('layouts.app')

@section('title', 'Earnings')

@section('main')
    <div class="main-content">
        <section class="section">

            <div class="section-header">
                <h1>Earnings</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active">Earnings</div>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            {{-- Summary Cards --}}
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Pending</h4>
                            </div>
                            <div class="card-body">Rp {{ number_format($summary->total_pending ?? 0, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-primary">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Settled</h4>
                            </div>
                            <div class="card-body">Rp {{ number_format($summary->total_settled ?? 0, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-success">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Paid</h4>
                            </div>
                            <div class="card-body">Rp {{ number_format($summary->total_paid ?? 0, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-danger">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Platform Fee</h4>
                            </div>
                            <div class="card-body">Rp {{ number_format($summary->total_platform_fee ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-body">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Earnings</h4>
                    </div>
                    <div class="card-body">

                        {{-- Filter --}}
                        <form method="GET" action="{{ route('admin.earnings.index') }}" class="mb-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <select name="status" class="form-control">
                                        <option value="">Semua Status</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                            Pending</option>
                                        <option value="settled" {{ request('status') == 'settled' ? 'selected' : '' }}>
                                            Settled</option>
                                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('admin.earnings.index') }}" class="btn btn-secondary btn-block">
                                        <i class="fas fa-times"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>

                        {{-- Table --}}
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Tukang</th>
                                        <th>No. Order</th>
                                        <th class="text-right">Order Amount</th>
                                        <th class="text-right">Platform Fee</th>
                                        <th class="text-right">Diterima</th>
                                        <th>Status</th>
                                        <th>Settled At</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $statusBadge = [
                                            'pending' => 'warning',
                                            'settled' => 'primary',
                                            'paid' => 'success',
                                        ];
                                    @endphp
                                    @forelse ($earnings as $earning)
                                        <tr>
                                            <td>{{ $earnings->firstItem() + $loop->index }}</td>
                                            <td>{{ $earning->tukang?->name ?? '-' }}</td>
                                            <td>
                                                @if ($earning->order)
                                                    <a href="{{ route('admin.orders.show', $earning->order) }}">
                                                        {{ $earning->order->order_number }}
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="text-right">Rp
                                                {{ number_format($earning->order_amount, 0, ',', '.') }}</td>
                                            <td class="text-right text-danger">Rp
                                                {{ number_format($earning->platform_fee, 0, ',', '.') }}</td>
                                            <td class="text-right text-success font-weight-bold">
                                                Rp {{ number_format($earning->amount, 0, ',', '.') }}
                                            </td>
                                            <td>
                                                <span
                                                    class="badge badge-{{ $statusBadge[$earning->status] ?? 'secondary' }}">
                                                    {{ ucfirst($earning->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $earning->settled_at?->format('d M Y') ?? '-' }}</td>
                                            <td>
                                                <a href="{{ route('admin.earnings.show', $earning) }}"
                                                    class="btn btn-sm btn-info" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if ($earning->status === 'pending')
                                                    <form method="POST"
                                                        action="{{ route('admin.earnings.settle', $earning) }}"
                                                        class="d-inline">
                                                        @csrf @method('PUT')
                                                        <button type="submit" class="btn btn-sm btn-success" title="Settle"
                                                            onclick="return confirm('Settle earning ini? Tukang akan bisa mencairkan dana.')">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <i class="fas fa-money-bill-wave fa-2x mb-2 d-block"></i>
                                                Belum ada data earning
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Menampilkan {{ $earnings->firstItem() ?? 0 }}–{{ $earnings->lastItem() ?? 0 }}
                                dari {{ $earnings->total() }} earning
                            </div>
                            {{ $earnings->links() }}
                        </div>

                    </div>
                </div>
            </div>

        </section>
    </div>
@endsection
