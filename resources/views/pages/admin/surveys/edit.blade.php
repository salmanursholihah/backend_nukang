@extends('layouts.app')

@section('title', 'Edit Survey')

@section('main')

<section class="section">

    <div class="section-header">
        <h1>Edit Survey</h1>
    </div>

    <div class="card">
        <div class="card-body">

            <form action="{{ route('surveys.update', $survey->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Customer</label>
                    <select name="customer_id" class="form-control">
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}"
                                {{ $survey->customer_id == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Tukang</label>
                    <select name="tukang_id" class="form-control">
                        @foreach ($tukangs as $tukang)
                            <option value="{{ $tukang->id }}"
                                {{ $survey->tukang_id == $tukang->id ? 'selected' : '' }}>
                                {{ $tukang->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Service</label>
                    <select name="service_id" class="form-control">
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}"
                                {{ $survey->service_id == $service->id ? 'selected' : '' }}>
                                {{ $service->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" class="form-control">{{ $survey->address }}</textarea>
                </div>

                <div class="form-group">
                    <label>Survey Date</label>
                    <input type="datetime-local"
                        name="survey_date"
                        class="form-control"
                        value="{{ $survey->survey_date ? date('Y-m-d\TH:i', strtotime($survey->survey_date)) : '' }}">
                </div>

                <div class="form-group">
                    <label>Survey Fee</label>
                    <input type="number"
                        name="survey_fee"
                        class="form-control"
                        value="{{ $survey->survey_fee }}">
                </div>

                <div class="form-group">
                    <label>Estimated Price</label>
                    <input type="number"
                        name="estimated_price"
                        class="form-control"
                        value="{{ $survey->estimated_price }}">
                </div>

                <div class="form-group">
                    <label>Estimated Days</label>
                    <input type="number"
                        name="estimated_days"
                        class="form-control"
                        value="{{ $survey->estimated_days }}">
                </div>

                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" class="form-control">{{ $survey->notes }}</textarea>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">

                        <option value="requested" {{ $survey->status == 'requested' ? 'selected' : '' }}>
                            Requested
                        </option>

                        <option value="accepted" {{ $survey->status == 'accepted' ? 'selected' : '' }}>
                            Accepted
                        </option>

                        <option value="rejected" {{ $survey->status == 'rejected' ? 'selected' : '' }}>
                            Rejected
                        </option>

                        <option value="survey_priced" {{ $survey->status == 'survey_priced' ? 'selected' : '' }}>
                            Survey Priced
                        </option>

                        <option value="estimated" {{ $survey->status == 'estimated' ? 'selected' : '' }}>
                            Estimated
                        </option>

                        <option value="approved" {{ $survey->status == 'approved' ? 'selected' : '' }}>
                            Approved
                        </option>

                        <option value="cancelled" {{ $survey->status == 'cancelled' ? 'selected' : '' }}>
                            Cancelled
                        </option>

                    </select>
                </div>

                <button class="btn btn-primary">
                    Update Survey
                </button>

            </form>

        </div>
    </div>

</section>

@endsection
