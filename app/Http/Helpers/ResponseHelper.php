<?php

declare(strict_types=1);

namespace App\Http\Helpers;

class ResponseHelper
{
    public static function success(
        bool $isSuccess = false,
        string $message = '',
        array $payload = [],
        int $code = 200,
    ): array {
        $response = [
            'code' => $code,
            'success' => $isSuccess,
            'payload' => $payload,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        return $response;
    }

    public static function failure(string $error = '', int $code = 500): array
    {
        return [
            'success' => false,
            'code' => $code,
            'error' => $error,
        ];
    }
}
