<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductRequest;
use App\Http\Resources\Api\ProductResource;
use App\Models\Product;
use App\Services\ImageService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function __construct(private readonly Product $productModel) {}

//    public function index(): JsonResponse|JsonResource
//    {
//        try {
//            $products = $this->productModel::with(['category', 'mainImage', 'otherImages'])->paginate(16);
//
//            return ResponseHelper::okResponse(data: ProductResource::collection($products));
//        } catch (Exception $exception) {
//            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
//        }
//    }

    public function index($categoryId): JsonResponse|JsonResource
    {
        try {
            // الحصول على category_id من الطلب
//            $categoryId = $request->input('category_id');

            // بناء الاستعلام
            $query = $this->productModel::with(['category', 'mainImage', 'otherImages']);

            // إذا تم تمرير category_id، قم بتصفية النتائج بناءً عليه
            if ($categoryId) {
                $query->whereHas('category', function ($q) use ($categoryId) {
                    $q->where('id', $categoryId);
                });
            }

            // تنفيذ الاستعلام مع التصفح
            $products = $query->paginate(16);

            // إرجاع النتيجة
            return ResponseHelper::okResponse(data: ProductResource::collection($products));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function show($id): JsonResponse|JsonResource
    {
        try {
            // إضافة سجل للأخطاء لتتبع ما يحدث
            Log::info('Fetching product', ['product_id' => $id]);

            $product = $this->productModel::with(['mainImage', 'otherImages'])->find($id);

            if (!$product) {
                Log::warning('Product not found', ['product_id' => $id]);
                return ResponseHelper::notFoundResponse();
            }

            return ResponseHelper::okResponse(data: ProductResource::make($product));
        } catch (Exception $exception) {
            // إضافة سجل للخطأ
            Log::error('Error fetching product', ['exception' => $exception->getMessage(), 'product_id' => $id]);
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }


    public function store(ProductRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $product = $this->productModel::create($data);

            $imageService = (new ImageService($product, $data));
            $imageService->storeMedia('product', 'main_image');
            if (isset($data['other_images']) && ! is_null($data['other_images'])) {
                $imageService->updateMedias('product-other-images', otherMediasRequest: 'other_images');
            }

            return ResponseHelper::createdResponse(ProductResource::make($product));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function update(ProductRequest $request, $id): JsonResponse|JsonResource
    {
        try {
            $data = $request->validated();
            $product = $this->productModel::find($id);
            if (empty($product)) {
                return ResponseHelper::notFoundResponse();
            }
            $product->update($data);

            $imageService = (new ImageService($product, $data));
            $imageService->updateMedia('product', 'main_image');
            if (isset($data['other_images']) && ! is_null($data['other_images'])) {
                $imageService->updateMedias('product-other-images', 'delete_images', 'other_images');
            }
            if (isset($data['delete_images']) && ! is_null($data['delete_images'])) {
                $imageService->deleteMediasWithIds('delete_images', 'otherImages');
            }

            return ResponseHelper::okResponse(data: ProductResource::make($product));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function destroy($id): JsonResponse|JsonResource
    {
        try {
            $product = $this->productModel::find($id);
            if (empty($product)) {
                return ResponseHelper::notFoundResponse();
            }
            $product->delete();

            return ResponseHelper::okResponse();
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }
}
