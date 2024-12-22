<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreConfigrationRequest;
use App\Http\Requests\Api\UpdateConfigrationRequest;
use App\Http\Resources\Api\ConfigrationResource;
use App\Models\Configration;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class ConfigrationController extends Controller
{
    public function __construct(private readonly Configration $model) {}

    public function index(): JsonResponse|JsonResource
    {
        try {
            $type = request()->query('type');
            $configs = $this->model::query()
                ->when($type === 'term', fn ($query) => $query->term())
                ->when($type === 'faq', fn ($query) => $query->faq())
                ->when($type === 'policy', fn ($query) => $query->policy())
                ->get();

            return ResponseHelper::okResponse(data: ConfigrationResource::collection($configs));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $config = $this->model::find($id);
            if (empty($config)) {
                return ResponseHelper::notFoundResponse();
            }

            return ResponseHelper::okResponse(data: ConfigrationResource::make($config));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function store(StoreConfigrationRequest $request)
    {
        try {
            $config = $this->model::create($request->validated());

            return ResponseHelper::createdResponse(ConfigrationResource::make($config));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function update(UpdateConfigrationRequest $request, $id)
    {
        try {
            $story = $this->model::find($id);
            if (empty($story)) {
                return ResponseHelper::notFoundResponse();
            }
            $story->update($request->validated());

            return ResponseHelper::okResponse(data: ConfigrationResource::make($story));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $config = $this->model::find($id);
            if (empty($config)) {
                return ResponseHelper::notFoundResponse();
            }
            $config->delete();

            return ResponseHelper::okResponse();
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }
}
