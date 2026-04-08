@extends('layouts.app')

@section('title', 'Orders')

@section('main')
    <div class="main-content">
        <section class="section">

            <div class="section-header">
                <h1>Orders</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active">Orders</div>
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

            <div class="section-body">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Orders</h4>
                    </div>
                    <div class="card-body">

                        {{-- Filter --}}
                        <form method="GET" action="{{ route('admin.orders.index') }}" class="mb-4">
                            <div class="row">
                                <div class="col-md-2">
                                    <select name="status" class="form-control">
                                        <option value="">Semua Status</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                            Pending</option>
                                        <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>
                                            Accepted</option>
                                        <option value="on_progress"
                                            {{ request('status') == 'on_progress' ? 'selected' : '' }}>On Progress</option>
                                        <option value="completed"
                                            {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled"
                                            {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Cari no. order..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="date_from" class="form-control"
                                        value="{{ request('date_from') }}">
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="date_to" class="form-control"
                                        value="{{ request('date_to') }}">
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <div class="col-md-1">
                                    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-block">
                                        <i class="fas fa-times"></i>
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
                                        <th>No. Order</th>
                                        <th>Customer</th>
                                        <th>Tukang</th>
                                        <th>Total</th>
                                        <th>Tgl Servis</th>
                                        <th>Status</th>
                                        <th>Pembayaran</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $statusBadge = [
                                            'pending' => 'warning',
                                            'accepted' => 'primary',
                                            'on_progress' => 'info',
                                            'completed' => 'success',
                                            'cancelled' => 'danger',
                                        ];
                                        $paymentBadge = [
                                            'unpaid' => 'danger',
                                            'pending' => 'warning',
                                            'paid' => 'success',
                                            'failed' => 'danger',
                                            'refunded' => 'secondary',
                                        ];
                                    @endphp
                                    @forelse ($orders as $order)
                                        <tr>
                                            <td>{{ $orders->firstItem() + $loop->index }}</td>
                                            <td>
                                                <a href="{{ route('admin.orders.show', $order) }}"
                                                    class="font-weight-bold">
                                                    {{ $order->order_number }}
                                                </a>
                                            </td>
                                            <td>{{ $order->customer?->name ?? '-' }}</td>
                                            <td>{{ $order->tukang?->name ?? '-' }}</td>
                                            <td>Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                                            <td>{{ $order->service_date?->format('d M Y') ?? '-' }}</td>
                                            <td>
                                                <span
                                                    class="badge badge-{{ $statusBadge[$order->status] ?? 'secondary' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($order->payment)
                                                    <span
                                                        class="badge badge-{{ $paymentBadge[$order->payment->status] ?? 'secondary' }}">
                                                        {{ ucfirst($order->payment->status) }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-light">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.orders.show', $order) }}"
                                                    class="btn btn-sm btn-info" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if (!in_array($order->status, ['completed', 'cancelled']))
                                                    <button type="button" class="btn btn-sm btn-danger" title="Batalkan"
                                                        data-toggle="modal" data-target="#cancelModal"
                                                        data-id="{{ $order->id }}"
                                                        data-number="{{ $order->order_number }}">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <i class="fas fa-shopping-cart fa-2x mb-2 d-block"></i>
                                                Belum ada order
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Menampilkan {{ $orders->firstItem() ?? 0 }}–{{ $orders->lastItem() ?? 0 }}
                                dari {{ $orders->total() }} order
                            </div>
                            {{ $orders->links() }}
                        </div>

                    </div>
                </div>
            </div>

        </section>
    </div>

    {{-- Modal Cancel --}}
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Batalkan Order</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="cancelForm" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-body">
                        <p>Batalkan order <strong id="cancelOrderNumber"></strong>?</p>
                        <div class="form-group">
                            <label>Alasan Pembatalan <span class="text-danger">*</span></label>
                            <textarea name="cancel_reason" class="form-control" rows="3" placeholder="Masukkan alasan pembatalan..."
                                required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times"></i> Batalkan Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#cancelModal').on('show.bs.modal', function(e) {
            const btn = $(e.relatedTarget);
            const id = btn.data('id');
            const number = btn.data('number');
            $('#cancelForm').attr('action', '/admin/orders/' + id + '/cancel');
            $('#cancelOrderNumber').text(number);
        });
    </script>
@endpush
