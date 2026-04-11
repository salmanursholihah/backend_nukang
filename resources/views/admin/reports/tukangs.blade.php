@extends('layouts.app')

@section('title', 'Performa Tukang')

@section('main')
    <div class="main-content">
        <section class="section">

            <div class="section-header">
                <h1>Performa Tukang</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                    <div class="breadcrumb-item active">Tukang</div>
                </div>
            </div>

            <div class="section-body">
                <div class="card">
                    <div class="card-header">
                        <h4>Ranking Tukang</h4>
                        <div class="card-header-action">
                            <small class="text-muted">Diurutkan berdasarkan order selesai terbanyak</small>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Tukang</th>
                                        <th>Kota</th>
                                        <th class="text-center">Rating</th>
                                        <th class="text-center">Order Selesai</th>
                                        <th class="text-center">Total Review</th>
                                        <th class="text-right">Total Penghasilan</th>
                                        <th class="text-center">Verifikasi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($tukangs as $tukang)
                                        <tr>
                                            <td>
                                                @if ($loop->iteration <= 3)
                                                    @php
                                                        $medalColor = ['text-warning', 'text-secondary', 'text-danger'];
                                                    @endphp
                                                    <i class="fas fa-trophy {{ $medalColor[$loop->index] }}"></i>
                                                    <strong>{{ $tukangs->firstItem() + $loop->index }}</strong>
                                                @else
                                                    {{ $tukangs->firstItem() + $loop->index }}
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if ($tukang->tukangProfile?->photo)
                                                        <img src="{{ asset($tukang->tukangProfile->photo) }}"
                                                            class="rounded-circle mr-2" width="36" height="36"
                                                            style="object-fit:cover;">
                                                    @else
                                                        <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center mr-2"
                                                            style="width:36px;height:36px;font-size:14px;">
                                                            {{ strtoupper(substr($tukang->name, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <strong>{{ $tukang->name }}</strong><br>
                                                        <small class="text-muted">{{ $tukang->phone }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $tukang->tukangProfile?->city ?? '-' }}</td>
                                            <td class="text-center">
                                                <i class="fas fa-star text-warning"></i>
                                                <strong>{{ $tukang->tukangProfile?->rating ?? 0 }}</strong>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-success badge-pill">
                                                    {{ $tukang->completed_orders }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                {{ $tukang->tukangProfile?->total_reviews ?? 0 }}
                                            </td>
                                            <td class="text-right font-weight-bold text-success">
                                                Rp {{ number_format($tukang->total_earned ?? 0, 0, ',', '.') }}
                                            </td>
                                            <td class="text-center">
                                                @if ($tukang->tukangProfile?->is_verified)
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check"></i> Verified
                                                    </span>
                                                @else
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-clock"></i> Pending
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.users.show', $tukang) }}"
                                                    class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <i class="fas fa-user-cog fa-2x mb-2 d-block"></i>
                                                Belum ada data tukang
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                Menampilkan {{ $tukangs->firstItem() ?? 0 }}–{{ $tukangs->lastItem() ?? 0 }}
                                dari {{ $tukangs->total() }} tukang
                            </div>
                            {{ $tukangs->links() }}
                        </div>
                    </div>
                </div>
            </div>

        </section>
    </div>
@endsection
