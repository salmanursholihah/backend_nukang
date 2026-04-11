@extends('layouts.app')

@section('title', 'Kategori')

@section('main')
    <div class="main-content">
        <section class="section">

            <div class="section-header">
                <h1>Kategori</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </div>
                    <div class="breadcrumb-item active">Kategori</div>
                </div>
            </div>

            {{-- Alert --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            <div class="section-body">
                <div class="card">

                    {{-- Header --}}
                    <div class="card-header">
                        <h4>Daftar Kategori</h4>
                        <div class="card-header-action">
                            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Kategori
                            </a>
                        </div>
                    </div>

                    <div class="card-body">

                        {{-- Filter --}}
                        <form method="GET" action="{{ route('admin.categories.index') }}" class="mb-4">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Cari nama kategori..." value="{{ request('search') }}">
                                </div>

                                <div class="col-md-3">
                                    <select name="is_active" class="form-control">
                                        <option value="">Semua Status</option>
                                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>
                                            Aktif
                                        </option>
                                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>
                                            Nonaktif
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                </div>

                                <div class="col-md-2">
                                    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary btn-block">
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
                                        <th>Image</th>
                                        <th>Nama</th>
                                        <th>Slug</th>
                                        <th>Jumlah Service</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($categories as $category)
                                        <tr>
                                            <td>{{ $categories->firstItem() + $loop->index }}</td>

                                            {{-- IMAGE FIX --}}
                                            <td>
                                                @if ($category->image)
                                                    <img src="{{ asset('storage/' . $category->image) }}" width="40"
                                                        height="40" class="rounded" style="object-fit:cover;">
                                                @else
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                        style="width:40px;height:40px;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                @endif
                                            </td>

                                            <td>
                                                <a href="{{ route('admin.categories.show', $category) }}">
                                                    {{ $category->name }}
                                                </a>
                                            </td>

                                            <td>
                                                <code>{{ $category->slug }}</code>
                                            </td>

                                            <td>
                                                <span class="badge badge-info">
                                                    {{ $category->services_count ?? 0 }} service
                                                </span>
                                            </td>

                                            <td>
                                                <span
                                                    class="badge badge-{{ $category->is_active ? 'success' : 'danger' }}">
                                                    {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                                </span>
                                            </td>

                                            <td>
                                                <a href="{{ route('admin.categories.show', $category) }}"
                                                    class="btn btn-sm btn-info" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                <a href="{{ route('admin.categories.edit', $category) }}"
                                                    class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <form method="POST"
                                                    action="{{ route('admin.categories.destroy', $category) }}"
                                                    class="d-inline">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Hapus kategori {{ $category->name }}?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="fas fa-list fa-2x mb-2 d-block"></i>
                                                Belum ada kategori
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Menampilkan {{ $categories->firstItem() ?? 0 }}–
                                {{ $categories->lastItem() ?? 0 }}
                                dari {{ $categories->total() }} kategori
                            </div>

                            {{ $categories->links() }}
                        </div>

                    </div>
                </div>
            </div>

        </section>
    </div>
@endsection
