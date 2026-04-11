@extends('layouts.app')

@section('title', 'Edit Review')

@section('main')
<section class="section">

    <div class="section-header">
        <h1>Edit Review</h1>
    </div>

    <div class="card">
        <div class="card-body">

            <form action="{{ route('reviews.update', $review->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Rating</label>
                    <input type="number" min="1" max="5"
                        name="rating"
                        class="form-control"
                        value="{{ $review->rating }}">
                </div>

                <div class="form-group">
                    <label>Comment</label>
                    <textarea name="comment" class="form-control">{{ $review->comment }}</textarea>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="is_visible" class="form-control">
                        <option value="1" {{ $review->is_visible ? 'selected' : '' }}>
                            Visible
                        </option>
                        <option value="0" {{ !$review->is_visible ? 'selected' : '' }}>
                            Hidden
                        </option>
                    </select>
                </div>

                <button class="btn btn-primary">Update</button>

            </form>

        </div>
    </div>
</section>
@endsection
