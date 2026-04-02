@extends('layouts.app')

@section('title', 'Create Survey')

@section('main')

    <div class="section-header">
        <h1>Create Survey</h1>
    </div>

    <div class="card">
        <div class="card-body">

            <form action="{{ route('surveys.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>Customer</label>
                    <select name="customer_id" class="form-control">
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Tukang</label>
                    <select name="tukang_id" class="form-control">
                        @foreach ($tukangs as $tukang)
                            <option value="{{ $tukang->id }}">{{ $tukang->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Order</label>
                    <select name="order_id" class="form-control">
                        @foreach ($orders as $order)
                            <option value="{{ $order->id }}">#{{ $order->id }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Survey Date</label>
                    <input type="date" name="survey_date" class="form-control">
                </div>

                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" class="form-control">
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control"></textarea>
                </div>

                <div class="form-group">
                    <label>Estimated Price</label>
                    <input type="number" name="estimated_price" class="form-control">
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>

                <button class="btn btn-primary">Save</button>

            </form>

        </div>
    </div>

@endsection
