<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FawaterkService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('fawaterk.api_key');
        $this->baseUrl = config('fawaterk.base_url');
    }

    public function createInvoice(array $data)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->post($this->baseUrl . '/createInvoice', $data);

        return $response->json();
    }
}
