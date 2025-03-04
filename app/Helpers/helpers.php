<?php

use GeoIp2\Database\Reader;
use App\Services\PerspectiveService;
use Illuminate\Support\Facades\Http;

function getUserRealIp()
{
    $response = Http::withoutVerifying()->get('https://api64.ipify.org?format=json');

    return $response->successful() ? $response->json()['ip'] : '8.8.8.8';
}

function getCountryFromIp()
{
    $ip = request()->ip();
    if ($ip == '127.0.0.1' || $ip == '::1') {
        $ip = getUserRealIp();
    }
    $dbPath = storage_path('app/public/geolite/GeoLite2-Country.mmdb');

    if (!file_exists($dbPath)) {
        return response()->json(['error' => 'GeoIP database not found'], 401);
    }
    try {
        $reader = new Reader($dbPath);
        $record = $reader->country($ip);
        $country = $record->country->name;
    } catch (\Throwable $th) {
        $country = 'Unknown';
    }
    return $country;
}

function screenInput($input)
{
    $perspectiveService = new PerspectiveService;
    $result = $perspectiveService->analyzeText($input);
    if($result['SEVERE_TOXICITY'] >= 0.6 || $result['IDENTITY_ATTACK'] >= 0.6 || $result['THREAT'] >= 0.6) return false;
    return true;
}
