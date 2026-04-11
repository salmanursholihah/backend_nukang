@extends('layouts.app')

@section('title', 'Create Review')

@section('main')
<section class="section">

    <div class="section-header">
        <h1>Create Review</h1>
    </div>

    <div class="card">
        <div class="card-body">

            <form action="{{ route('reviews.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>Customer ID</label>
                    <input type="number" name="customer_id" class="form-control">
                </div>

                <div class="form-group">
                    <label>Tukang ID</label>
                    <input type="number" name="tukang_id" class="form-control">
                </div>

                <div class="form-group">
                    <label>Order ID</label>
                    <input type="number" name="order_id" class="form-control">
                </div>

                <div class="form-group">
                    <label>Rating</label>
                    <input type="number" min="1" max="5" name="rating" class="form-control">
                </div>

                <div class="form-group">
                    <label>Comment</label>
                    <textarea name="comment" class="form-control"></textarea>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="is_visible" class="form-control">
                        <option value="1">Visible</option>
                        <option value="0">Hidden</option>
                    </select>
                </div>

                <button class="btn btn-primary">Save</button>

            </form>

        </div>
    </div>
</section>
@endsection
