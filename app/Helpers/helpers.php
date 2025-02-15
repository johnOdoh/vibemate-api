<?php

use GeoIp2\Database\Reader;
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
