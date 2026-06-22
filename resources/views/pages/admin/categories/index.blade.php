@extends('layouts.app')

@section('title', 'Category Management')

@section('main')
<section class="section">

    <div class="section-header">
        <h1>Category Management</h1>
    </div>

    <div class="section-body">

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card">

            <div class="card-header">
                <h4>Category List</h4>

                <div class="card-header-action">
                    <a href="{{ route('categories.create') }}" class="btn btn-primary">
                        + Add Category
                    </a>
                </div>
            </div>

            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Name</th>
                                <th>Icon</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($categories as $index => $category)
                                <tr>
                                    <td>{{ $categories->firstItem() + $index }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ $category->icon }}</td>
                                    <td>

                                        <a href="{{ route('categories.edit', $category->id) }}"
                                           class="btn btn-warning btn-sm">
                                            Edit
                                        </a>

                                        <form action="{{ route('categories.destroy', $category->id) }}"
                                              method="POST"
                                              style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-danger btn-sm"
                                                onclick="return confirm('Delete category?')">
                                                Delete
                                            </button>
                                        </form>

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No Data</td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                <div class="mt-3">
                    {{ $categories->links() }}
                </div>

            </div>
        </div>
    </div>

</section>
@endsection
