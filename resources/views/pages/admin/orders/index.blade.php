@extends('layouts.app')

@section('title', 'Order Management')

@section('main')
    <section class="section">
        <div class="section-header">
            <h1>Order Management</h1>
        </div>

        <div class="section-body">

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="card">

                <div class="card-header">
                    <h4>All Orders</h4>
                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-bordered table-striped">

                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Customer</th>
                                    <th>Tukang</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($orders as $index => $order)
                                    <tr>
                                        <td>{{ $orders->firstItem() + $index }}</td>
                                        <td>{{ $order->customer->name ?? '-' }}</td>
                                        <td>{{ $order->tukang->name ?? '-' }}</td>

                                        <td>
                                            <span class="badge badge-info">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>

                                        <td>
                                            Rp {{ number_format($order->total_price) }}
                                        </td>

                                        <td>

                                            {{-- Detail --}}
                                            <a href="{{ route('orders.show', $order->id) }}" class="btn btn-info btn-sm">
                                                Detail
                                            </a>

                                            {{-- Edit --}}
                                            <button class="btn btn-warning btn-sm" data-toggle="modal"
                                                data-target="#editModal{{ $order->id }}">
                                                Edit
                                            </button>

                                            {{-- Delete --}}
                                            <form action="{{ route('orders.destroy', $order->id) }}" method="POST"
                                                style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')

                                                <button class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Delete order?')">
                                                    Delete
                                                </button>
                                            </form>

                                        </td>
                                    </tr>

                                    {{-- Edit Modal --}}
                                    <div class="modal fade" id="editModal{{ $order->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">

                                                <form action="{{ route('orders.update', $order->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')

                                                    <div class="modal-header">
                                                        <h5>Edit Order</h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>

                                                    <div class="modal-body">

                                                        <div class="form-group">
                                                            <label>Status</label>
                                                            <select name="status" class="form-control">

                                                                <option value="pending"
                                                                    {{ $order->status == 'pending' ? 'selected' : '' }}>
                                                                    Pending
                                                                </option>

                                                                <option value="accepted"
                                                                    {{ $order->status == 'accepted' ? 'selected' : '' }}>
                                                                    Accepted
                                                                </option>

                                                                <option value="working"
                                                                    {{ $order->status == 'working' ? 'selected' : '' }}>
                                                                    Working
                                                                </option>

                                                                <option value="completed"
                                                                    {{ $order->status == 'completed' ? 'selected' : '' }}>
                                                                    Completed
                                                                </option>

                                                                <option value="cancelled"
                                                                    {{ $order->status == 'cancelled' ? 'selected' : '' }}>
                                                                    Cancelled
                                                                </option>

                                                            </select>
                                                        </div>

                                                        <div class="form-group">
                                                            <label>Total Price</label>
                                                            <input type="number" name="total_price" class="form-control"
                                                                value="{{ $order->total_price }}">
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
                                        <td colspan="6" class="text-center">No Data</td>
                                    </tr>
                                @endforelse

                            </tbody>

                        </table>

                    </div>

                    {{-- Pagination --}}
                    <div class="mt-3">
                        {{ $orders->links() }}
                    </div>

                </div>

            </div>

        </div>
    </section>

@endsection
