@extends('layouts.app')

@section('title', 'Create Service')

@section('main')
<section class="section">

    <div class="section-header">
        <h1>Create Service</h1>
    </div>

    <div class="section-body">

        <div class="card">
            <div class="card-body">

                <form action="{{ route('services.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Choose Category</option>

                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">
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
                               value="{{ old('name') }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control">{{ old('description') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Price</label>
                        <input type="number"
                               name="price"
                               class="form-control"
                               value="{{ old('price') }}"
                               required>
                    </div>

                    <button class="btn btn-primary">Save</button>
                    <a href="{{ route('services.index') }}" class="btn btn-secondary">Back</a>

                </form>

            </div>
        </div>

    </div>

</section>
@endsection
