<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Product;

class OrderController extends Controller
{
    public function index()
    {
        // 1. Filter by current user ID for security
        $orders = Order::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {

                // 2. Find the user for this order
                $user = User::find($order->user_id);
                $email = $user ? $user->email : 'Unknown User';

                // 3. Calculate total and get product names
                $total = 0;
                $items = OrderItem::where('order_id', $order->order_id)->get()->map(function($item) use (&$total) {
                    $product = Product::where('product_id', $item->product_id)->first();
                    $total += ($item->quantity * $item->unit_price);

                    return [
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'product_name' => $product ? $product->product_name : 'Unknown Item'
                    ];
                });

                // 4. Construct the final array with user_email included
                $orderArray = $order->toArray();
                $orderArray['user_email'] = $email; // This is the line you were looking for!
                $orderArray['items'] = $items->toArray();
                $orderArray['total_amount'] = $total;

                return $orderArray;
            });

        return Inertia::render('OrderHistory', [
            'orders' => $orders
        ]);
    }
}
