@extends('layouts.app')

@section('title', 'User Management')

@section('main')

    <div class="section-header">
        <h1>User Management</h1>
    </div>

    <div class="section-body">

        {{-- Alert Success --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card">

            <div class="card-header">
                <h4>User List</h4>

                <div class="card-header-action">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#createModal">
                        + Add User
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
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($users as $index => $user)
                                <tr>
                                    <td>{{ $users->firstItem() + $index }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->phone }}</td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>

                                    <td>

                                        {{-- Edit --}}
                                        <button class="btn btn-warning btn-sm" data-toggle="modal"
                                            data-target="#editModal{{ $user->id }}">
                                            Edit
                                        </button>

                                        {{-- Delete --}}
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                            style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-danger btn-sm" onclick="return confirm('Delete user?')">
                                                Delete
                                            </button>
                                        </form>

                                    </td>
                                </tr>

                                {{-- Edit Modal --}}
                                <div class="modal fade" id="editModal{{ $user->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">

                                            <form action="{{ route('users.update', $user->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')

                                                <div class="modal-header">
                                                    <h5>Edit User</h5>
                                                    <button type="button" class="close" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>

                                                <div class="modal-body">

                                                    <div class="form-group">
                                                        <label>Name</label>
                                                        <input type="text" name="name" class="form-control"
                                                            value="{{ $user->name }}" required>
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Email</label>
                                                        <input type="email" name="email" class="form-control"
                                                            value="{{ $user->email }}" required>
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Phone</label>
                                                        <input type="text" name="phone" class="form-control"
                                                            value="{{ $user->phone }}">
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Role</label>
                                                        <select name="role" class="form-control">
                                                            <option value="customer"
                                                                {{ $user->role == 'customer' ? 'selected' : '' }}>Customer
                                                            </option>
                                                            <option value="tukang"
                                                                {{ $user->role == 'tukang' ? 'selected' : '' }}>Tukang
                                                            </option>
                                                            <option value="admin"
                                                                {{ $user->role == 'admin' ? 'selected' : '' }}>Admin
                                                            </option>
                                                            <option value="superadmin"
                                                                {{ $user->role == 'superadmin' ? 'selected' : '' }}>Super
                                                                Admin</option>
                                                        </select>
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
                    {{ $users->links() }}
                </div>

            </div>

        </div>

    </div>

    {{-- Create Modal --}}
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <form action="{{ route('users.store') }}" method="POST">
                    @csrf

                    <div class="modal-header">
                        <h5>Add User</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">

                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Role</label>
                            <select name="role" class="form-control" required>
                                <option value="customer">Customer</option>
                                <option value="tukang">Tukang</option>
                                <option value="admin">Admin</option>
                                <option value="superadmin">Super Admin</option>
                            </select>
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
