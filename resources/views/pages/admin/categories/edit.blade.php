@extends('layouts.app')

@section('title', 'Edit Category')

@section('main')
<section class="section">

    <div class="section-header">
        <h1>Edit Category</h1>
    </div>

    <div class="section-body">

        <div class="card">
            <div class="card-body">

                <form action="{{ route('categories.update', $category->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label>Name</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               value="{{ old('name', $category->name) }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Icon</label>
                        <input type="text"
                               name="icon"
                               class="form-control"
                               value="{{ old('icon', $category->icon) }}">
                    </div>

                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('categories.index') }}" class="btn btn-secondary">Back</a>

                </form>

            </div>
        </div>

    </div>

</section>
@endsection
