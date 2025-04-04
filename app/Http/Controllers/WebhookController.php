<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function paypalWebhooks(Request $request) {
        $payload = $request->all();
        $subscription_id = $payload['resource']['id'];
        switch ($payload['event_type']) {
            case 'BILLING.SUBSCRIPTION.CANCELLED':
                cancelSubscription($subscription_id);
                break;

            case 'BILLING.SUBSCRIPTION.ACTIVATED':
                IF($payload['resource']['status_change_note'])
                    reactivateSubscription($subscription_id);
                break;

            case 'BILLING.SUBSCRIPTION.SUSPENDED':
                suspendSubscription($subscription_id);
                break;

            case 'BILLING.SUBSCRIPTION.PAYMENT.FAILED':
                expireSubscription($subscription_id);
                break;

            case 'PAYMENT.SALE.COMPLETED':
                if ($payload['resource']['billing_agreement_id'])
                    activateSubscription($payload['resource']['billing_agreement_id']);
                break;

            case 'PAYMENT.CAPTURE.COMPLETED';
                $id = $payload['resource']['supplementary_data']['related_ids']['order_id'];
                approvePayment($id);
                break;
        }
        return response()->noContent();
    }

    public function stripeWebhooks(Request $request) {
        $payload = $request->all();
        Log::info('Stripe webhook', $payload);
        return response()->json(['message' => 'Success']);
    }
}
