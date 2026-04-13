@extends('layouts.app')

@section('title', 'Reviews')

@section('main')
    <div class="main-content">
        <section class="section">

            <div class="section-header">
                <h1>Reviews</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active">Reviews</div>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            <div class="section-body">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Reviews</h4>
                    </div>
                    <div class="card-body">

                        {{-- Filter --}}
                        <form method="GET" action="{{ route('admin.reviews.index') }}" class="mb-4">
                            <div class="row">
                                <div class="col-md-2">
                                    <select name="rating" class="form-control">
                                        <option value="">Semua Rating</option>
                                        @for ($i = 5; $i >= 1; $i--)
                                            <option value="{{ $i }}"
                                                {{ request('rating') == $i ? 'selected' : '' }}>
                                                {{ $i }} Bintang
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="is_published" class="form-control">
                                        <option value="">Semua</option>
                                        <option value="1" {{ request('is_published') === '1' ? 'selected' : '' }}>
                                            Published</option>
                                        <option value="0" {{ request('is_published') === '0' ? 'selected' : '' }}>
                                            Hidden</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary btn-block">
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
                                        <th>No. Order</th>
                                        <th>Rating</th>
                                        <th>Komentar</th>
                                        <th>Status</th>
                                        <th>Tgl</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($reviews as $review)
                                        <tr>
                                            <td>{{ $reviews->firstItem() + $loop->index }}</td>
                                            <td>{{ $review->customer?->name ?? '-' }}</td>
                                            <td>{{ $review->tukang?->name ?? '-' }}</td>
                                            <td>
                                                @if ($review->order)
                                                    <a href="{{ route('admin.orders.show', $review->order) }}">
                                                        {{ $review->order->order_number }}
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"
                                                            style="font-size:12px;"></i>
                                                    @endfor
                                                    <span class="ml-1 font-weight-bold">{{ $review->rating }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="d-inline-block text-truncate" style="max-width:200px;"
                                                    title="{{ $review->comment }}">
                                                    {{ $review->comment ?? '-' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge badge-{{ $review->is_published ? 'success' : 'secondary' }}">
                                                    {{ $review->is_published ? 'Published' : 'Hidden' }}
                                                </span>
                                            </td>
                                            <td>{{ $review->created_at->format('d M Y') }}</td>
                                            <td>
                                                <a href="{{ route('admin.reviews.show', $review) }}"
                                                    class="btn btn-sm btn-info" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                {{-- Toggle publish --}}
                                                <form method="POST"
                                                    action="{{ route('admin.reviews.unpublish', $review) }}"
                                                    class="d-inline">
                                                    @csrf @method('PUT')
                                                    <button type="submit"
                                                        class="btn btn-sm btn-{{ $review->is_published ? 'secondary' : 'success' }}"
                                                        title="{{ $review->is_published ? 'Sembunyikan' : 'Publish' }}"
                                                        onclick="return confirm('{{ $review->is_published ? 'Sembunyikan' : 'Publish' }} review ini?')">
                                                        <i
                                                            class="fas fa-{{ $review->is_published ? 'eye-slash' : 'eye' }}"></i>
                                                    </button>
                                                </form>

                                                {{-- Hapus --}}
                                                <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}"
                                                    class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus"
                                                        onclick="return confirm('Hapus review ini permanen?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <i class="fas fa-star fa-2x mb-2 d-block"></i>
                                                Belum ada review
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Menampilkan {{ $reviews->firstItem() ?? 0 }}–{{ $reviews->lastItem() ?? 0 }}
                                dari {{ $reviews->total() }} review
                            </div>
                            {{ $reviews->links() }}
                        </div>

                    </div>
                </div>
            </div>

        </section>
    </div>
@endsection
