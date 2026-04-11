@extends('layouts.app')

@section('title', 'Detail User')

@section('main')
    <div class="main-content">
        <section class="section">

            <div class="section-header">
                <h1>Detail User</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></div>
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

            <div class="section-body">
                <div class="row">

                    {{-- Info User --}}
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                @if ($user->avatar)
                                    <img src="{{ asset($user->avatar) }}" class="rounded-circle mb-3" width="100"
                                        height="100" style="object-fit:cover;">
                                @else
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3"
                                        style="width:100px;height:100px;font-size:36px;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif

                                <h5 class="mb-1">{{ $user->name }}</h5>
                                <div class="mb-2">
                                    @php $roleBadge = ['admin' => 'danger', 'tukang' => 'warning', 'customer' => 'primary']; @endphp
                                    <span class="badge badge-{{ $roleBadge[$user->role] ?? 'secondary' }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                    <span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }} ml-1">
                                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>
                                <p class="text-muted mb-1"><i class="fas fa-envelope mr-1"></i> {{ $user->email }}</p>
                                <p class="text-muted mb-3"><i class="fas fa-phone mr-1"></i> {{ $user->phone ?? '-' }}</p>
                                <p class="text-muted small">Bergabung: {{ $user->created_at->format('d M Y') }}</p>

                                <div class="mt-3">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>

                                    {{-- Toggle Aktif --}}
                                    <form method="POST" action="{{ route('admin.users.toggle', $user) }}"
                                        class="d-inline">
                                        @csrf @method('PUT')
                                        <button type="submit"
                                            class="btn btn-sm btn-{{ $user->is_active ? 'secondary' : 'success' }}"
                                            onclick="return confirm('{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} user ini?')">
                                            <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                                            {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>

                                    {{-- Verifikasi tukang --}}
                                    @if ($user->isTukang())
                                        <form method="POST" action="{{ route('admin.users.verify', $user) }}"
                                            class="d-inline mt-1">
                                            @csrf @method('PUT')
                                            <button type="submit"
                                                class="btn btn-sm btn-{{ $user->tukangProfile?->is_verified ? 'danger' : 'success' }}"
                                                onclick="return confirm('{{ $user->tukangProfile?->is_verified ? 'Batalkan verifikasi' : 'Verifikasi' }} tukang ini?')">
                                                <i
                                                    class="fas fa-{{ $user->tukangProfile?->is_verified ? 'times' : 'check-double' }}"></i>
                                                {{ $user->tukangProfile?->is_verified ? 'Batalkan Verifikasi' : 'Verifikasi' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Statistik --}}
                        @if ($orderStats)
                            <div class="card">
                                <div class="card-header">
                                    <h4>Statistik</h4>
                                </div>
                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Total Order</span>
                                            <strong>{{ $orderStats['total'] }}</strong>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Selesai</span>
                                            <strong class="text-success">{{ $orderStats['completed'] }}</strong>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Dibatalkan</span>
                                            <strong class="text-danger">{{ $orderStats['cancelled'] }}</strong>
                                        </li>
                                        @if ($user->isCustomer())
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Total Belanja</span>
                                                <strong>Rp {{ number_format($orderStats['spent'], 0, ',', '.') }}</strong>
                                            </li>
                                        @else
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Total Penghasilan</span>
                                                <strong class="text-success">Rp
                                                    {{ number_format($orderStats['earned'], 0, ',', '.') }}</strong>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Info Tukang (jika role tukang) --}}
                    <div class="col-md-8">
                        @if ($user->isTukang() && $user->tukangProfile)
                            @php $profile = $user->tukangProfile; @endphp
                            <div class="card">
                                <div class="card-header">
                                    <h4>Profil Tukang</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <th width="140">Kota</th>
                                                    <td>{{ $profile->city ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Provinsi</th>
                                                    <td>{{ $profile->province ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Alamat</th>
                                                    <td>{{ $profile->address ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Radius Kerja</th>
                                                    <td>{{ $profile->radius_km }} km</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <th width="140">Rating</th>
                                                    <td><i class="fas fa-star text-warning"></i> {{ $profile->rating }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Total Job</th>
                                                    <td>{{ $profile->total_jobs }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Total Review</th>
                                                    <td>{{ $profile->total_reviews }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Status</th>
                                                    <td>
                                                        <span
                                                            class="badge badge-{{ $profile->is_verified ? 'success' : 'warning' }}">
                                                            {{ $profile->is_verified ? 'Terverifikasi' : 'Belum Verifikasi' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                    @if ($profile->bio)
                                        <div class="mt-2">
                                            <strong>Bio:</strong>
                                            <p class="text-muted mt-1">{{ $profile->bio }}</p>
                                        </div>
                                    @endif

                                    {{-- Foto KTP --}}
                                    @if ($profile->id_card_photo)
                                        <div class="mt-3">
                                            <strong>Foto KTP:</strong><br>
                                            <img src="{{ asset($profile->id_card_photo) }}" class="img-fluid mt-2 rounded"
                                                style="max-width:300px;">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Services --}}
                            @if ($user->tukangServices->count() > 0)
                                <div class="card">
                                    <div class="card-header">
                                        <h4>Keahlian / Services</h4>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Service</th>
                                                        <th>Kategori</th>
                                                        <th>Harga Dasar</th>
                                                        <th>Harga Custom</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($user->tukangServices as $service)
                                                        <tr>
                                                            <td>{{ $service->name }}</td>
                                                            <td>{{ $service->category?->name ?? '-' }}</td>
                                                            <td>Rp {{ number_format($service->base_price, 0, ',', '.') }}
                                                            </td>
                                                            <td>
                                                                {{ $service->pivot->custom_price ? 'Rp ' . number_format($service->pivot->custom_price, 0, ',', '.') : '-' }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="card">
                                <div class="card-body text-center text-muted py-5">
                                    <i class="fas fa-user fa-3x mb-3 d-block"></i>
                                    <p>Tidak ada data profil tambahan untuk user ini.</p>
                                </div>
                            </div>
                        @endif

                        <div class="mt-3">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>

                </div>
            </div>

        </section>
    </div>
@endsection
