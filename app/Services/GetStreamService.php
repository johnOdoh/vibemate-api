<?php

namespace App\Services;

class GetStreamService
{
    protected $client;
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $key = env('STREAM_API_KEY');
        $secret = env('STREAM_API_SECRET');
        $this->client = new \GetStream\StreamChat\Client($key, $secret);
    }

    public function getUserToken($userId)
    {
        return $this->client->createToken($userId, null, now()->getTimestamp());
    }

    public function revokeUserToken($userId)
    {
        return $this->client->revokeUserToken($userId, now()->getTimestamp());
    }
}
