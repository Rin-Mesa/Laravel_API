<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->role === 'admin'
            ? Order::with(['user', 'items.product'])->latest()->get()
            : $request->user()
            ->orders()
            ->with(['items.product'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string',

            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $order = DB::transaction(function () use ($request, $validator) {

                // Merge duplicate products
                $items = collect($validator->validated()['items'])
                    ->groupBy('product_id')
                    ->map(function ($group) {
                        return [
                            'product_id' => $group->first()['product_id'],
                            'quantity' => $group->sum('quantity'),
                        ];
                    })
                    ->values();

                $products = Product::whereIn(
                    'id',
                    $items->pluck('product_id')
                )
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $total = 0;

                $order = Order::create([
                    'user_id' => $request->user()?->id, 
                    'customer_name' => $request->customer_name, 
                    'phone' => $request->phone, 
                    'email' => $request->email, 
                    'address' => $request->address, 
                    'total_price' => 0, 
                    'status' => 'pending',
                    ]);

                foreach ($items as $item) {

                    $product = $products[$item['product_id']];

                    if ($product->stock < $item['quantity']) { 
                        throw new \Exception(
                             "{$product->name} has insufficient stock." 
                        ); 
                    }

                    $lineTotal =
                        $product->price *
                        $item['quantity'];

                    $total += $lineTotal;

                    $order->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'price' => $product->price,
                    ]);

                    // Reduce stock
                    $product->decrement(
                        'stock',
                        $item['quantity']
                    );
                }

                $order->update([
                    'total_price' => $total,
                ]);

                return $order->load([
                    'items.product'
                ]);
            });

            return response()->json([
                'success' => true,
                'data' => $order,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show(Request $request, string $id)
    {
        $order = $request->user()->role === 'admin'
            ? Order::with([
                'user',
                'items.product'
            ])->findOrFail($id)
            : $request->user()
            ->orders()
            ->with([
                'items.product'
            ])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $order = $request->user()->role === 'admin'
            ? Order::findOrFail($id)
            : $request->user()
            ->orders()
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => [
                'required',
                Rule::in([
                    'pending',
                    'processing',
                    'completed',
                    'cancelled',
                ]),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order->update(
            $validator->validated()
        );

        return response()->json([
            'success' => true,
            'data' => $order->load([
                'items.product'
            ]),
        ]);
    }

    public function dashboardStats(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], Response::HTTP_FORBIDDEN);
        }

        $totalRevenue = Order::where(
            'status',
            'completed'
        )->sum('total_price');

        $newOrdersCount = Order::count();

        $outOfStockCount = Product::where(
            'stock',
            '<=',
            0
        )->count();

        $totalUsersCount = User::count();

        $activeUsers = User::where(
            'created_at',
            '>=',
            now()->subDays(30)
        )->count();

        $recentOrders = Order::with([
            'user:id,name'
        ])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {

                return [
                    'id' => $order->id,

                    'customer_name' =>
                    $order->user->name ?? 'Guest',

                    'customer_initials' =>
                    $order->user
                        ? collect(
                            explode(
                                ' ',
                                $order->user->name
                            )
                        )
                        ->map(
                            fn($n)
                            => strtoupper(
                                substr($n, 0, 1)
                            )
                        )
                        ->join('')
                        : 'GU',

                    'date' =>
                    $order
                        ->created_at
                        ->format('M d, Y'),

                    'amount' =>
                    $order->total_price,

                    'status' =>
                    match ($order->status) {
                        'completed' => 'Delivered',
                        'processing' => 'Shipped',
                        default => ucfirst(
                            $order->status
                        )
                    }
                ];
            });

        return response()->json([
            'success' => true,

            'data' => [

                'total_revenue' =>
                $totalRevenue,

                'new_orders' =>
                $newOrdersCount,

                'out_of_stock' =>
                $outOfStockCount,

                'total_users' =>
                $totalUsersCount,

                'active_users' =>
                $activeUsers,

                'recent_orders' =>
                $recentOrders,

                'sales_chart' => [
                    [
                        'month' => 'Jan',
                        'revenue' => 18000
                    ],
                    [
                        'month' => 'Feb',
                        'revenue' => 22000
                    ],
                    [
                        'month' => 'Mar',
                        'revenue' => 20000
                    ],
                    [
                        'month' => 'Apr',
                        'revenue' => 48000
                    ],
                    [
                        'month' => 'May',
                        'revenue' => 45000
                    ],
                    [
                        'month' => 'Jun',
                        'revenue' => 30000
                    ],
                    [
                        'month' => 'Jul',
                        'revenue' => 52000
                    ]
                ]
            ]
        ]);
    }
}
