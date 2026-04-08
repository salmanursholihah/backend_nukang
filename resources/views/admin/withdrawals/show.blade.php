@extends('layouts.app')

@section('title', 'Detail Withdrawal')

@section('main')
    <div class="main-content">
        <section class="section">

            <div class="section-header">
                <h1>Detail Withdrawal</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.withdrawals.index') }}">Withdrawals</a></div>
                    <div class="breadcrumb-item active">Detail</div>
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

            @php
                $statusBadge = [
                    'pending' => 'warning',
                    'processing' => 'info',
                    'success' => 'success',
                    'failed' => 'danger',
                ];
            @endphp

            <div class="section-body">
                <div class="row">

                    {{-- Kiri --}}
                    <div class="col-md-4">

                        {{-- Status & Jumlah --}}
                        <div class="card">
                            <div class="card-header">
                                <h4>Status Penarikan</h4>
                            </div>
                            <div class="card-body text-center">
                                <span class="badge badge-{{ $statusBadge[$withdrawal->status] ?? 'secondary' }}"
                                    style="font-size:1rem;padding:.5rem 1.2rem;">
                                    {{ ucfirst($withdrawal->status) }}
                                </span>
                                <div class="mt-3 mb-2">
                                    <div class="text-muted small">Jumlah Penarikan</div>
                                    <div class="h3 font-weight-bold text-success mt-1">
                                        Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}
                                    </div>
                                </div>
                                <div class="text-muted small">
                                    <div>Diajukan: {{ $withdrawal->created_at->format('d M Y H:i') }}</div>
                                    @if ($withdrawal->processed_at)
                                        <div>Diproses: {{ $withdrawal->processed_at->format('d M Y H:i') }}</div>
                                    @endif
                                </div>

                                @if ($withdrawal->reference_id)
                                    <div class="mt-2">
                                        <small class="text-muted">Reference ID:</small><br>
                                        <code>{{ $withdrawal->reference_id }}</code>
                                    </div>
                                @endif

                                @if ($withdrawal->notes)
                                    <div
                                        class="alert alert-{{ $withdrawal->status === 'failed' ? 'danger' : 'info' }} mt-3 text-left">
                                        <small>{{ $withdrawal->notes }}</small>
                                    </div>
                                @endif

                                @if ($withdrawal->status === 'pending')
                                    <div class="mt-3">
                                        <button class="btn btn-success btn-block mb-2" data-toggle="modal"
                                            data-target="#approveModal">
                                            <i class="fas fa-check"></i> Setujui & Transfer
                                        </button>
                                        <button class="btn btn-danger btn-block" data-toggle="modal"
                                            data-target="#rejectModal">
                                            <i class="fas fa-times"></i> Tolak
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Info Tukang --}}
                        <div class="card">
                            <div class="card-header">
                                <h4>Tukang</h4>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center mr-2"
                                        style="width:40px;height:40px;">
                                        {{ strtoupper(substr($withdrawal->tukang?->name ?? 'T', 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $withdrawal->tukang?->name ?? '-' }}</strong><br>
                                        <small class="text-muted">{{ $withdrawal->tukang?->email }}</small>
                                    </div>
                                </div>
                                <p class="mb-1"><i
                                        class="fas fa-phone mr-2 text-muted"></i>{{ $withdrawal->tukang?->phone ?? '-' }}
                                </p>
                            </div>
                        </div>

                    </div>

                    {{-- Kanan --}}
                    <div class="col-md-8">

                        {{-- Info Rekening --}}
                        <div class="card">
                            <div class="card-header">
                                <h4>Informasi Rekening Tujuan</h4>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless mb-0">
                                    <tr>
                                        <th width="180">Bank</th>
                                        <td>
                                            <span class="badge badge-primary" style="font-size:.9rem;padding:.4rem .8rem;">
                                                {{ strtoupper($withdrawal->bank_name) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>No. Rekening</th>
                                        <td>
                                            <strong style="font-size:1.1rem;letter-spacing:1px;">
                                                {{ $withdrawal->bank_account_number }}
                                            </strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Atas Nama</th>
                                        <td>{{ strtoupper($withdrawal->bank_account_name) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Jumlah Transfer</th>
                                        <td>
                                            <strong class="text-success h5">
                                                Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}
                                            </strong>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>

                    </div>

                </div>
            </div>

        </section>
    </div>

    {{-- Modal Approve --}}
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Setujui Penarikan</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form method="POST" action="{{ route('admin.withdrawals.approve', $withdrawal) }}">
                    @csrf @method('PUT')
                    <div class="modal-body">
                        <div class="alert alert-info">
                            Transfer <strong>Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</strong>
                            ke rekening <strong>{{ $withdrawal->bank_name }}
                                {{ $withdrawal->bank_account_number }}</strong>
                            a/n <strong>{{ $withdrawal->bank_account_name }}</strong>
                        </div>
                        <div class="form-group">
                            <label>Reference ID <small class="text-muted">(opsional)</small></label>
                            <input type="text" name="reference_id" class="form-control"
                                placeholder="Nomor referensi transfer dari bank">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Konfirmasi Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Reject --}}
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tolak Penarikan</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal) }}">
                    @csrf @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Masukkan alasan penolakan..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times"></i> Tolak
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
