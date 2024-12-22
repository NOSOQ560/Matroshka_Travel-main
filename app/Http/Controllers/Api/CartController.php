<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CartRequest;
use App\Http\Resources\Api\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function __construct(private readonly Cart $cartModel, private readonly CartItem $cartItemModel) {}

    public function index(): JsonResponse|JsonResource
    {
        try {
            $cart = $this->cartModel::whereUserId(auth()->id())->with('cartItems.product.mainImage')->first();

            return ResponseHelper::okResponse(data: CartResource::make($cart));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function store(CartRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $cart = $this->cartModel::whereUserId(auth()->id())->first();
            if (empty($cart)) {
                $cart = $this->cartModel::create($data + ['user_id' => auth()->id()]);
            }

            $cartItem = $this->cartItemModel::where([
                'cart_id' => $cart['id'],
                'product_id' => $data['product_id'],
            ])->first();

            $totalQuantity = $cartItem ? $cartItem->quantity + $data['quantity'] : $data['quantity'];

            if ($totalQuantity > $cartItem->product->stock) {
                throw ValidationException::withMessages([
                    'quantity' => __('messages.not_enough_product', ['available' => $cartItem->product->stock]),
                ]);
            }

            if (! empty($cartItem)) {
                $cartItem->increment('quantity', $data['quantity']);
            } else {
                $this->cartItemModel::create($data + ['cart_id' => $cart['id']]);
            }

            return ResponseHelper::createdResponse(data: CartResource::make($cart));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function update(CartRequest $request, $id): JsonResponse|JsonResource
    {
        try {
            $data = $request->validated();
            $cart = $this->cartModel::whereUserId(auth()->id())->first();
            if (empty($cart)) {
                return ResponseHelper::notFoundResponse();
            }

            $cartItem = $this->cartItemModel::where([
                'id' => $id,
                'cart_id' => $cart['id'],
            ])->first();

            if (empty($cartItem)) {
                return ResponseHelper::notFoundResponse();
            }

            if ($data['quantity'] > $cartItem->product->stock) {
                throw ValidationException::withMessages([
                    'quantity' => __('messages.not_enough_product', ['available' => $cartItem->product->stock]),
                ]);
            }

            $cartItem->update($data);

            return ResponseHelper::okResponse(data: CartResource::make($cart));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function destroy($id): JsonResponse|JsonResource
    {
        try {
            $cart = $this->cartModel::whereUserId(auth()->id())->first();
            if (empty($cart)) {
                return ResponseHelper::notFoundResponse();
            }

            $cartItem = $this->cartItemModel::where([
                'id' => $id,
                'cart_id' => $cart['id'],
            ])->first();

            if (empty($cartItem)) {
                return ResponseHelper::notFoundResponse();
            }

            $cartItem->delete();

            return ResponseHelper::okResponse();
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }
}