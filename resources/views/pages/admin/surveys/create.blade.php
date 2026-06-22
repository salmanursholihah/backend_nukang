@extends('layouts.app')

@section('title', 'Create Survey')

@section('main')

<section class="section">

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
                    <label>Service</label>
                    <select name="service_id" class="form-control">
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" class="form-control"></textarea>
                </div>

                <div class="form-group">
                    <label>Survey Date</label>
                    <input type="datetime-local" name="survey_date" class="form-control">
                </div>

                <div class="form-group">
                    <label>Survey Fee</label>
                    <input type="number" name="survey_fee" class="form-control">
                </div>

                <div class="form-group">
                    <label>Estimated Price</label>
                    <input type="number" name="estimated_price" class="form-control">
                </div>

                <div class="form-group">
                    <label>Estimated Days</label>
                    <input type="number" name="estimated_days" class="form-control">
                </div>

                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" class="form-control"></textarea>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">

                        <option value="requested">Requested</option>
                        <option value="accepted">Accepted</option>
                        <option value="rejected">Rejected</option>
                        <option value="survey_priced">Survey Priced</option>
                        <option value="estimated">Estimated</option>
                        <option value="approved">Approved</option>
                        <option value="cancelled">Cancelled</option>

                    </select>
                </div>

                <button class="btn btn-primary">Save Survey</button>

            </form>

        </div>
    </div>
</section>

@endsection
