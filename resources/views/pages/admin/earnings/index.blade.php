@extends('layouts.app')

@section('title', 'Earnings Management')

@section('main')
    <section class="section">
        <div class="section-header">
            <h1>Earnings Management</h1>
        </div>

        <div class="card">

            <div class="card-header">
                <h4>Partner Earnings</h4>

                <div class="card-header-action">
                    <a href="{{ route('earnings.create') }}" class="btn btn-primary">
                        Add Earning
                    </a>
                </div>
            </div>

            <div class="card-body">

                <table class="table table-bordered">

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Order</th>
                            <th>Tukang</th>
                            <th>Gross</th>
                            <th>Platform Fee</th>
                            <th>Net</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($earnings as $index => $earning)
                            <tr>
                                <td>{{ $earnings->firstItem() + $index }}</td>
                                <td>#{{ $earning->order_id }}</td>
                                <td>{{ $earning->tukang->user->name ?? '-' }}</td>
                                <td>Rp {{ number_format($earning->gross_amount) }}</td>
                                <td>Rp {{ number_format($earning->platform_fee) }}</td>
                                <td>Rp {{ number_format($earning->net_amount) }}</td>

                                <td>
                                    <span class="badge badge-{{ $earning->status == 'paid' ? 'success' : 'warning' }}">
                                        {{ ucfirst($earning->status) }}
                                    </span>
                                </td>

                                <td>
                                    <a href="{{ route('earnings.edit', $earning->id) }}" class="btn btn-warning btn-sm">
                                        Edit
                                    </a>

                                    <a href="{{ route('earnings.pay', $earning->id) }}" class="btn btn-success btn-sm">
                                        Pay
                                    </a>

                                    <form action="{{ route('earnings.destroy', $earning->id) }}" method="POST"
                                        style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-danger btn-sm" onclick="return confirm('Delete earning?')">
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

                {{ $earnings->links() }}

            </div>
        </div>
    </section>
@endsection
