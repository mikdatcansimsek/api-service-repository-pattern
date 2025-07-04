<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Http\Resources\ProductResource;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function __construct(private ProductService $productService)
    {
    }


    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Get all products",
     *     tags={"Products"},
     *     @OA\Response(
     *         response=200,
     *         description="List of products",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Product")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $products = match(true) {
            $request->has('active') => $this->productService->getActiveProducts($request->active),
            $request->has('available') => $this->productService->getAvailableProducts($request->available),
            $request->has('category_id') => $this->productService->getProductsByCategory($request->category_id),
            $request->has('search') => $this->productService->searchProducts($request->search),
            default => $this->productService->getAllRecords(),
        };
        return ProductResource::collection($products);
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Create product",
     *     tags={"Products"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="iPhone 15"),
     *             @OA\Property(property="description", type="string", example="Latest iPhone"),
     *             @OA\Property(property="price", type="number", example=999.99),
     *             @OA\Property(property="quantity", type="integer", example=50),
     *             @OA\Property(property="sku", type="string", example="IP15001"),
     *             @OA\Property(property="category_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $product = $this->productService->createRecord($request->all());
        return new ProductResource($product);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Get product by ID",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product details",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     )
     * )
     */
    public function show(string $id)
    {
        $product = $this->productService->getRecordById($id);
        return new ProductResource($product);
    }

    /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     summary="Update product",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="iPhone 15 Pro"),
     *             @OA\Property(property="price", type="number", example=1099.99)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $product = $this->productService->updateRecord($id, $request->all());
        return new ProductResource($product);
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     summary="Delete product",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Product deleted"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $this->productService->deleteRecord($id);
        return response()->noContent();
    }

    /**
     * @OA\Get(
     *     path="/api/products/sku/{sku}",
     *     summary="Get product by SKU",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="sku",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product found",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     )
     * )
     */
    public function findBySku(string $sku)
    {
        $product = $this->productService->findProductBySku($sku);
        return new ProductResource($product);
    }
}
