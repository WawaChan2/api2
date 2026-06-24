<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Movement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $transaction = Transaction::create();

        $order = Order::create([
            'order_id' => $transaction->transaction_id,
            'user_id' => Auth::id(),
            'status' => "PENDING",
        ]);

        foreach ($request->items as $item) {
            OrderItem::create([
                'order_id' => $order->order_id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
            ]);

            $inventory = Inventory::where('product_id', $item['product_id'])->first();

            Movement::create([
                'inventory_id' => $inventory->inventory_id,
                'transaction_id' => $transaction->transaction_id,
                'transaction_type' => 'ORDER',
                'quantity_delta' => -$item['quantity'],
            ]);
        }

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
