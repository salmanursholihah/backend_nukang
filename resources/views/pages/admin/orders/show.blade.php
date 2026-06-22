@extends('layouts.app')

@section('title', 'Order Detail')

@section('main')
<section class="section">
    <div class="section-header">
        <h1>Order Details</h1>
    </div>

    <div class="card">

        <div class="card-body">

            <h5>Customer: {{ $order->customer->name ?? '-' }}</h5>
            <h5>Tukang: {{ $order->tukang->name ?? '-' }}</h5>
            <h5>Status: {{ $order->status }}</h5>
            <h5>Total: Rp {{ number_format($order->total_price) }}</h5>

            <hr>

            <h5>Services:</h5>

            <ul>
                @foreach ($order->details as $detail)
                    <li>
                        {{ $detail->service->name ?? '-' }}
                    </li>
                @endforeach
            </ul>

            <hr>

            <h5>Progress:</h5>

            <ul>
                @foreach ($order->progresses as $progress)
                    <li>
                        {{ $progress->description }}
                    </li>
                @endforeach
            </ul>

        </div>

    </div>
</section>

@endsection
