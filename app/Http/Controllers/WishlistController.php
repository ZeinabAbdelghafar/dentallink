<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Models\Product;

class WishlistController extends Controller
{

    public function index(Request $request)
    {
        $email = $request->user()->email;
        $wishlist = Wishlist::where('email', $email)->first();

        if (!$wishlist) {
            return response()->json([]);
        }

        return response()->json($wishlist->items()->get());
    }


    public function toggleFavorite(Request $request)
    {
        $user = $request->user();
        $email = $user->email;

        if (!$email) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $productId = $request->input('productId');

        if (!$productId) {
            return response()->json(['error' => 'Product ID is required'], 400);
        }

        $product = Product::find($productId);

        if (!$product) {
            return response()->json(['error' => 'Invalid Product ID'], 404);
        }

        $wishlist = Wishlist::firstOrCreate(['email' => $email]);

        $existingItem = $wishlist->items()->where('productId', $productId)->first();
        if ($existingItem) {
            $existingItem->delete();
            return response()->json([
                'message' => 'Removed from favorites',
                'wishlist' => $wishlist->items()->with('product')->get()
            ]);
        } else {
            $wishlist->items()->create([
                'productId' => $product->id,
                'name' => $product->title,
                'price' => $product->price,
                "img" => $product->image ?? null,
                "stock" => $product->stock,

            ]);

            $message = 'Added to favorites';
            if ($product->stock <= 0) {
                $message .= ' (Currently out of stock. You’ll be notified when it’s back.)';
            }

            return response()->json([
                'message' => $message,
                'wishlist' => $wishlist->items()->with('product')->get()
            ]);
        }
    }


    public function store(Request $request)
    {
        $user = $request->user();
        $email = $user->email;
        $productId = $request->input('productId');

        if (!$productId) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['error' => 'Invalid Product ID'], 404);
        }

        $wishlist = Wishlist::firstOrCreate(['email' => $email]);

        $exists = $wishlist->items()->where('productId', $productId)->exists();

        if (!$exists) {
            $wishlist->items()->create([
                'productId' => $product->id,
                'name' => $product->title,
                'price' => $product->price,
                'img' => $product->image,
                'stock' => $product->stock,
            ]);
        }

        return response()->json($wishlist->items()->get());
    }

    public function update(Request $request, string $productId)
    {
        $user = $request->user();
        $email = $user->email;
        $wishlist = Wishlist::where('email', $email)->first();

        if (!$wishlist) {
            return response()->json(['error' => 'Wishlist not found'], 404);
        }

        $item = $wishlist->items()->where('productId', $productId)->first();

        if (!$item) {
            return response()->json(['error' => 'Item not found in wishlist'], 404);
        }

        $item->update($request->all());

        return response()->json([
            'message' => 'Wishlist item updated successfully',
            'item' => $item
        ]);
    }


    public function destroy(Request $request)
    {
        $email = $request->user()->email;

        $wishlist = Wishlist::where('email', $email)->first();

        if ($wishlist) {
            $wishlist->delete();
            return response()->json(['message' => 'Wishlist deleted successfully']);
        }

        return response()->json(['message' => 'Wishlist not found'], 404);
    }
}
