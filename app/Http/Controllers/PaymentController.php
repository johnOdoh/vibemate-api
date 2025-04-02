<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Stripe\StripeClient;

class PaymentController extends Controller
{
    public function createPaypalOrder(Request $request)
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
                            "value" => "200.00"
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
                            'return_url' => route('payment.paypal.success'),
                            'cancel_url' => route('payment.paypal.cancel'),
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
        $approvalUrl = collect($response['links'])->where('rel', 'payer-action')->first()['href'];
        return $approvalUrl;
        // return response()->json($response->json());
    }

    public function paypalPaymentSuccess(Request $request)
    {
        $orderId = $request->token;
        $provider = new PayPalClient();
        $accessToken = $provider->getAccessToken()['access_token'];
        $response = Http::withHeaders([
                'Authorization' => "Bearer $accessToken",
                'Content-Type' => 'application/json',
                // 'PayPal-Request-Id' => $paypalRequestId,
            ])
            ->post("https://api-m.sandbox.paypal.com/v2/checkout/orders/$orderId/capture", null);

        Log::info("Paypal payment by {$request->user()->profile->username}", $response->json());
        return response()->json(['message' => 'Your payment is being processed. Your payment status will be updated as possible']);
    }

    public function paypalPaymentCancel(Request $request)
    {
        Transaction::where('reference', $request->token)->delete();
        return response()->json($request->all());
    }

    public function paypalPayouts()
    {
        $provider = new PayPalClient();
        $accessToken = $provider->getAccessToken()['access_token'];
        $response = Http::withToken($accessToken)
            ->withHeaders([
                'Content-Type' => 'application/json'
            ])
            ->post('https://api-m.sandbox.paypal.com/v1/payments/payouts', [
                'sender_batch_header' => [
                    'email_subject' => "You have received a payout!",
                    "email_message" => "You have received your referral payout! Thanks for using our service!"
                ],
                'items' => [
                    [
                        'recipient_type' => 'EMAIL',
                        'amount' => [
                            'value' => '3000.00', // Amount to payout
                            'currency' => 'GBP'
                        ],
                        'receiver' => 'sb-cws0a39006800@personal.example.com', // Customer PayPal email
                        'note' => 'Here is your payout!',
                        // Remember to put a unique prefix
                        'sender_item_id' => uniqid(),
                    ]
                ]
            ]);
        return $response->json();
    }

    public function createStripePayment(Request $request)
    {
        $stripe = new StripeClient(env('STRIPE_SECRET'));

         // Create a Stripe Checkout session
         $response = $stripe->checkout->sessions->create([
            'client_reference_id' => uniqid(more_entropy: true),
            // 'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'gbp',
                    'product_data' => [
                        'name' => 'Verification Badge',
                        'description' => 'Get a Vibemate verification badge'
                    ],
                    'unit_amount' => 200*100, // Amount in cents (20.00 GBP)
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('payment.stripe.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payment.stripe.cancel').'?session_id={CHECKOUT_SESSION_ID}',
        ]);

        Transaction::create([
            'user_id' => $request->user()->id,
            'reference' => $response->client_reference_id,
            'amount' => 100.00,
            'status' => 'PENDING',
            'type' => 'debit',
            'title' => 'Verification Badge',
            'description' => 'Verification Badge',
        ]);
        return response()->json($response);
    }

    public function stripePaymentSuccess(Request $request)
    {
        $stripe = new StripeClient(env('STRIPE_SECRET'));
        $session = $stripe->checkout->sessions->retrieve($request->session_id);
        approvePayment($session->client_reference_id);
        return $session;
    }

    public function stripePaymentCancel(Request $request)
    {
        $stripe = new StripeClient(env('STRIPE_SECRET'));
        $session = $stripe->checkout->sessions->retrieve($request->session_id);
        Transaction::where('reference', $session->client_reference_id)->delete();
        return $session;
    }

}
