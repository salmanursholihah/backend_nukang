@extends('layouts.app')

@section('title', 'Tambah Service')

@section('main')
    <div class="main-content">
        <section class="section">

            <div class="section-header">
                <h1>Tambah Service</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.services.index') }}">Services</a></div>
                    <div class="breadcrumb-item active">Tambah</div>
                </div>
            </div>

            <div class="section-body">
                <div class="card">
                    <div class="card-header">
                        <h4>Form Tambah Service</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.services.store') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Kategori <span class="text-danger">*</span></label>
                                        <select name="category_id"
                                            class="form-control @error('category_id') is-invalid @enderror" required>
                                            <option value="">-- Pilih Kategori --</option>
                                            @foreach ($categories as $cat)
                                                <option value="{{ $cat->id }}"
                                                    {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                                    {{ $cat->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('category_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nama Service <span class="text-danger">*</span></label>
                                        <input type="text" name="name"
                                            class="form-control @error('name') is-invalid @enderror"
                                            value="{{ old('name') }}" placeholder="contoh: Pasang Stop Kontak" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Deskripsi</label>
                                <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
                                    placeholder="Deskripsi singkat service ini...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Harga Dasar (Rp)</label>
                                        <input type="number" name="base_price"
                                            class="form-control @error('base_price') is-invalid @enderror"
                                            value="{{ old('base_price') }}" min="0" placeholder="0">
                                        @error('base_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Harga minimal / estimasi awal</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Harga Per Satuan (Rp)</label>
                                        <input type="number" name="price_per_unit"
                                            class="form-control @error('price_per_unit') is-invalid @enderror"
                                            value="{{ old('price_per_unit') }}" min="0" placeholder="0">
                                        @error('price_per_unit')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Satuan</label>
                                        <input type="text" name="unit"
                                            class="form-control @error('unit') is-invalid @enderror"
                                            value="{{ old('unit') }}" placeholder="meter / jam / titik / unit">
                                        @error('unit')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Thumbnail</label>
                                <div class="mb-2">
                                    <img id="thumbPreview" src="#" alt="Preview" class="rounded d-none"
                                        style="width:100px;height:100px;object-fit:cover;">
                                </div>
                                <input type="file" name="thumbnail"
                                    class="form-control-file @error('thumbnail') is-invalid @enderror" accept="image/*"
                                    onchange="previewThumb(this)">
                                @error('thumbnail')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Format: JPG, PNG, WEBP. Maks 2MB.</small>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="is_active" value="1" class="custom-control-input"
                                        id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">Aktif</label>
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Simpan
                                </button>
                                <a href="{{ route('admin.services.index') }}" class="btn btn-secondary ml-2">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

        </section>
    </div>
@endsection

@push('scripts')
    <script>
        function previewThumb(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    const preview = document.getElementById('thumbPreview');
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endpush
