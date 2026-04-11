@extends('layouts.app')

@section('title', 'Detail Survey')

@section('main')
    <div class="main-content">
        <section class="section">

            <div class="section-header">
                <h1>Detail Survey</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.surveys.index') }}">Surveys</a></div>
                    <div class="breadcrumb-item active">Detail</div>
                </div>
            </div>

            @php
                $statusBadge = [
                    'requested' => 'warning',
                    'accepted' => 'primary',
                    'on_survey' => 'info',
                    'survey_priced' => 'dark',
                    'approved' => 'success',
                    'rejected' => 'danger',
                    'cancelled' => 'secondary',
                ];
            @endphp

            <div class="section-body">
                <div class="row">

                    {{-- Kolom Kiri --}}
                    <div class="col-md-4">

                        {{-- Status --}}
                        <div class="card">
                            <div class="card-header">
                                <h4>Status Survey</h4>
                            </div>
                            <div class="card-body text-center">
                                <span class="badge badge-{{ $statusBadge[$survey->status] ?? 'secondary' }}"
                                    style="font-size:1rem;padding:.5rem 1rem;">
                                    {{ ucfirst(str_replace('_', ' ', $survey->status)) }}
                                </span>
                                <table class="table table-sm table-borderless mt-3 text-left">
                                    <tr>
                                        <th>Tgl Dibuat</th>
                                        <td>{{ $survey->created_at->format('d M Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tgl Survey</th>
                                        <td>{{ $survey->survey_date?->format('d M Y H:i') ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Biaya Survey</th>
                                        <td>{{ $survey->survey_fee ? 'Rp ' . number_format($survey->survey_fee, 0, ',', '.') : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Est. Harga</th>
                                        <td>
                                            <strong class="text-success">
                                                {{ $survey->estimated_price ? 'Rp ' . number_format($survey->estimated_price, 0, ',', '.') : '-' }}
                                            </strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Est. Hari</th>
                                        <td>{{ $survey->estimated_days ? $survey->estimated_days . ' hari' : '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        {{-- Customer --}}
                        <div class="card">
                            <div class="card-header">
                                <h4>Customer</h4>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-2"
                                        style="width:40px;height:40px;">
                                        {{ strtoupper(substr($survey->customer?->name ?? 'C', 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $survey->customer?->name ?? '-' }}</strong><br>
                                        <small class="text-muted">{{ $survey->customer?->email }}</small>
                                    </div>
                                </div>
                                <p class="mb-0"><i class="fas fa-phone mr-1 text-muted"></i>
                                    {{ $survey->customer?->phone ?? '-' }}</p>
                            </div>
                        </div>

                        {{-- Tukang --}}
                        <div class="card">
                            <div class="card-header">
                                <h4>Tukang</h4>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center mr-2"
                                        style="width:40px;height:40px;">
                                        {{ strtoupper(substr($survey->tukang?->name ?? 'T', 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $survey->tukang?->name ?? '-' }}</strong><br>
                                        <small class="text-muted">{{ $survey->tukang?->phone }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Kolom Kanan --}}
                    <div class="col-md-8">

                        {{-- Info Service & Alamat --}}
                        <div class="card">
                            <div class="card-header">
                                <h4>Detail Survey</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Service</strong>
                                        <p class="text-muted">{{ $survey->service?->name ?? '-' }}</p>
                                        <strong>Deskripsi Service</strong>
                                        <p class="text-muted">{{ $survey->service?->description ?? '-' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Alamat Survey</strong>
                                        <p class="text-muted"><i class="fas fa-map-marker-alt text-danger mr-1"></i>
                                            {{ $survey->address }}</p>
                                        @if ($survey->notes)
                                            <strong>Catatan Customer</strong>
                                            <p class="text-muted">{{ $survey->notes }}</p>
                                        @endif
                                        @if ($survey->tukang_notes)
                                            <strong>Catatan Tukang</strong>
                                            <p class="text-muted">{{ $survey->tukang_notes }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Detail Estimasi per Service --}}
                        @if ($survey->surveyServices->count() > 0)
                            <div class="card">
                                <div class="card-header">
                                    <h4>Estimasi Harga per Service</h4>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Service</th>
                                                    <th>Satuan</th>
                                                    <th class="text-right">Harga Est.</th>
                                                    <th class="text-center">Qty</th>
                                                    <th class="text-right">Subtotal</th>
                                                    <th>Catatan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($survey->surveyServices as $ss)
                                                    <tr>
                                                        <td>{{ $ss->service_name }}</td>
                                                        <td>{{ $ss->service?->unit ?? '-' }}</td>
                                                        <td class="text-right">Rp
                                                            {{ number_format($ss->estimated_price ?? 0, 0, ',', '.') }}
                                                        </td>
                                                        <td class="text-center">{{ $ss->qty }}</td>
                                                        <td class="text-right">Rp
                                                            {{ number_format(($ss->estimated_price ?? 0) * $ss->qty, 0, ',', '.') }}
                                                        </td>
                                                        <td>{{ $ss->notes ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="thead-light">
                                                <tr>
                                                    <td colspan="4" class="text-right"><strong>Total Estimasi</strong>
                                                    </td>
                                                    <td class="text-right">
                                                        <strong class="text-success">
                                                            Rp
                                                            {{ number_format($survey->estimated_price ?? 0, 0, ',', '.') }}
                                                        </strong>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Link ke Order --}}
                        @if ($survey->order)
                            <div class="card">
                                <div class="card-header">
                                    <h4>Order Terkait</h4>
                                </div>
                                <div class="card-body">
                                    <p>Survey ini sudah disetujui dan menghasilkan order:</p>
                                    <a href="{{ route('admin.orders.show', $survey->order) }}" class="btn btn-primary">
                                        <i class="fas fa-shopping-cart"></i>
                                        {{ $survey->order->order_number }}
                                        <span class="badge badge-light ml-1">{{ ucfirst($survey->order->status) }}</span>
                                    </a>
                                </div>
                            </div>
                        @endif

                        <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>

                    </div>
                </div>
            </div>

        </section>
    </div>
@endsection
