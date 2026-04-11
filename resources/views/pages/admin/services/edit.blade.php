@extends('layouts.app')

@section('title', 'Edit Service')

@section('main')
<section class="section">

    <div class="section-header">
        <h1>Edit Service</h1>
    </div>

    <div class="section-body">

        <div class="card">
            <div class="card-body">

                <form action="{{ route('services.update', $service->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_id" class="form-control" required>

                            @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ $service->category_id == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    <div class="form-group">
                        <label>Name</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               value="{{ old('name', $service->name) }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description"
                                  class="form-control">{{ old('description', $service->description) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Price</label>
                        <input type="number"
                               name="price"
                               class="form-control"
                               value="{{ old('price', $service->price) }}"
                               required>
                    </div>

                    <button class="btn btn-primary">Update</button>
                    <a href="{{ route('services.index') }}" class="btn btn-secondary">Back</a>

                </form>

            </div>
        </div>

    </div>

</section>
@endsection
