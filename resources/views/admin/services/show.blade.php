{{-- ============================================================ --}}
{{-- resources/views/admin/services/show.blade.php              --}}
{{-- ============================================================ --}}
@extends('layouts.app')

@section('title', 'Detail Service')

@section('main')
    <div class="main-content">
        <section class="section">

            <div class="section-header">
                <h1>Detail Service</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.services.index') }}">Services</a></div>
                    <div class="breadcrumb-item active">Detail</div>
                </div>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                @if ($service->thumbnail)
                                    <img src="{{ asset($service->thumbnail) }}" class="rounded mb-3"
                                        style="width:120px;height:120px;object-fit:cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center mx-auto mb-3"
                                        style="width:120px;height:120px;">
                                        <i class="fas fa-tools fa-3x text-muted"></i>
                                    </div>
                                @endif

                                <h5>{{ $service->name }}</h5>
                                <div class="mb-2">
                                    <span class="badge badge-light border">{{ $service->category?->name }}</span>
                                    <span class="badge badge-{{ $service->is_active ? 'success' : 'danger' }} ml-1">
                                        {{ $service->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>

                                <table class="table table-sm table-borderless text-left mt-3">
                                    <tr>
                                        <th>Slug</th>
                                        <td><code>{{ $service->slug }}</code></td>
                                    </tr>
                                    <tr>
                                        <th>Harga Dasar</th>
                                        <td>
                                            {{ $service->base_price ? 'Rp ' . number_format($service->base_price, 0, ',', '.') : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Per Satuan</th>
                                        <td>
                                            {{ $service->price_per_unit ? 'Rp ' . number_format($service->price_per_unit, 0, ',', '.') : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Satuan</th>
                                        <td>{{ $service->unit ?? '-' }}</td>
                                    </tr>
                                </table>

                                <div class="mt-3">
                                    <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.services.destroy', $service) }}"
                                        class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Hapus service ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h4>Deskripsi</h4>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">{{ $service->description ?? 'Tidak ada deskripsi.' }}</p>
                            </div>
                        </div>

                        <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>

        </section>
    </div>
@endsection
