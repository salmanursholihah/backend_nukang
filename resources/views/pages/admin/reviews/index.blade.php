@extends('layouts.app')

@section('title', 'Review Management')

@section('main')

    <div class="section-header">
        <h1>Review Management</h1>
    </div>

    <div class="section-body">

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card">

            <div class="card-header">
                <h4>Customer Reviews</h4>
            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Customer</th>
                                <th>Tukang</th>
                                <th>Order</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($reviews as $index => $review)
                                <tr>
                                    <td>{{ $reviews->firstItem() + $index }}</td>

                                    <td>{{ $review->customer->name ?? '-' }}</td>

                                    <td>{{ $review->tukang->name ?? '-' }}</td>

                                    <td>#{{ $review->order_id }}</td>

                                    <td>{{ $review->rating }}</td>

                                    <td>{{ $review->comment }}</td>

                                    <td>
                                        @if ($review->is_visible)
                                            <span class="badge badge-success">Visible</span>
                                        @else
                                            <span class="badge badge-danger">Hidden</span>
                                        @endif
                                    </td>

                                    <td>

                                        {{-- Edit --}}
                                        <button class="btn btn-warning btn-sm" data-toggle="modal"
                                            data-target="#editModal{{ $review->id }}">
                                            Edit
                                        </button>

                                        {{-- Hide --}}
                                        <a href="{{ route('reviews.hide', $review->id) }}" class="btn btn-secondary btn-sm">
                                            Hide
                                        </a>

                                        {{-- Show --}}
                                        <a href="{{ route('reviews.showReview', $review->id) }}"
                                            class="btn btn-success btn-sm">
                                            Show
                                        </a>

                                        {{-- Delete --}}
                                        <form action="{{ route('reviews.destroy', $review->id) }}" method="POST"
                                            style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-danger btn-sm"
                                                onclick="return confirm('Delete review?')">
                                                Delete
                                            </button>
                                        </form>

                                    </td>
                                </tr>

                                {{-- Edit Modal --}}
                                <div class="modal fade" id="editModal{{ $review->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">

                                            <form action="{{ route('reviews.update', $review->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')

                                                <div class="modal-header">
                                                    <h5>Edit Review</h5>
                                                    <button type="button" class="close" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>

                                                <div class="modal-body">

                                                    <div class="form-group">
                                                        <label>Rating</label>
                                                        <input type="number" min="1" max="5" name="rating"
                                                            class="form-control" value="{{ $review->rating }}">
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Comment</label>
                                                        <textarea name="comment" class="form-control">{{ $review->comment }}</textarea>
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Status</label>
                                                        <select name="is_visible" class="form-control">
                                                            <option value="1"
                                                                {{ $review->is_visible ? 'selected' : '' }}>
                                                                Visible
                                                            </option>
                                                            <option value="0"
                                                                {{ !$review->is_visible ? 'selected' : '' }}>
                                                                Hidden
                                                            </option>
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
                                    <td colspan="8" class="text-center">No Data</td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

                <div class="mt-3">
                    {{ $reviews->links() }}
                </div>

            </div>

        </div>

    </div>

@endsection
