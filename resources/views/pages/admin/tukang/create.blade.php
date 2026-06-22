@extends('layouts.app')

@section('title', 'Create Tukang')

@section('main')

<section class="section">

    <div class="section-header">
        <h1>Create Tukang</h1>
    </div>

    <div class="section-body">

        <div class="card">
            <div class="card-body">

                <form action="{{ route('tukangs.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label>User</label>
                        <select name="user_id" class="form-control" required>
                            <option value="">Choose User</option>

                            @foreach($users as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Skill</label>
                        <input type="text" name="skill" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Experience</label>
                        <input type="text" name="experience" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Rating</label>
                        <input type="number" step="0.1" name="rating" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Verification</label>
                        <select name="is_verified" class="form-control">
                            <option value="1">Verified</option>
                            <option value="0">Pending</option>
                        </select>
                    </div>

                    <button class="btn btn-primary">Save</button>
                    <a href="{{ route('tukangs.index') }}" class="btn btn-secondary">Back</a>

                </form>

            </div>
        </div>

    </div>

</section>

@endsection
