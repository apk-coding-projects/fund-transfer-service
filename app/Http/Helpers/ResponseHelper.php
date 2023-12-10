<?php

declare(strict_types=1);

namespace App\Http\Helpers;

class ResponseHelper
{
    public static function success(bool $isSuccess = false, string $message = '', array $payload = []): array
    {
        $response = [
            'success' => $isSuccess,
            'payload' => $payload,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        return $response;
    }

    public static function failure(bool $isSuccess = false, string $error = ''): array
    {
        return [
            'success' => $isSuccess,
            'error' => $error,
        ];
    }
}
