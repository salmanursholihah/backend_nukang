@extends('layouts.app')

@section('title', 'Detail Review')

@section('main')
    <div class="main-content">
        <section class="section">

            <div class="section-header">
                <h1>Detail Review</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.reviews.index') }}">Reviews</a></div>
                    <div class="breadcrumb-item active">Detail</div>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            <div class="section-body">
                <div class="row">

                    {{-- Kiri --}}
                    <div class="col-md-4">

                        {{-- Rating --}}
                        <div class="card">
                            <div class="card-header">
                                <h4>Rating</h4>
                            </div>
                            <div class="card-body text-center">
                                <div class="mb-2">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i
                                            class="fas fa-star fa-2x {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"></i>
                                    @endfor
                                </div>
                                <div class="h2 font-weight-bold">{{ $review->rating }}<span class="text-muted h5">/5</span>
                                </div>
                                <div class="mt-2">
                                    <span class="badge badge-{{ $review->is_published ? 'success' : 'secondary' }}"
                                        style="font-size:.85rem;padding:.4rem .8rem;">
                                        {{ $review->is_published ? 'Published' : 'Hidden' }}
                                    </span>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    {{ $review->created_at->format('d M Y H:i') }}
                                </small>

                                {{-- Tags --}}
                                @if ($review->tags && count($review->tags) > 0)
                                    <div class="mt-3">
                                        @foreach ($review->tags as $tag)
                                            <span class="badge badge-light border mr-1 mb-1">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Actions --}}
                                <div class="mt-4">
                                    <form method="POST" action="{{ route('admin.reviews.unpublish', $review) }}"
                                        class="mb-2">
                                        @csrf @method('PUT')
                                        <button type="submit"
                                            class="btn btn-{{ $review->is_published ? 'secondary' : 'success' }} btn-block"
                                            onclick="return confirm('{{ $review->is_published ? 'Sembunyikan' : 'Publish' }} review ini?')">
                                            <i class="fas fa-{{ $review->is_published ? 'eye-slash' : 'eye' }}"></i>
                                            {{ $review->is_published ? 'Sembunyikan' : 'Publish' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-block"
                                            onclick="return confirm('Hapus review ini permanen? Aksi ini tidak bisa dibatalkan.')">
                                            <i class="fas fa-trash"></i> Hapus Permanen
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Customer --}}
                        <div class="card">
                            <div class="card-header">
                                <h4>Customer</h4>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    @if ($review->customer?->avatar)
                                        <img src="{{ asset($review->customer->avatar) }}" class="rounded-circle mr-2"
                                            width="40" height="40" style="object-fit:cover;">
                                    @else
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-2"
                                            style="width:40px;height:40px;">
                                            {{ strtoupper(substr($review->customer?->name ?? 'C', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <strong>{{ $review->customer?->name ?? '-' }}</strong><br>
                                        <small class="text-muted">{{ $review->customer?->email }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tukang --}}
                        <div class="card">
                            <div class="card-header">
                                <h4>Tukang Direview</h4>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center mr-2"
                                        style="width:40px;height:40px;">
                                        {{ strtoupper(substr($review->tukang?->name ?? 'T', 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $review->tukang?->name ?? '-' }}</strong><br>
                                        <small class="text-muted">{{ $review->tukang?->email }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Kanan --}}
                    <div class="col-md-8">

                        {{-- Komentar --}}
                        <div class="card">
                            <div class="card-header">
                                <h4>Komentar</h4>
                            </div>
                            <div class="card-body">
                                @if ($review->comment)
                                    <blockquote class="blockquote">
                                        <p class="mb-0">{{ $review->comment }}</p>
                                        <footer class="blockquote-footer mt-2">
                                            {{ $review->customer?->name }} &mdash;
                                            <cite>{{ $review->created_at->format('d M Y') }}</cite>
                                        </footer>
                                    </blockquote>
                                @else
                                    <p class="text-muted text-center py-3">
                                        <i class="fas fa-comment-slash fa-2x d-block mb-2"></i>
                                        Tidak ada komentar
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Order Terkait --}}
                        @if ($review->order)
                            <div class="card">
                                <div class="card-header">
                                    <h4>Order Terkait</h4>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <th width="160">No. Order</th>
                                            <td>
                                                <a href="{{ route('admin.orders.show', $review->order) }}">
                                                    {{ $review->order->order_number }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Tgl Servis</th>
                                            <td>{{ $review->order->service_date?->format('d M Y') ?? '-' }}</td>
                                        </tr>
                                    </table>
                                    <a href="{{ route('admin.orders.show', $review->order) }}"
                                        class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> Lihat Detail Order
                                    </a>
                                </div>
                            </div>
                        @endif

                        <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>

                    </div>

                </div>
            </div>

        </section>
    </div>
@endsection
