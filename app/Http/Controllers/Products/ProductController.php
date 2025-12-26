<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\Products\CreateProduct;
use App\Services\Products\DeleteProduct;
use App\Services\Products\ListProducts;
use App\Services\Products\UpdateProduct;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request, ListProducts $service): Response
    {
        return Inertia::render('Products/Index', [
            'products' => $service->execute($request->only([
                'search',
                'track_inventory',
                'out_of_stock',
                'inactive',
                'per_page',
            ])),
            'filters' => $request->only([
                'search',
                'track_inventory',
                'out_of_stock',
                'inactive',
                'per_page',
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Products/Create');
    }

    public function store(StoreProductRequest $request, CreateProduct $service)
    {
        $service->execute($request->validated(), $request->user()->id);

        return redirect()->route('products.index');
    }

    public function edit(Product $product): Response
    {
        return Inertia::render('Products/Edit', [
            'product' => $product,
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product, UpdateProduct $service)
    {
        $service->execute($product, $request->validated());

        return redirect()->route('products.index');
    }

    public function destroy(Product $product, DeleteProduct $service)
    {
        $service->execute($product);

        return redirect()->route('products.index');
    }
}
