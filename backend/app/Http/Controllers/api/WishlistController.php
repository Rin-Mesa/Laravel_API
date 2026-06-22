<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $wishlists = $request->user()->wishlists()->with('product.category')->get();

        return response()->json([
            'success' => true,
            'data' => $wishlists,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $wishlist = Wishlist::firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $validator->validated()['product_id'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $wishlist->load('product.category'),
        ], Response::HTTP_CREATED);
    }

    public function destroy(Request $request, string $id)
    {
        $wishlist = $request->user()->wishlists()->findOrFail($id);
        $wishlist->delete();

        return response()->json([
            'success' => true,
            'message' => 'Wishlist item removed successfully.',
        ]);
    }
}
