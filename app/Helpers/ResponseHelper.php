<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class ResponseHelper
{
    /**
     * @param string $message
     * @param mixed $data
     * @param bool $success
     * @param int $status
     * @return JsonResponse
     */
    private static function generalResponse(string $message, mixed $data, bool $success, int $status): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
            'success' => $success,
            'status' => $status,
        ], $status);
    }

    /**
     * @param string $message
     * @param mixed $data
     * @param bool $success
     * @return JsonResponse|JsonResource
     */
    public static function okResponse(string $message = 'success', mixed $data = [], bool $success = true): JsonResponse|JsonResource
    {
        if ($data instanceof JsonResource) {
            return $data->additional([
                'message' => $message,
                'success' => $success,
                'status' => Response::HTTP_OK,
            ]);
        }

        return self::generalResponse($message, $data, $success, Response::HTTP_OK);
    }

    /**
     * @param mixed $data
     * @return JsonResponse
     */
    public static function createdResponse(mixed $data): JsonResponse
    {
        return self::generalResponse(__('created'), $data, true, Response::HTTP_CREATED);
    }

    /**
     * @return JsonResponse
     */
    public static function unauthenticatedResponse(): JsonResponse
    {
        return self::generalResponse(__('unauthenticated'), [], false, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @return JsonResponse
     */
    public static function unauthorizedResponse(): JsonResponse
    {
        return self::generalResponse(__('forbidden'), [], false, Response::HTTP_FORBIDDEN);
    }

    /**
     * @return JsonResponse
     */
    public static function notFoundResponse(): JsonResponse
    {
        return self::generalResponse(__('not_found'), [], false, Response::HTTP_NOT_FOUND);
    }

    /**
     * @param mixed $data
     * @return JsonResponse
     */
    public static function validationErrorResponse(mixed $data): JsonResponse
    {
        return self::generalResponse(__('validation_error'), $data, false, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @param mixed $data
     * @return JsonResponse
     */
    public static function internalServerErrorResponse(mixed $data): JsonResponse
    {
        return self::generalResponse(__('internal_server_error'), $data, false, Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
