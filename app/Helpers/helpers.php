<?php

use Cloudinary\Configuration\Configuration;
use GeoIp2\Database\Reader;
use App\Services\PerspectiveService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

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

function getSpotifyToken()
{
    if (Cache::has('spotify_token')) {
        return Cache::get('spotify_token');
    }

    $response = Http::withoutVerifying()->asForm()->post('https://accounts.spotify.com/api/token', [
        'grant_type'    => 'client_credentials',
        'client_id'     => env('SPOTIFY_CLIENT_ID'),
        'client_secret' => env('SPOTIFY_CLIENT_SECRET'),
    ]);

    $token = $response->json()['access_token'];
    $expiresIn = $response->json()['expires_in']; // Typically 3600 seconds (1 hour)

    // Store in cache for the duration of token validity
    Cache::put('spotify_token', $token, now()->addSeconds($expiresIn - 60)); // Subtract 1 minute for safety

    return $token;
}

function instantiateCloudinary()
{
    return new Configuration([
        'cloud' => [
            'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
            'api_key'    => env('CLOUDINARY_KEY'),
            'api_secret' => env('CLOUDINARY_SECRET'),
            'url'        => [
                'secure' => true
            ]
        ],
    ]);
}

