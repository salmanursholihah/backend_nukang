@extends('layouts.app')

@section('title', 'Create Category')

@section('main')
<section class="section">

    <div class="section-header">
        <h1>Create Category</h1>
    </div>

    <div class="section-body">

        <div class="card">
            <div class="card-body">

                <form action="{{ route('categories.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label>Name</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               value="{{ old('name') }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Icon</label>
                        <input type="text"
                               name="icon"
                               class="form-control"
                               value="{{ old('icon') }}">
                    </div>

                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="{{ route('categories.index') }}" class="btn btn-secondary">Back</a>

                </form>

            </div>
        </div>

    </div>

</section>
@endsection
