<?php

namespace App\Http\Controllers;

use App\Helpers\UploadFileFromBase64;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Plank\Mediable\Facades\MediaUploader;

class ProductController extends Controller
{
    use UploadFileFromBase64;

    public function __construct()
    {
        $this->middleware('auth:api')->except(['index', 'show']);
        $this->middleware('permission:ADMIN,SELLER', [
            'except' => ['index', 'show']
        ]);
    }

    public function index(Request $request)
    {
        $products = Product::paginate($request->get('size', 15))->withQueryString();

        return response()->json($products, Response::HTTP_OK);
    }

    public function store(CreateProductRequest $request)
    {
        $productData = $request->validated();

        $product = Product::create($productData);

        if ($request->has('images')) {
            $this->uploadImages($request->images, $product);
        }

        return response()->json($product, Response::HTTP_CREATED);
    }

    public function show(int $id)
    {
        $product = Product::findOrFail($id);

        return response()->json($product, Response::HTTP_OK);
    }

    public function update(UpdateStoreRequest $request, int $id)
    {

        $product = Product::find($id);
        $productData = $request->validated();

        $product->update($productData);

        if ($request->has('images')) {
            $this->uploadImages($request->images, $product);
        }

        return response()->json($product, Response::HTTP_OK);
    }

    public function destroy(int $id)
    {
        $product = Product::findOrFail($id);

        $product->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    private function uploadImages(array $images, Product $product)
    {
        foreach ($images as $image) {
            $imageUploaded = MediaUploader::fromSource($this->uploadFile($image))
                ->toDisk('public')
                ->toDirectory("stores/{$product->store->id}")
                ->useHashForFilename()
                ->upload();
                
            $product->attachMedia($imageUploaded, 'products');
        }
    }
}
