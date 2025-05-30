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
        $productId = $request->input('product_id');

        if (!$productId) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['error' => 'Invalid Product ID'], 404);
        }

        $wishlist = Wishlist::firstOrCreate(['email' => $email]);

        $existingItem = $wishlist->items()->where('product_id', $productId)->first();

        if ($existingItem) {
            $existingItem->delete();
            return response()->json([
                'message' => 'Removed from favorites',
                'wishlist' => $wishlist->items()->get()
            ]);
        } else {
            $wishlist->items()->create([
                'product_id' => $product->id,
                'name' => $product->title,
                'price' => $product->price,
                'img' => $product->image,
                'stock' => $product->stock,
            ]);
            return response()->json([
                'message' => 'Added to favorites',
                'wishlist' => $wishlist->items()->get()
            ]);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $email = $user->email;
        $productId = $request->input('product_id');

        if (!$productId) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['error' => 'Invalid Product ID'], 404);
        }

        $wishlist = Wishlist::firstOrCreate(['email' => $email]);

        $exists = $wishlist->items()->where('product_id', $productId)->exists();

        if (!$exists) {
            $wishlist->items()->create([
                'product_id' => $product->id,
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

        $item = $wishlist->items()->where('product_id', $productId)->first();

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
