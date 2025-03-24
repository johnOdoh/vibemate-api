<?php

namespace App\Http\Middleware;

use Closure;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class VerifyPaypalWebhook
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->validatePaypalWebhook($request)) {
            Log::warning('Invalid PayPal Webhook!');
            return response()->json(['error' => 'Invalid webhook'], 400);
        }

        Log::info('Valid PayPal Webhook Received.');
        return $next($request);
    }

    private function validatePaypalWebhook(Request $request)
    {
        $client = new Client();
        $provider = new PayPalClient();

        $headers = $request->header();
        $body = $request->getContent();

        $verifyUrl = "https://api-m.sandbox.paypal.com/v1/notifications/verify-webhook-signature"; // Use live URL in production.

        $response = $client->post($verifyUrl, [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $provider->getAccessToken(),
            ],
            'json' => [
                'auth_algo'         => $headers['Paypal-Auth-Algo'][0] ?? '',
                'cert_url'          => $headers['Paypal-Cert-Url'][0] ?? '',
                'transmission_id'   => $headers['Paypal-Transmission-Id'][0] ?? '',
                'transmission_sig'  => $headers['Paypal-Transmission-Sig'][0] ?? '',
                'transmission_time' => $headers['Paypal-Transmission-Time'][0] ?? '',
                'webhook_id'        => env('PAYPAL_SUBSCRIPTION_WEBHOOK_ID'), // Set this in .env
                'webhook_event'     => json_decode($body, true),
            ]
        ]);

        $result = json_decode($response->getBody()->getContents(), true);
        return isset($result['verification_status']) && $result['verification_status'] === 'SUCCESS';
    }
}
