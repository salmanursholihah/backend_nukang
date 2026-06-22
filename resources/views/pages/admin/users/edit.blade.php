@extends('layouts.app')

@section('title', 'Edit User')

@section('main')

<section class="section">

    <div class="section-header">
        <h1>Edit User</h1>
    </div>

    <div class="section-body">

        <div class="card">
            <div class="card-body">

                <form action="{{ route('users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label>Name</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               value="{{ old('name', $user->name) }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email"
                               name="email"
                               class="form-control"
                               value="{{ old('email', $user->email) }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text"
                               name="phone"
                               class="form-control"
                               value="{{ old('phone', $user->phone) }}">
                    </div>

                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" class="form-control" required>
                            <option value="superadmin" {{ $user->role == 'superadmin' ? 'selected' : '' }}>Superadmin</option>
                            <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="customer" {{ $user->role == 'customer' ? 'selected' : '' }}>Customer</option>
                            <option value="tukang" {{ $user->role == 'tukang' ? 'selected' : '' }}>Tukang</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Password (optional)</label>
                        <input type="password" name="password" class="form-control">
                    </div>

                    <button class="btn btn-primary">Update</button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Back</a>

                </form>

            </div>
        </div>

    </div>

</section>

@endsection
