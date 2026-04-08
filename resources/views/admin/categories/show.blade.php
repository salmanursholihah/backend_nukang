@extends('layouts.app')

@section('title', 'Detail Kategori')

@section('main')
    <div class="main-content">
        <section class="section">

            <div class="section-header">
                <h1>Detail Kategori</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Kategori</a></div>
                    <div class="breadcrumb-item active">Detail</div>
                </div>
            </div>

            <div class="section-body">
                <div class="row">

                    {{-- Info Kategori --}}
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                @if ($category->icon)
                                    <img src="{{ asset($category->icon) }}" class="rounded mb-3"
                                        style="width:100px;height:100px;object-fit:cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center mx-auto mb-3"
                                        style="width:100px;height:100px;">
                                        <i class="fas fa-list fa-3x text-muted"></i>
                                    </div>
                                @endif

                                <h5>{{ $category->name }}</h5>
                                <code class="text-muted">{{ $category->slug }}</code>
                                <div class="mt-2">
                                    <span class="badge badge-{{ $category->is_active ? 'success' : 'danger' }}">
                                        {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                    <span class="badge badge-info ml-1">
                                        {{ $category->services->count() }} service
                                    </span>
                                </div>
                                <p class="text-muted small mt-2">
                                    Dibuat: {{ $category->created_at->format('d M Y') }}
                                </p>

                                <div class="mt-3">
                                    <a href="{{ route('admin.categories.edit', $category) }}"
                                        class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                        class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Hapus kategori ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Daftar Services --}}
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h4>Services dalam Kategori Ini</h4>
                                <div class="card-header-action">
                                    <a href="{{ route('admin.services.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Tambah Service
                                    </a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Nama Service</th>
                                                <th>Harga Dasar</th>
                                                <th>Satuan</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($category->services as $service)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $service->name }}</td>
                                                    <td>
                                                        {{ $service->base_price ? 'Rp ' . number_format($service->base_price, 0, ',', '.') : '-' }}
                                                    </td>
                                                    <td>{{ $service->unit ?? '-' }}</td>
                                                    <td>
                                                        <span
                                                            class="badge badge-{{ $service->is_active ? 'success' : 'danger' }}">
                                                            {{ $service->is_active ? 'Aktif' : 'Nonaktif' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.services.show', $service) }}"
                                                            class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('admin.services.edit', $service) }}"
                                                            class="btn btn-sm btn-warning">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-4">
                                                        Belum ada service di kategori ini
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                </div>
            </div>

        </section>
    </div>
@endsection
