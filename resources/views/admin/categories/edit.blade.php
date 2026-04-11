@extends('layouts.app')

@section('title', 'Edit Kategori')

@section('main')
    <div class="main-content">
        <section class="section">

            <div class="section-header">
                <h1>Edit Kategori</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Kategori</a></div>
                    <div class="breadcrumb-item active">Edit</div>
                </div>
            </div>

            <div class="section-body">
                <div class="card">
                    <div class="card-header">
                        <h4>Edit: {{ $category->name }}</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.categories.update', $category) }}"
                            enctype="multipart/form-data">
                            @csrf @method('PUT')

                            <div class="form-group">
                                <label>Nama Kategori <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $category->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Icon Kategori</label>
                                <div class="mb-2">
                                    @if ($category->icon)
                                        <img id="iconPreview" src="{{ asset($category->icon) }}" class="rounded"
                                            style="width:80px;height:80px;object-fit:cover;">
                                    @else
                                        <img id="iconPreview" src="#" alt="Preview" class="rounded d-none"
                                            style="width:80px;height:80px;object-fit:cover;">
                                    @endif
                                </div>
                                <input type="file" name="icon"
                                    class="form-control-file @error('icon') is-invalid @enderror" accept="image/*"
                                    onchange="previewIcon(this)">
                                @error('icon')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Kosongkan jika tidak ingin mengubah icon.</small>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="is_active" value="1" class="custom-control-input"
                                        id="is_active" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">Aktif</label>
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Simpan Perubahan
                                </button>
                                <a href="{{ route('admin.categories.show', $category) }}" class="btn btn-secondary ml-2">
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
        function previewIcon(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    const preview = document.getElementById('iconPreview');
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endpush
