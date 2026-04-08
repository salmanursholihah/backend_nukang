@extends('layouts.app')

@section('title', 'Withdrawals')

@section('main')
    <div class="main-content">
        <section class="section">

            <div class="section-header">
                <h1>Withdrawals</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active">Withdrawals</div>
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

            {{-- Summary pending --}}
            @if ($pendingAmount > 0)
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Ada <strong>Rp {{ number_format($pendingAmount, 0, ',', '.') }}</strong> penarikan menunggu diproses.
                    <a href="{{ route('admin.withdrawals.index', ['status' => 'pending']) }}" class="alert-link ml-2">
                        Lihat semua pending
                    </a>
                </div>
            @endif

            <div class="section-body">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Withdrawals</h4>
                    </div>
                    <div class="card-body">

                        {{-- Filter --}}
                        <form method="GET" action="{{ route('admin.withdrawals.index') }}" class="mb-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <select name="status" class="form-control">
                                        <option value="">Semua Status</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                            Pending</option>
                                        <option value="processing"
                                            {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>
                                            Success</option>
                                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>
                                            Failed</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-secondary btn-block">
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
                                        <th>Bank</th>
                                        <th>No. Rekening</th>
                                        <th class="text-right">Jumlah</th>
                                        <th>Status</th>
                                        <th>Tgl Pengajuan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $statusBadge = [
                                            'pending' => 'warning',
                                            'processing' => 'info',
                                            'success' => 'success',
                                            'failed' => 'danger',
                                        ];
                                    @endphp
                                    @forelse ($withdrawals as $withdrawal)
                                        <tr class="{{ $withdrawal->status === 'pending' ? 'table-warning' : '' }}">
                                            <td>{{ $withdrawals->firstItem() + $loop->index }}</td>
                                            <td>
                                                <strong>{{ $withdrawal->tukang?->name ?? '-' }}</strong><br>
                                                <small class="text-muted">{{ $withdrawal->tukang?->phone }}</small>
                                            </td>
                                            <td>{{ $withdrawal->bank_name }}</td>
                                            <td>
                                                {{ $withdrawal->bank_account_number }}<br>
                                                <small class="text-muted">a/n {{ $withdrawal->bank_account_name }}</small>
                                            </td>
                                            <td class="text-right font-weight-bold">
                                                Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}
                                            </td>
                                            <td>
                                                <span
                                                    class="badge badge-{{ $statusBadge[$withdrawal->status] ?? 'secondary' }}">
                                                    {{ ucfirst($withdrawal->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $withdrawal->created_at->format('d M Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('admin.withdrawals.show', $withdrawal) }}"
                                                    class="btn btn-sm btn-info" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if ($withdrawal->status === 'pending')
                                                    <button type="button" class="btn btn-sm btn-success" title="Setujui"
                                                        data-toggle="modal" data-target="#approveModal"
                                                        data-id="{{ $withdrawal->id }}"
                                                        data-name="{{ $withdrawal->tukang?->name }}"
                                                        data-amount="Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" title="Tolak"
                                                        data-toggle="modal" data-target="#rejectModal"
                                                        data-id="{{ $withdrawal->id }}"
                                                        data-name="{{ $withdrawal->tukang?->name }}"
                                                        data-amount="Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="fas fa-wallet fa-2x mb-2 d-block"></i>
                                                Belum ada withdrawal
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Menampilkan {{ $withdrawals->firstItem() ?? 0 }}–{{ $withdrawals->lastItem() ?? 0 }}
                                dari {{ $withdrawals->total() }} withdrawal
                            </div>
                            {{ $withdrawals->links() }}
                        </div>

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
                <form id="approveForm" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-body">
                        <p>Setujui penarikan <strong id="approveName"></strong> sebesar <strong
                                id="approveAmount"></strong>?</p>
                        <div class="form-group">
                            <label>Reference ID <small class="text-muted">(opsional)</small></label>
                            <input type="text" name="reference_id" class="form-control"
                                placeholder="Nomor referensi transfer dari bank">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Setujui & Transfer
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
                <form id="rejectForm" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-body">
                        <p>Tolak penarikan <strong id="rejectName"></strong> sebesar <strong id="rejectAmount"></strong>?
                        </p>
                        <div class="form-group">
                            <label>Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Masukkan alasan penolakan..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times"></i> Tolak Penarikan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#approveModal').on('show.bs.modal', function(e) {
            const btn = $(e.relatedTarget);
            $('#approveForm').attr('action', '/admin/withdrawals/' + btn.data('id') + '/approve');
            $('#approveName').text(btn.data('name'));
            $('#approveAmount').text(btn.data('amount'));
        });

        $('#rejectModal').on('show.bs.modal', function(e) {
            const btn = $(e.relatedTarget);
            $('#rejectForm').attr('action', '/admin/withdrawals/' + btn.data('id') + '/reject');
            $('#rejectName').text(btn.data('name'));
            $('#rejectAmount').text(btn.data('amount'));
        });
    </script>
@endpush
