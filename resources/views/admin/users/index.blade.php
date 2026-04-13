@extends('layouts.app')

@section('title', 'Manajemen User')

@section('main')
    <div class="main-content">
        <section class="section">

            <div class="section-header">
                <h1>Manajemen User</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active">Users</div>
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
                        <h4>Daftar User</h4>
                        <div class="card-header-action">
                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah User
                            </a>
                        </div>
                    </div>
                    <div class="card-body">

                        {{-- Filter --}}
                        <form method="GET" action="{{ route('admin.users.index') }}" class="mb-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <select name="role" class="form-control">
                                        <option value="">Semua Role</option>
                                        <option value="customer" {{ request('role') == 'customer' ? 'selected' : '' }}>
                                            Customer</option>
                                        <option value="tukang" {{ request('role') == 'tukang' ? 'selected' : '' }}>Tukang
                                        </option>
                                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-3">
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
                                        placeholder="Cari nama, email, phone..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </form>

                        {{-- Table --}}
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Info Tukang</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($users as $user)
                                        <tr>
                                            <td>{{ $users->firstItem() + $loop->index }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if ($user->avatar)
                                                        <img src="{{ asset($user->avatar) }}" class="rounded-circle mr-2"
                                                            width="32" height="32">
                                                    @else
                                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-2"
                                                            style="width:32px;height:32px;font-size:12px;">
                                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                    <a
                                                        href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a>
                                                </div>
                                            </td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->phone ?? '-' }}</td>
                                            <td>
                                                @php
                                                    $roleBadge = [
                                                        'admin' => 'danger',
                                                        'tukang' => 'warning',
                                                        'customer' => 'primary',
                                                    ];
                                                @endphp
                                                <span class="badge badge-{{ $roleBadge[$user->role] ?? 'secondary' }}">
                                                    {{ ucfirst($user->role) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }}">
                                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($user->tukangProfile)
                                                    <small>
                                                        <i class="fas fa-star text-warning"></i>
                                                        {{ $user->tukangProfile->rating }}
                                                        &nbsp;|&nbsp;
                                                        {{ $user->tukangProfile->total_jobs }} job
                                                        &nbsp;|&nbsp;
                                                        @if ($user->tukangProfile->is_verified)
                                                            <span class="text-success"><i class="fas fa-check-circle"></i>
                                                                Verified</span>
                                                        @else
                                                            <span class="text-danger"><i class="fas fa-times-circle"></i>
                                                                Unverified</span>
                                                        @endif
                                                    </small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.users.show', $user) }}"
                                                    class="btn btn-sm btn-info" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.users.edit', $user) }}"
                                                    class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                {{-- Toggle Aktif --}}
                                                <form method="POST" action="{{ route('admin.users.toggle', $user) }}"
                                                    class="d-inline">
                                                    @csrf @method('PUT')
                                                    <button type="submit"
                                                        class="btn btn-sm btn-{{ $user->is_active ? 'secondary' : 'success' }}"
                                                        title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                        onclick="return confirm('{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} user ini?')">
                                                        <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                                                    </button>
                                                </form>

                                                {{-- Verifikasi (khusus tukang) --}}
                                                @if ($user->isTukang())
                                                    <form method="POST" action="{{ route('admin.users.verify', $user) }}"
                                                        class="d-inline">
                                                        @csrf @method('PUT')
                                                        <button type="submit"
                                                            class="btn btn-sm btn-{{ $user->tukangProfile?->is_verified ? 'danger' : 'success' }}"
                                                            title="{{ $user->tukangProfile?->is_verified ? 'Batalkan Verifikasi' : 'Verifikasi' }}"
                                                            onclick="return confirm('{{ $user->tukangProfile?->is_verified ? 'Batalkan verifikasi' : 'Verifikasi' }} tukang ini?')">
                                                            <i
                                                                class="fas fa-{{ $user->tukangProfile?->is_verified ? 'times' : 'check-double' }}"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                {{-- Hapus --}}
                                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                                    class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus"
                                                        onclick="return confirm('Hapus user {{ $user->name }}?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                                Tidak ada user ditemukan
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Menampilkan {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }}
                                dari {{ $users->total() }} user
                            </div>
                            {{ $users->links() }}
                        </div>

                    </div>
                </div>
            </div>

        </section>
    </div>
@endsection
