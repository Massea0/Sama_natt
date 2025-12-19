<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    protected function success(mixed $data, int $status = 200): JsonResponse
    {
        return response()->json(['data' => $data], $status);
    }

    protected function successWithMeta(mixed $data, array $meta, int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'meta' => $meta,
        ], $status);
    }

    protected function error(string $code, string $message, int $status, array $details = []): JsonResponse
    {
        $error = [
            'code' => $code,
            'message' => $message,
        ];
        if (!empty($details)) {
            $error['details'] = $details;
        }
        return response()->json(['error' => $error], $status);
    }

    protected function notFound(string $message = 'Ressource non trouvée'): JsonResponse
    {
        return $this->error('not_found', $message, 404);
    }

    protected function forbidden(string $message = 'Accès refusé'): JsonResponse
    {
        return $this->error('forbidden', $message, 403);
    }

    protected function validationFailed(array $errors): JsonResponse
    {
        return $this->error('validation_failed', 'Les données fournies sont invalides', 422, $errors);
    }
}
