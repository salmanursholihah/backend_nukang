@extends('layouts.app')

@section('title', 'Services')

@section('main')
    <div class="main-content">
        <section class="section">

            <div class="section-header">
                <h1>Services</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active">Services</div>
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
                        <h4>Daftar Services</h4>
                        <div class="card-header-action">
                            <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Service
                            </a>
                        </div>
                    </div>
                    <div class="card-body">

                        {{-- Filter --}}
                        <form method="GET" action="{{ route('admin.services.index') }}" class="mb-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <select name="category_id" class="form-control">
                                        <option value="">Semua Kategori</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}"
                                                {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                                {{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="is_active" class="form-control">
                                        <option value="">Semua Status</option>
                                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif
                                        </option>
                                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Nonaktif
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Cari nama service..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <div class="col-md-1">
                                    <a href="{{ route('admin.services.index') }}" class="btn btn-secondary btn-block">
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
                                        <th>Thumbnail</th>
                                        <th>Nama Service</th>
                                        <th>Kategori</th>
                                        <th>Harga Dasar</th>
                                        <th>Satuan</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($services as $service)
                                        <tr>
                                            <td>{{ $services->firstItem() + $loop->index }}</td>
                                            <td>
                                                @if ($service->thumbnail)
                                                    <img src="{{ asset($service->thumbnail) }}" width="50"
                                                        height="50" class="rounded" style="object-fit:cover;">
                                                @else
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                        style="width:50px;height:50px;">
                                                        <i class="fas fa-tools text-muted"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.services.show', $service) }}">
                                                    {{ $service->name }}
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge badge-light border">
                                                    {{ $service->category?->name ?? '-' }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $service->base_price ? 'Rp ' . number_format($service->base_price, 0, ',', '.') : '-' }}
                                            </td>
                                            <td>{{ $service->unit ?? '-' }}</td>
                                            <td>
                                                <span class="badge badge-{{ $service->is_active ? 'success' : 'danger' }}">
                                                    {{ $service->is_active ? 'Aktif' : 'Nonaktif' }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.services.show', $service) }}"
                                                    class="btn btn-sm btn-info" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.services.edit', $service) }}"
                                                    class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST"
                                                    action="{{ route('admin.services.destroy', $service) }}"
                                                    class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus"
                                                        onclick="return confirm('Hapus service {{ $service->name }}?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="fas fa-tools fa-2x mb-2 d-block"></i>
                                                Belum ada service
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Menampilkan {{ $services->firstItem() ?? 0 }}–{{ $services->lastItem() ?? 0 }}
                                dari {{ $services->total() }} service
                            </div>
                            {{ $services->links() }}
                        </div>

                    </div>
                </div>
            </div>

        </section>
    </div>
@endsection
