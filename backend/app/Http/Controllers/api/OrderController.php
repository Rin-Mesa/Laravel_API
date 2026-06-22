<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->orders()->with('items.product')->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order = DB::transaction(function () use ($request, $validator) {
            $items = collect($validator->validated()['items']);
            $products = Product::whereIn('id', $items->pluck('product_id'))->get()->keyBy('id');
            $total = 0;

            $order = Order::create([
                'user_id' => $request->user()->id,
                'total_price' => 0,
                'status' => 'pending',
            ]);

            foreach ($items as $item) {
                $product = $products[$item['product_id']];
                $lineTotal = $product->price * $item['quantity'];
                $total += $lineTotal;

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);
            }

            $order->update(['total_price' => $total]);

            return $order->load('items.product');
        });

        return response()->json([
            'success' => true,
            'data' => $order,
        ], Response::HTTP_CREATED);
    }

    public function show(Request $request, string $id)
    {
        $order = $request->user()->orders()->with('items.product')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $order = $request->user()->orders()->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in(['pending', 'processing', 'completed', 'cancelled'])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order->update($validator->validated());

        return response()->json([
            'success' => true,
            'data' => $order->load('items.product'),
        ]);
    }
}
