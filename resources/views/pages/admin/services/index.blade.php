@extends('layouts.app')

@section('title', 'Service Management')

@section('main')

    <div class="section-header">
        <h1>Service Management</h1>
    </div>

    <div class="section-body">

        {{-- Success Alert --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card">

            <div class="card-header">
                <h4>Service List</h4>

                <div class="card-header-action">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#createModal">
                        + Add Service
                    </button>
                </div>
            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Category</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($services as $index => $service)
                                <tr>
                                    <td>{{ $services->firstItem() + $index }}</td>
                                    <td>{{ $service->category->name ?? '-' }}</td>
                                    <td>{{ $service->name }}</td>
                                    <td>{{ $service->description }}</td>
                                    <td>Rp {{ number_format($service->price) }}</td>

                                    <td>

                                        {{-- Edit --}}
                                        <button class="btn btn-warning btn-sm" data-toggle="modal"
                                            data-target="#editModal{{ $service->id }}">
                                            Edit
                                        </button>

                                        {{-- Delete --}}
                                        <form action="{{ route('services.destroy', $service->id) }}" method="POST"
                                            style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-danger btn-sm"
                                                onclick="return confirm('Delete service?')">
                                                Delete
                                            </button>
                                        </form>

                                    </td>
                                </tr>

                                {{-- Edit Modal --}}
                                <div class="modal fade" id="editModal{{ $service->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">

                                            <form action="{{ route('services.update', $service->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')

                                                <div class="modal-header">
                                                    <h5>Edit Service</h5>
                                                    <button type="button" class="close" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>

                                                <div class="modal-body">

                                                    <div class="form-group">
                                                        <label>Category</label>
                                                        <select name="category_id" class="form-control" required>
                                                            @foreach ($categories as $category)
                                                                <option value="{{ $category->id }}"
                                                                    {{ $service->category_id == $category->id ? 'selected' : '' }}>
                                                                    {{ $category->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Name</label>
                                                        <input type="text" name="name" class="form-control"
                                                            value="{{ $service->name }}" required>
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Description</label>
                                                        <textarea name="description" class="form-control">{{ $service->description }}</textarea>
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Price</label>
                                                        <input type="number" name="price" class="form-control"
                                                            value="{{ $service->price }}" required>
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
                                    <td colspan="6" class="text-center">No Data</td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $services->links() }}
                </div>

            </div>

        </div>

    </div>

    {{-- Create Modal --}}
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <form action="{{ route('services.store') }}" method="POST">
                    @csrf

                    <div class="modal-header">
                        <h5>Add Service</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">

                        <div class="form-group">
                            <label>Category</label>
                            <select name="category_id" class="form-control" required>
                                <option value="">Choose Category</option>

                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->name }}
                                    </option>
                                @endforeach

                            </select>
                        </div>

                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Price</label>
                            <input type="number" name="price" class="form-control" required>
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
