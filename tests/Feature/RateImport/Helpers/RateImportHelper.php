<?php

declare(strict_types=1);

namespace Tests\Feature\RateImport\Helpers;

class RateImportHelper
{
    public static function getFakeSuccessResponse($currency = 'USD'): array
    {
        return [
            'success' => true,
            'terms' => 'https://currencylayer.com/terms',
            'privacy' => 'https://currencylayer.com/privacy',
            'historical' => true,
            'date' => date('Y-m-d'),
            'timestamp' => 1702242784,
            'source' => $currency,
            'quotes' =>
                [
                    $currency . 'GBP' => 0.796784,
                    $currency . 'EUR' => 0.929025,
                    $currency . 'AUD' => 1.52059,
                    $currency . 'NZD' => 1.634922,
                    $currency . 'CAD' => 1.357955,
                ],
        ];
    }

    public static function getFakeFailureResponse(): array
    {
        return [
            'success' => false,
            'error' =>
                [
                    'code' => 101,
                    'type' => 'missing_access_key',
                    'info' => 'You have not supplied an API Access Key. [Required format: access_key=YOUR_ACCESS_KEY]',
                ],
        ];
    }
}
