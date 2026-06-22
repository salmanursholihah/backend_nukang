@extends('layouts.app')

@section('title', 'Edit Earning')

@section('main')
    <section class="section">
        <div class="section-header">
            <h1>Edit Earning</h1>
        </div>

        <div class="card">
            <div class="card-body">

                <form action="{{ route('earnings.update', $earning->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label>Gross Amount</label>
                        <input type="number" name="gross_amount" class="form-control" value="{{ $earning->gross_amount }}">
                    </div>

                    <div class="form-group">
                        <label>Platform Fee</label>
                        <input type="number" name="platform_fee" class="form-control" value="{{ $earning->platform_fee }}">
                    </div>

                    <div class="form-group">
                        <label>Net Amount</label>
                        <input type="number" name="net_amount" class="form-control" value="{{ $earning->net_amount }}">
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="pending" {{ $earning->status == 'pending' ? 'selected' : '' }}>
                                Pending
                            </option>
                            <option value="paid" {{ $earning->status == 'paid' ? 'selected' : '' }}>
                                Paid
                            </option>
                        </select>
                    </div>

                    <button class="btn btn-primary">Update</button>

                </form>

            </div>
        </div>
    </section>
@endsection
