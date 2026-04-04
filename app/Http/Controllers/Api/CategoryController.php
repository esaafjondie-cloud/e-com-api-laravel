<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * List Active Categories
     *
     * Returns a list of all active categories with their images.
     *
     * @group Categories
     * @unauthenticated
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Clothing (ألبسة)",
     *       "image": "http://localhost/storage/categories/clothing-cat.png",
     *       "is_active": true
     *     }
     *   ]
     * }
     */
    public function index()
    {
        $categories = Category::where('is_active', true)->get()->map(function ($category) {
            return [
                'id'        => $category->id,
                'name'      => $category->name,
                'image'     => $category->image ? asset('storage/' . $category->image) : null,
                'is_active' => $category->is_active,
            ];
        });

        return response()->json(['data' => $categories]);
    }
}
