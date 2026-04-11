<?php

namespace App\Http\Controllers\Web\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
public function index()
    {
        $orders = Order::with([
            'customer',
            'tukang',
            'details.service',
            'progresses'
        ])->latest()->paginate();

        return view('pages.admin.orders.index', compact('orders'));
    }

public function show($id)
{
    $order = Order::with([
        'customer',
        'tukang',
        'details.service'
    ])->findOrFail($id);

    return view('pages.admin.orders.show', compact('order'));
}}
