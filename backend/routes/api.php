<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
// Products
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
// Categories
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
// Reviews (Read Only)
Route::get('/reviews', [ReviewController::class, 'index']);
Route::get('/reviews/{review}', [ReviewController::class, 'show']);

// Authenticated Users (Customer + Admin)

Route::middleware('auth:sanctum')->group(function () {

    // Current User
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);
    // Cart
    Route::apiResource('carts', CartController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    // Wishlist
    Route::apiResource('wishlists', WishlistController::class)
        ->only(['index', 'store', 'destroy']);
    // Orders
    Route::apiResource('orders', OrderController::class);
    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);
});

// Admin Only

Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    // Admin users
    Route::get('/admin/users', function (\Illuminate\Http\Request $request) {
        return response()->json([
            'success' => true,
            'data' => \App\Models\User::query()
                ->select('id', 'name', 'email', 'role', 'created_at')
                ->latest()
                ->get(),
        ], 200);
    });

    Route::put('/admin/users/{id}', function (\Illuminate\Http\Request $request, $id) {
        $user = \App\Models\User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'sometimes|required|in:admin,customer',
            'is_active' => 'sometimes|boolean',
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'User updated successfully.',
        ], 200);
    });

    Route::delete('/admin/users/{id}', function ($id) {
        $user = \App\Models\User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
        ], 200);
    });

    // Categories Management
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    // Products Management
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);

    // Dashboard
    Route::get('/admin/dashboard-stats', [OrderController::class, 'dashboardStats']);
});
