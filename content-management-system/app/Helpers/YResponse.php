<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use PDO;
use stdClass;

class YResponse
{
    public stdClass $data;
    // public string $message;
    public int $status;
    public array $headers;

    public function __construct(public string $message = "", $data = null, $status = 200, $headers = [])
    {
        // $this->message = $message;
        $this->data = $data ?? new stdClass();
        $this->status = $status;
        $this->headers = $headers;
    }

    public function send(): JsonResponse
    {
        return response()->json([
            "message" => $this->message,
            "data" => $this->data
        ], $this->status, $this->headers);
    }

    public static function json(string $message = "", $data = [],  $status = 200, string $error = "",  $headers = []): JsonResponse
    {
        return response()->json([
            "message" => $message,
            "data" => (object)$data,
            "error" => $error
        ], $status, $headers);
    }
}
