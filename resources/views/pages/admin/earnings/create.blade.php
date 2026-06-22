@extends('layouts.app')

@section('title', 'Create Earning')

@section('main')
    <section class="section">
        <div class="section-header">
            <h1>Create Earning</h1>
        </div>

        <div class="card">
            <div class="card-body">

                <form action="{{ route('earnings.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label>Order ID</label>
                        <input type="number" name="order_id" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Tukang ID</label>
                        <input type="number" name="tukang_id" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Gross Amount</label>
                        <input type="number" name="gross_amount" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Platform Fee</label>
                        <input type="number" name="platform_fee" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Net Amount</label>
                        <input type="number" name="net_amount" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>

                    <button class="btn btn-primary">Save</button>

                </form>

            </div>
        </div>
    </section>
@endsection
