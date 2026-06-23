<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class StatsController extends Controller
{
    public function index(): Response
    {
        $userId = Auth::id();

        // 1. Fetch user orders
        $orders = Order::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalOrdersCount = $orders->count();
        $activeOrdersCount = 0;
        $totalSpentAmount = 0;

        // 2. Loop through orders to compute stats and map purchase list arrays
        $purchaseHistory = $orders->map(function ($order) use (&$activeOrdersCount, &$totalSpentAmount) {
            
            // Check active states based on your app's statuses
            if (in_array($order->status, ['PENDING', 'SHIPPED'])) {
                $activeOrdersCount++;
            }

            // Calculate total for this specific order dynamically
            $orderTotal = 0;
            /** @var \Illuminate\Support\Collection<int, array> $itemsCollection */
            $itemsCollection = OrderItem::where('order_id', $order->order_id)->get()->map(function($item) use (&$orderTotal) {
                $product = Product::where('product_id', $item->product_id)->first();
                $orderTotal += ($item->quantity * $item->unit_price);

                return [
                    'product_id'   => $item->product_id,
                    'quantity'     => $item->quantity,
                    'unit_price'   => $item->unit_price,
                    'product_name' => $product ? $product->product_name : 'Unknown Item'
                ];
            });
            $items = $itemsCollection->all();

            // Accumulate spending metric if order wasn't cancelled
            if ($order->status !== 'CANCELLED') {
                $totalSpentAmount += $orderTotal;
            }

            return [
                'order_id'     => $order->order_id,
                'status'       => $order->status,
                'created_at'   => $order->created_at ? $order->created_at->format('n/j/Y, g:i A') : '', 
                'total_amount' => number_format($orderTotal, 2),
                'items'        => $items
            ];
        });

        // 3. Render UserStats component with calculated dynamic properties payload
        return Inertia::render('UserStats', [
            'stats' => [
                'totalOrders'  => $totalOrdersCount,
                'activeOrders' => $activeOrdersCount,
                'totalSpent'   => '$' . number_format($totalSpentAmount, 2),
            ],
            'purchaseHistory' => $purchaseHistory
        ]);
    }
}