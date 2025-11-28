<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class APIResponseHelper
{
    public static function sendResponse($data, $message): JsonResponse
    {
        return response()->json([
            'code' => JsonResponse::HTTP_OK,
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], JsonResponse::HTTP_OK, [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    public static function sendError($error, $code = JsonResponse::HTTP_UNPROCESSABLE_ENTITY): JsonResponse
    {
        return response()->json([
            'code' => $code,
            'success' => false,
            'message' => $error,
            'data' => null,
        ], $code, [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    public static function sendSuccess($message): JsonResponse
    {
        return self::sendResponse(null, $message);
    }

    public static function sendPaginatedResponse($data, $message, $perPage, $currentPage, $total): JsonResponse
    {
        $pagination = [
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage),
        ];

        return self::sendResponse([
            'data' => $data,
            'pagination' => $pagination,
        ], $message);
    }

    public static function sendDownloadResponse($filePath, $fileName, $contentType)
    {
        $headers = [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        return response()->download($filePath, $fileName, $headers);
    }

    public static function sendCustomHeaderResponse($data, $message, $headers): JsonResponse
    {
        return self::sendResponse($data, $message)->withHeaders($headers);
    }

    public static function sendCachedResponse($data, $message, $expiresInMinutes = 60)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], 200)->header('Cache-Control', 'public, max-age=' . ($expiresInMinutes * 60));
    }
}
