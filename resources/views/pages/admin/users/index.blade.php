@extends('layouts.app')

@section('title', 'User Management')

@section('main')

<section class="section">

    <div class="section-header">
        <h1>User Management</h1>
    </div>

    <div class="section-body">

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card">

            <div class="card-header">
                <h4>User List</h4>

                <div class="card-header-action">
                    <a href="{{ route('users.create') }}" class="btn btn-primary">
                        + Add User
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
                                    <td>{{ $user->role }}</td>

                                    <td>

                                        <a href="{{ route('users.edit', $user->id) }}"
                                           class="btn btn-warning btn-sm">
                                            Edit
                                        </a>

                                        <form action="{{ route('users.destroy', $user->id) }}"
                                              method="POST"
                                              style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-danger btn-sm"
                                                onclick="return confirm('Delete user?')">
                                                Delete
                                            </button>
                                        </form>

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No Data</td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

                <div class="mt-3">
                    {{ $users->links() }}
                </div>

            </div>

        </div>

    </div>

</section>

@endsection
