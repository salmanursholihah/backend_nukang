@extends('layouts.app')

@section('title', 'Review Management')

@section('main')
<section class="section">

    <div class="section-header">
        <h1>Review Management</h1>
    </div>

    <div class="card">

        <div class="card-header">
            <h4>Customer Reviews</h4>

            <div class="card-header-action">
                <a href="{{ route('reviews.create') }}" class="btn btn-primary">
                    Add Review
                </a>
            </div>
        </div>

        <div class="card-body">

            <table class="table table-bordered">

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
                                <span class="badge badge-{{ $review->is_visible ? 'success' : 'danger' }}">
                                    {{ $review->is_visible ? 'Visible' : 'Hidden' }}
                                </span>
                            </td>

                            <td>
                                <a href="{{ route('reviews.edit', $review->id) }}"
                                    class="btn btn-warning btn-sm">
                                    Edit
                                </a>

                                <a href="{{ route('reviews.hide', $review->id) }}"
                                    class="btn btn-secondary btn-sm">
                                    Hide
                                </a>

                                <a href="{{ route('reviews.showReview', $review->id) }}"
                                    class="btn btn-success btn-sm">
                                    Show
                                </a>

                                <form action="{{ route('reviews.destroy', $review->id) }}"
                                    method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-danger btn-sm"
                                        onclick="return confirm('Delete review?')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No Data</td>
                        </tr>
                    @endforelse

                </tbody>

            </table>

            {{ $reviews->links() }}

        </div>
    </div>
</section>
@endsection
