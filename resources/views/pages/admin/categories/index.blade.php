@extends('layouts.app')

@section('title', 'Category Management')

@section('main')
<div class="section-header">
    <h1>Category Management</h1>
</div>

<div class="section-body">

    {{-- Success Alert --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Add Button --}}
    <div class="card mb-4">
        <div class="card-header">
            <h4>Category List</h4>

            <div class="card-header-action">
                <button class="btn btn-primary" data-toggle="modal" data-target="#createModal">
                    + Add Category
                </button>
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

                                    {{-- Edit --}}
                                    <button class="btn btn-warning btn-sm"
                                        data-toggle="modal"
                                        data-target="#editModal{{ $category->id }}">
                                        Edit
                                    </button>

                                    {{-- Delete --}}
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

                            {{-- Edit Modal --}}
                            <div class="modal fade" id="editModal{{ $category->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">

                                        <form action="{{ route('categories.update', $category->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')

                                            <div class="modal-header">
                                                <h5>Edit Category</h5>
                                                <button type="button" class="close" data-dismiss="modal">
                                                    <span>&times;</span>
                                                </button>
                                            </div>

                                            <div class="modal-body">

                                                <div class="form-group">
                                                    <label>Name</label>
                                                    <input type="text"
                                                           name="name"
                                                           class="form-control"
                                                           value="{{ $category->name }}"
                                                           required>
                                                </div>

                                                <div class="form-group">
                                                    <label>Icon</label>
                                                    <input type="text"
                                                           name="icon"
                                                           class="form-control"
                                                           value="{{ $category->icon }}">
                                                </div>

                                            </div>

                                            <div class="modal-footer">
                                                <button class="btn btn-primary">Update</button>
                                            </div>

                                        </form>

                                    </div>
                                </div>
                            </div>

                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No Data</td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $categories->links() }}
            </div>

        </div>
    </div>
</div>

{{-- Create Modal --}}
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <form action="{{ route('categories.store') }}" method="POST">
                @csrf

                <div class="modal-header">
                    <h5>Add Category</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>Name</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Icon</label>
                        <input type="text"
                               name="icon"
                               class="form-control">
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">Save</button>
                </div>

            </form>

        </div>
    </div>
</div>

@endsection
