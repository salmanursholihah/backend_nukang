@extends('layouts.app')

@section('title', 'Detail Earning')

@section('main')
    <div class="main-content">
        <section class="section">

            <div class="section-header">
                <h1>Detail Earning</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.earnings.index') }}">Earnings</a></div>
                    <div class="breadcrumb-item active">Detail</div>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            @php
                $statusBadge = ['pending' => 'warning', 'settled' => 'primary', 'paid' => 'success'];
            @endphp

            <div class="section-body">
                <div class="row">

                    {{-- Kiri --}}
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Status Earning</h4>
                            </div>
                            <div class="card-body text-center">
                                <span class="badge badge-{{ $statusBadge[$earning->status] ?? 'secondary' }}"
                                    style="font-size:1rem;padding:.5rem 1.2rem;">
                                    {{ ucfirst($earning->status) }}
                                </span>

                                {{-- Breakdown nominal --}}
                                <div class="mt-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Total Order</span>
                                        <strong>Rp {{ number_format($earning->order_amount, 0, ',', '.') }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Platform Fee (10%)</span>
                                        <strong class="text-danger">- Rp
                                            {{ number_format($earning->platform_fee, 0, ',', '.') }}</strong>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <span class="font-weight-bold">Diterima Tukang</span>
                                        <strong class="text-success h5">Rp
                                            {{ number_format($earning->amount, 0, ',', '.') }}</strong>
                                    </div>
                                </div>

                                <div class="mt-3 text-muted small">
                                    <div>Dibuat: {{ $earning->created_at->format('d M Y H:i') }}</div>
                                    @if ($earning->settled_at)
                                        <div>Settled: {{ $earning->settled_at->format('d M Y H:i') }}</div>
                                    @endif
                                </div>

                                @if ($earning->status === 'pending')
                                    <form method="POST" action="{{ route('admin.earnings.settle', $earning) }}"
                                        class="mt-3">
                                        @csrf @method('PUT')
                                        <button type="submit" class="btn btn-success btn-block"
                                            onclick="return confirm('Settle earning ini? Tukang akan bisa mencairkan dana.')">
                                            <i class="fas fa-check"></i> Settle Earning
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        {{-- Info Tukang --}}
                        <div class="card">
                            <div class="card-header">
                                <h4>Tukang</h4>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center mr-2"
                                        style="width:40px;height:40px;">
                                        {{ strtoupper(substr($earning->tukang?->name ?? 'T', 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $earning->tukang?->name ?? '-' }}</strong><br>
                                        <small class="text-muted">{{ $earning->tukang?->phone ?? '-' }}</small>
                                    </div>
                                </div>
                                <p class="mb-0 text-muted small">{{ $earning->tukang?->email }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Kanan --}}
                    <div class="col-md-8">
                        {{-- Info Order --}}
                        @if ($earning->order)
                            <div class="card">
                                <div class="card-header">
                                    <h4>Order Terkait</h4>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <th width="160">No. Order</th>
                                            <td>
                                                <a href="{{ route('admin.orders.show', $earning->order) }}"
                                                    class="font-weight-bold">
                                                    {{ $earning->order->order_number }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Total Order</th>
                                            <td>Rp {{ number_format($earning->order->total_price, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Selesai</th>
                                            <td>{{ $earning->order->completed_at?->format('d M Y H:i') ?? '-' }}</td>
                                        </tr>
                                    </table>
                                    <a href="{{ route('admin.orders.show', $earning->order) }}"
                                        class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> Lihat Detail Order
                                    </a>
                                </div>
                            </div>
                        @endif

                        <a href="{{ route('admin.earnings.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                </div>
            </div>

        </section>
    </div>
@endsection
