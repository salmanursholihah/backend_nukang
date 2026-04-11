@extends('layouts.app')

@section('title', 'Survey Management')

@section('main')

<section class="section">

    <div class="section-header">
        <h1>Survey Management</h1>
    </div>

    <div class="section-body">

        <a href="{{ route('surveys.create') }}" class="btn btn-primary mb-3">
            Add Survey
        </a>

        <div class="card">

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered">

                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Customer</th>
                                <th>Tukang</th>
                                <th>Service</th>
                                <th>Address</th>
                                <th>Survey Date</th>
                                <th>Survey Fee</th>
                                <th>Estimated Price</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach ($surveys as $index => $survey)
                                <tr>
                                    <td>{{ $surveys->firstItem() + $index }}</td>

                                    <td>{{ $survey->customer->name ?? '-' }}</td>

                                    <td>{{ $survey->tukang->name ?? '-' }}</td>

                                    <td>{{ $survey->service->name ?? '-' }}</td>

                                    <td>{{ $survey->address }}</td>

                                    <td>{{ $survey->survey_date }}</td>

                                    <td>
                                        Rp {{ number_format($survey->survey_fee, 0, ',', '.') }}
                                    </td>

                                    <td>
                                        Rp {{ number_format($survey->estimated_price, 0, ',', '.') }}
                                    </td>

                                    <td>
                                        <span class="badge badge-info">
                                            {{ $survey->status }}
                                        </span>
                                    </td>

                                    <td>

                                        <a href="{{ route('surveys.edit', $survey->id) }}"
                                            class="btn btn-warning btn-sm">
                                            Edit
                                        </a>

                                        <form action="{{ route('surveys.destroy', $survey->id) }}"
                                            method="POST"
                                            style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-danger btn-sm"
                                                onclick="return confirm('Delete survey?')">
                                                Delete
                                            </button>
                                        </form>

                                    </td>
                                </tr>
                            @endforeach

                        </tbody>

                    </table>

                </div>

                {{ $surveys->links() }}

            </div>

        </div>

    </div>
</section>

@endsection
