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
        $orders = Order::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {

                $user = User::find($order->user_id);
                $email = $user ? $user->email : 'Unknown User';

                $total = 0;
                $itemsCollection = OrderItem::where('order_id', $order->order_id)->get()->map(function($item) use (&$total) {
                    $product = Product::where('product_id', $item->product_id)->first();
                    $total += ($item->quantity * $item->unit_price);

                    return [
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'product_name' => $product ? $product->product_name : 'Unknown Item'
                    ];
                });
                /** @var array $items */
                $items = $itemsCollection->toArray();

                $orderArray = $order->toArray();
                $orderArray['user_email'] = $email;
                $orderArray['items'] = $items;
                $orderArray['total_amount'] = $total;

                return $orderArray;
            });

        return Inertia::render('OrderHistory', [
            'orders' => $orders
        ]);
    }

    public function cancel(Order $order){
        if($order->user_id !== Auth::id()){
            abort(403, 'Unauthorized action.');
        }

        if(!in_array($order->status, ['PENDING', 'SHIPPED'])){
            return back()->with('error', 'This order cannot be cancelled.');
        }

        $order->update([
            'status' => 'CANCELLED',
        ]);

        return back()->with('message', 'Order cancelled successfully.');
    }
}

