<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        // Filter by category slug if provided
        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->input('sortBy', 'title');
        $sortDirection = $request->input('sortDirection', 'asc');

        $validSortColumns = ['title', 'price', 'created_at'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'title';

        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 9);

        $products = $query->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'currentPage' => $products->currentPage(),
            'totalPages' => $products->lastPage(),
            'totalProducts' => $products->total(),
            'products' => $products->items(),
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        \Log::info('Store method hit'); 
        $data = $request->validated();

        // Handle image uploads
        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $uploadedFile = Cloudinary::upload($image->getRealPath());
                $images[] = $uploadedFile->getSecurePath();
            }
        }

        $data['images'] = $images;
        $data['ratings'] = ['1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0];

        $product = Product::create($data);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product
        ], 201);
    }

    public function show($id)
    {
        $product = Product::with('category')->findOrFail($id);
        return response()->json($product);
    }

    public function update(UpdateProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $data = $request->validated();

        // Handle image updates
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $uploadedFile = Cloudinary::upload($image->getRealPath());
                $images[] = $uploadedFile->getSecurePath();
            }
            $data['images'] = $images;
        }

        $product->update($data);

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product
        ]);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }

    public function addRating(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'user_id' => 'required|exists:users,id'
        ]);

        $product = Product::findOrFail($id);
        $userId = $request->user_id;

        // Check if user already rated this product
        if (isset($product->ratings['user_'.$userId])) {
            return response()->json([
                'message' => 'You have already rated this product'
            ], 400);
        }

        // Update rating counts
        $rating = $request->rating;
        $ratings = $product->ratings ?? ['1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0];
        $ratings[$rating] += 1;
        $ratings['user_'.$userId] = $rating; // Track user's rating

        $product->ratings = $ratings;
        $product->calculateAverageRating();

        return response()->json([
            'message' => 'Rating added successfully',
            'product' => $product
        ]);
    }

    public function getRating($id)
    {
        $product = Product::findOrFail($id);

        return response()->json([
            'average_rating' => $product->average_rating,
            'total_ratings' => $product->total_ratings
        ]);
    }

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2'
        ]);

        $query = $request->query;

        $products = Product::where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->get();

        return response()->json($products);
    }

    public function count()
    {
        $count = Product::count();
        return response()->json(['count' => $count]);
    }
}
