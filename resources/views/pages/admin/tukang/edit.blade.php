@extends('layouts.app')

@section('title', 'Edit Tukang')

@section('main')

<section class="section">

    <div class="section-header">
        <h1>Edit Tukang</h1>
    </div>

    <div class="section-body">

        <div class="card">
            <div class="card-body">

                <form action="{{ route('tukangs.update', $tukang->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label>Skill</label>
                        <input type="text"
                               name="skill"
                               class="form-control"
                               value="{{ old('skill', $tukang->skill) }}">
                    </div>

                    <div class="form-group">
                        <label>Experience</label>
                        <input type="text"
                               name="experience"
                               class="form-control"
                               value="{{ old('experience', $tukang->experience) }}">
                    </div>

                    <div class="form-group">
                        <label>Rating</label>
                        <input type="number"
                               step="0.1"
                               name="rating"
                               class="form-control"
                               value="{{ old('rating', $tukang->rating) }}">
                    </div>

                    <div class="form-group">
                        <label>Verification</label>
                        <select name="is_verified" class="form-control">
                            <option value="1" {{ $tukang->is_verified ? 'selected' : '' }}>Verified</option>
                            <option value="0" {{ !$tukang->is_verified ? 'selected' : '' }}>Pending</option>
                        </select>
                    </div>

                    <button class="btn btn-primary">Update</button>
                    <a href="{{ route('tukangs.index') }}" class="btn btn-secondary">Back</a>

                </form>

            </div>
        </div>

    </div>

</section>

@endsection
