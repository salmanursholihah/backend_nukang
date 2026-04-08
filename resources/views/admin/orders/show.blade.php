@extends('layouts.app')

@section('title', 'Detail Order')

@section('main')
    <div class="main-content">
        <section class="section">

            <div class="section-header">
                <h1>Detail Order</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Orders</a></div>
                    <div class="breadcrumb-item active">{{ $order->order_number }}</div>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            @php
                $statusBadge = [
                    'pending' => 'warning',
                    'accepted' => 'primary',
                    'on_progress' => 'info',
                    'completed' => 'success',
                    'cancelled' => 'danger',
                ];
            @endphp

            <div class="section-body">
                <div class="row">

                    {{-- Kolom Kiri --}}
                    <div class="col-md-4">

                        {{-- Status Order --}}
                        <div class="card">
                            <div class="card-header">
                                <h4>Status Order</h4>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <span class="badge badge-{{ $statusBadge[$order->status] ?? 'secondary' }}"
                                        style="font-size:1rem;padding:.5rem 1rem;">
                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                </div>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th>No. Order</th>
                                        <td><strong>{{ $order->order_number }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Tgl Dibuat</th>
                                        <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tgl Servis</th>
                                        <td>{{ $order->service_date?->format('d M Y H:i') ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Mulai</th>
                                        <td>{{ $order->started_at?->format('d M Y H:i') ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Selesai</th>
                                        <td>{{ $order->completed_at?->format('d M Y H:i') ?? '-' }}</td>
                                    </tr>
                                </table>

                                @if ($order->cancel_reason)
                                    <div class="alert alert-danger mt-2">
                                        <strong>Alasan Batal:</strong><br>
                                        {{ $order->cancel_reason }}
                                    </div>
                                @endif

                                @if (!in_array($order->status, ['completed', 'cancelled']))
                                    <button class="btn btn-danger btn-block mt-2" data-toggle="modal"
                                        data-target="#cancelModal">
                                        <i class="fas fa-times"></i> Batalkan Order
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- Customer --}}
                        <div class="card">
                            <div class="card-header">
                                <h4>Customer</h4>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    @if ($order->customer?->avatar)
                                        <img src="{{ asset($order->customer->avatar) }}" class="rounded-circle mr-2"
                                            width="40" height="40">
                                    @else
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-2"
                                            style="width:40px;height:40px;">
                                            {{ strtoupper(substr($order->customer?->name ?? 'C', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <strong>{{ $order->customer?->name ?? '-' }}</strong><br>
                                        <small class="text-muted">{{ $order->customer?->email }}</small>
                                    </div>
                                </div>
                                <p class="mb-0"><i class="fas fa-phone mr-1 text-muted"></i>
                                    {{ $order->customer?->phone ?? '-' }}</p>
                            </div>
                        </div>

                        {{-- Tukang --}}
                        <div class="card">
                            <div class="card-header">
                                <h4>Tukang</h4>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    @if ($order->tukang?->tukangProfile?->photo)
                                        <img src="{{ asset($order->tukang->tukangProfile->photo) }}"
                                            class="rounded-circle mr-2" width="40" height="40">
                                    @else
                                        <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center mr-2"
                                            style="width:40px;height:40px;">
                                            {{ strtoupper(substr($order->tukang?->name ?? 'T', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <strong>{{ $order->tukang?->name ?? '-' }}</strong><br>
                                        <small class="text-muted">
                                            <i class="fas fa-star text-warning"></i>
                                            {{ $order->tukang?->tukangProfile?->rating ?? 0 }}
                                        </small>
                                    </div>
                                </div>
                                <p class="mb-0"><i class="fas fa-phone mr-1 text-muted"></i>
                                    {{ $order->tukang?->phone ?? '-' }}</p>
                            </div>
                        </div>

                    </div>

                    {{-- Kolom Kanan --}}
                    <div class="col-md-8">

                        {{-- Detail Pesanan --}}
                        <div class="card">
                            <div class="card-header">
                                <h4>Detail Pesanan</h4>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Service</th>
                                                <th class="text-right">Harga</th>
                                                <th class="text-center">Qty</th>
                                                <th class="text-right">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($order->details as $detail)
                                                <tr>
                                                    <td>{{ $detail->service_name }}</td>
                                                    <td class="text-right">Rp
                                                        {{ number_format($detail->price, 0, ',', '.') }}</td>
                                                    <td class="text-center">{{ $detail->qty }}</td>
                                                    <td class="text-right">Rp
                                                        {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="thead-light">
                                            <tr>
                                                <td colspan="3" class="text-right"><strong>Subtotal</strong></td>
                                                <td class="text-right">Rp
                                                    {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="text-right">Biaya Platform (10%)</td>
                                                <td class="text-right">Rp
                                                    {{ number_format($order->service_fee, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="text-right"><strong>Total</strong></td>
                                                <td class="text-right"><strong>Rp
                                                        {{ number_format($order->total_price, 0, ',', '.') }}</strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Alamat & Catatan --}}
                        <div class="card">
                            <div class="card-header">
                                <h4>Alamat & Catatan</h4>
                            </div>
                            <div class="card-body">
                                <p><i class="fas fa-map-marker-alt text-danger mr-2"></i> {{ $order->address }}</p>
                                @if ($order->notes)
                                    <p class="text-muted mb-0"><i class="fas fa-sticky-note mr-2"></i> {{ $order->notes }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Pembayaran --}}
                        @if ($order->payment)
                            <div class="card">
                                <div class="card-header">
                                    <h4>Pembayaran</h4>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <th width="140">Status</th>
                                            <td>
                                                @php $payBadge = ['unpaid'=>'danger','pending'=>'warning','paid'=>'success','failed'=>'danger']; @endphp
                                                <span
                                                    class="badge badge-{{ $payBadge[$order->payment->status] ?? 'secondary' }}">
                                                    {{ ucfirst($order->payment->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Metode</th>
                                            <td>{{ ucfirst($order->payment->method ?? '-') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Channel</th>
                                            <td>{{ $order->payment->payment_channel ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Jumlah</th>
                                            <td>Rp {{ number_format($order->payment->amount, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Dibayar</th>
                                            <td>{{ $order->payment->paid_at?->format('d M Y H:i') ?? '-' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        @endif

                        {{-- Progress Pengerjaan --}}
                        @if ($order->progresses->count() > 0)
                            <div class="card">
                                <div class="card-header">
                                    <h4>Progress Pengerjaan</h4>
                                </div>
                                <div class="card-body">
                                    @foreach ($order->progresses as $progress)
                                        <div class="d-flex mb-3">
                                            @if ($progress->photo)
                                                <img src="{{ asset($progress->photo) }}" class="rounded mr-3"
                                                    style="width:80px;height:80px;object-fit:cover;">
                                            @endif
                                            <div>
                                                <strong>{{ $progress->title }}</strong>
                                                <p class="text-muted mb-1">{{ $progress->description }}</p>
                                                <small
                                                    class="text-muted">{{ $progress->created_at->format('d M Y H:i') }}</small>
                                            </div>
                                        </div>
                                        @if (!$loop->last)
                                            <hr>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Review --}}
                        @if ($order->review)
                            <div class="card">
                                <div class="card-header">
                                    <h4>Review Customer</h4>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i
                                                class="fas fa-star {{ $i <= $order->review->rating ? 'text-warning' : 'text-muted' }}"></i>
                                        @endfor
                                        <strong class="ml-2">{{ $order->review->rating }}/5</strong>
                                    </div>
                                    <p class="text-muted">{{ $order->review->comment ?? 'Tidak ada komentar.' }}</p>
                                    @if ($order->review->tags)
                                        @foreach ($order->review->tags as $tag)
                                            <span class="badge badge-light border mr-1">{{ $tag }}</span>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endif

                        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>

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
                <form method="POST" action="{{ route('admin.orders.cancel', $order) }}">
                    @csrf @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Alasan Pembatalan <span class="text-danger">*</span></label>
                            <textarea name="cancel_reason" class="form-control" rows="3" placeholder="Masukkan alasan pembatalan..."
                                required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times"></i> Batalkan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
