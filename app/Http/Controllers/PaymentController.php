<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaymentController extends Controller
{
    public function createOrder(Request $request)
    {
        $provider = new PayPalClient();
        $accessToken = $provider->getAccessToken()['access_token'];

        // Generate a unique PayPal-Request-Id
        $paypalRequestId = (string) Str::uuid();

        // Create PayPal Order
        $response = Http::withToken($accessToken)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'PayPal-Request-Id' => $paypalRequestId, // Ensures idempotency
            ])
            ->post('https://api-m.sandbox.paypal.com/v2/checkout/orders', [
                "intent" => "CAPTURE",
                "purchase_units" => [
                    [
                        "amount" => [
                            "currency_code" => "GBP",
                            "value" => "100.00"
                        ],
                        "description" => "Verification Badge"
                    ]
                ],
                "payment_source" => [
                    "paypal" => [
                        "experience_context" => [
                            "payment_method_preference" => "IMMEDIATE_PAYMENT_REQUIRED",
                            "landing_page" => "LOGIN",
                            "shipping_preference" => "GET_FROM_FILE",
                            "user_action" => "PAY_NOW",
                            'return_url' => route('payment.success'),
                            'cancel_url' => route('payment.cancel'),
                        ]
                    ]
                ],
            ]);

        if (!$response->successful()) return response()->json(['error' => 'Failed to create order'], 500);

        Transaction::create([
            'user_id' => $request->user()->id,
            'reference' => $response->json('id'),
            'amount' => 100.00,
            'status' => 'PENDING',
            'type' => 'debit',
            'title' => 'Verification Badge',
            'description' => 'Verification Badge',
        ]);
        $approvalUrl = collect($response['links'])->where('rel', 'approve')->first()['href'];
        return $approvalUrl;
        // return response()->json($response->json());
    }

    public function success(Request $request)
    {
        $orderId = $request->token;
        $provider = new PayPalClient();
        $accessToken = $provider->getAccessToken()['access_token'];
        $paypalRequestId = (string) Str::uuid();
        $response = Http::withHeaders([
                'Authorization' => "Bearer $accessToken",
                'Content-Type' => 'application/json',
                // 'PayPal-Request-Id' => $paypalRequestId,
            ])
            ->post("https://api-m.sandbox.paypal.com/v2/checkout/orders/$orderId/capture", null);

            Log::info('PayPal Capture Response', $response->json());
        return response()->json($response->json());
    }

    public function cancel(Request $request)
    {
        Transaction::where('reference', $request->token)->delete();
        return response()->json($request->all());
    }
}
