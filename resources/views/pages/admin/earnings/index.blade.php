@extends('layouts.app')

@section('title', 'Earnings Management')

@section('main')

    <div class="section-header">
        <h1>Earnings Management</h1>
    </div>

    <div class="section-body">

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card">

            <div class="card-header">
                <h4>Partner Earnings</h4>
            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered table-striped">

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
                                        @if ($earning->status == 'paid')
                                            <span class="badge badge-success">Paid</span>
                                        @else
                                            <span class="badge badge-warning">Pending</span>
                                        @endif
                                    </td>

                                    <td>

                                        {{-- Edit --}}
                                        <button class="btn btn-warning btn-sm" data-toggle="modal"
                                            data-target="#editModal{{ $earning->id }}">
                                            Edit
                                        </button>

                                        {{-- Pay --}}
                                        <a href="{{ route('earnings.pay', $earning->id) }}" class="btn btn-success btn-sm">
                                            Pay
                                        </a>

                                        {{-- Delete --}}
                                        <form action="{{ route('earnings.destroy', $earning->id) }}" method="POST"
                                            style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-danger btn-sm"
                                                onclick="return confirm('Delete earning?')">
                                                Delete
                                            </button>
                                        </form>

                                    </td>
                                </tr>

                                {{-- Edit Modal --}}
                                <div class="modal fade" id="editModal{{ $earning->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">

                                            <form action="{{ route('earnings.update', $earning->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')

                                                <div class="modal-header">
                                                    <h5>Edit Earning</h5>
                                                    <button type="button" class="close" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>

                                                <div class="modal-body">

                                                    <div class="form-group">
                                                        <label>Gross Amount</label>
                                                        <input type="number" name="gross_amount" class="form-control"
                                                            value="{{ $earning->gross_amount }}">
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Platform Fee</label>
                                                        <input type="number" name="platform_fee" class="form-control"
                                                            value="{{ $earning->platform_fee }}">
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Net Amount</label>
                                                        <input type="number" name="net_amount" class="form-control"
                                                            value="{{ $earning->net_amount }}">
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Status</label>
                                                        <select name="status" class="form-control">
                                                            <option value="pending"
                                                                {{ $earning->status == 'pending' ? 'selected' : '' }}>
                                                                Pending
                                                            </option>
                                                            <option value="paid"
                                                                {{ $earning->status == 'paid' ? 'selected' : '' }}>
                                                                Paid
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
                    {{ $earnings->links() }}
                </div>

            </div>

        </div>

    </div>

@endsection
