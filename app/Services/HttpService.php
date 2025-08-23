<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class HttpService
{
    public static function getRequest(string $url, array $params = [])
    {
        $response = Http::timeout(30)
            ->get($url, $params);

        return $response;
    }

    public static function postRequest(string $url, array $data = [])
    {
        $response = Http::post($url, $data);

        return $response;
    }

    public static function postRequestHeaders(string $url, $headers = [], array $data = [])
    {
        $response = Http::withHeaders($headers)->post($url, $data);

        return $response;
    }
}
