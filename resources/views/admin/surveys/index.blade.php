{{-- ============================================================ --}}
{{-- resources/views/admin/surveys/index.blade.php              --}}
{{-- ============================================================ --}}
@extends('layouts.app')

@section('title', 'Survey Requests')

@section('main')
    <div class="main-content">
        <section class="section">

            <div class="section-header">
                <h1>Survey Requests</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active">Surveys</div>
                </div>
            </div>

            <div class="section-body">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Survey</h4>
                    </div>
                    <div class="card-body">

                        {{-- Filter --}}
                        <form method="GET" action="{{ route('admin.surveys.index') }}" class="mb-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <select name="status" class="form-control">
                                        <option value="">Semua Status</option>
                                        <option value="requested" {{ request('status') == 'requested' ? 'selected' : '' }}>
                                            Requested</option>
                                        <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>
                                            Accepted</option>
                                        <option value="on_survey" {{ request('status') == 'on_survey' ? 'selected' : '' }}>
                                            On Survey</option>
                                        <option
                                            value="survey_priced"{{ request('status') == 'survey_priced' ? 'selected' : '' }}>
                                            Survey Priced</option>
                                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>
                                            Approved</option>
                                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>
                                            Rejected</option>
                                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                                            Cancelled</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Cari nama customer..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary btn-block">
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
                                        <th>Customer</th>
                                        <th>Tukang</th>
                                        <th>Service</th>
                                        <th>Tgl Survey</th>
                                        <th>Estimasi</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $statusBadge = [
                                            'requested' => 'warning',
                                            'accepted' => 'primary',
                                            'on_survey' => 'info',
                                            'survey_priced' => 'purple',
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'cancelled' => 'secondary',
                                        ];
                                    @endphp
                                    @forelse ($surveys as $survey)
                                        <tr>
                                            <td>{{ $surveys->firstItem() + $loop->index }}</td>
                                            <td>{{ $survey->customer?->name ?? '-' }}</td>
                                            <td>{{ $survey->tukang?->name ?? '-' }}</td>
                                            <td>{{ $survey->service?->name ?? '-' }}</td>
                                            <td>{{ $survey->survey_date?->format('d M Y') ?? '-' }}</td>
                                            <td>
                                                {{ $survey->estimated_price ? 'Rp ' . number_format($survey->estimated_price, 0, ',', '.') : '-' }}
                                            </td>
                                            <td>
                                                <span
                                                    class="badge badge-{{ $statusBadge[$survey->status] ?? 'secondary' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $survey->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.surveys.show', $survey) }}"
                                                    class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="fas fa-clipboard-list fa-2x mb-2 d-block"></i>
                                                Belum ada survey
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Menampilkan {{ $surveys->firstItem() ?? 0 }}–{{ $surveys->lastItem() ?? 0 }}
                                dari {{ $surveys->total() }} survey
                            </div>
                            {{ $surveys->links() }}
                        </div>

                    </div>
                </div>
            </div>

        </section>
    </div>
@endsection
