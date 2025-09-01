<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class ProductCatalogController extends Controller
{
    /**
     * Display the Product Listing Page (PLP) for a given category.
     *
     * This method handles:
     * - Fetching all products within the category and its descendants.
     * - Applying dynamic filters based on URL query parameters.
     * - Eager-loading necessary relationships for performance.
     * - Paginating the results.
     *
     * @param Request $request
     * @param Category $category Injected via Route Model Binding.
     * @return View
     */
    public function showCategory(Request $request, Category $category): View
    {
        // Start building the base query for products.
        $productsQuery = Product::query()
            // Filter products belonging to the current category or any of its children.
            // This leverages the materialized path strategy for high performance.
            ->whereHas('categories', function ($query) use ($category) {
                $query->where('path', 'like', $category->path . '%');
            })
            // Eager load essential relationships to prevent N+1 problems in the view.
            ->with(['media', 'skus']);

        // --- Dynamic Filtering Logic ---
        // Get all filterable query parameters from the URL (e.g., ?color=rojo&talla=m).
        $filters = $request->query();

        // Iterate over each filter and apply it to the query.
        foreach ($filters as $attributeSlug => $valueSlug) {
            // We apply a nested `whereHas` to ensure products have a SKU that matches
            // the specific attribute-value combination.
            $productsQuery->whereHas('skus', function ($skuQuery) use ($attributeSlug, $valueSlug) {
                $skuQuery->whereHas('attributeValues', function ($attrValQuery) use ($attributeSlug, $valueSlug) {
                    // This assumes attribute names and values are URL-friendly (slugs).
                    // In a real-world scenario, you might have 'slug' columns on attributes/values.
                    $attrValQuery
                        ->where('value', $valueSlug)
                        ->whereHas('attribute', fn ($attrQuery) => $attrQuery->where('name', $attributeSlug));
                });
            });
        }

        // Paginate the final, filtered results.
        $products = $productsQuery->paginate(12)->withQueryString();

        // Pass the category and the paginated products collection to the view.
        return view('catalog.category', [
            'category' => $category,
            'products' => $products,
        ]);
    }

    /**
     * Display the Product Detail Page (PDP).
     *
     * @param Product $product Injected via Route Model Binding.
     * @return View
     */
    public function showProduct(Product $product): View
    {
        // Logic for the PDP will be implemented here in the next sprint.
        // For now, it's a placeholder.
        return view('product.show', ['product' => $product]);
    }
}
