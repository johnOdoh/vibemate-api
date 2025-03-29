<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Enums\SubscriptionStatus;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class SubscriptionController extends Controller
{
    public function createPlan() {
        $provider = new PayPalClient();
        $provider->getAccessToken();
        $response = $provider->addProduct('Demo Product', 'Demo Product', 'SERVICE', 'SOFTWARE')
                    ->addDailyPlan('Demo Plan', 'Demo Plan', 30)
                    ->setReturnAndCancelUrl(config('app.url').'/paypal-success', config('app.url').'/paypal-cancel')
                    ->setupSubscription('John Doe', 'john@example.com', now()->addSeconds(60));
        return response()->json($response);
    }

    public function createSubscription(Request $request, Plan $plan) {
        $provider = new PayPalClient();
        $provider->getAccessToken();
        $response = $provider->addPaymentFailureThreshold(1)
                    ->addProductById($plan->paypal_product_id)
                    ->addBillingPlanById($plan->paypal_plan_id)
                    ->setReturnAndCancelUrl(config('app.url').'/subscription/success', config('app.url').'/subscription/cancel')
                    ->setupSubscription($request->user()->profile->username, $request->user()->email, now()->addSeconds(60));
        $request->user()->subscription ? $request->user()->subscription->delete() : null;
        Subscription::create([
            'user_id' => $request->user()->id,
            'plan_id' => $plan->id,
            'paypal_subscription_id' => $response['id'],
            'start' => now(),
            'end' => now()->addDays($plan->interval)
        ]);
        // return redirect()->away($response['links'][0]['href']);
        return response()->json($response);
    }

    public function cancelSubscription($subscription_id) {
        if (cancelSubscription($subscription_id))
            return response()->json(['message' => 'Subscription canceled']);
        else
            return response()->json(['message' => 'Subscription not found'], 404);
    }

    public function suspendSubscription($subscription_id) {
        if (suspendSubscription($subscription_id))
            return response()->json(['message' => 'Subscription suspended']);
        else
            return response()->json(['message' => 'Subscription not found'], 404);
    }

    public function reactivateSubscription($subscription_id) {
        if (reactivateSubscription($subscription_id))
            return response()->json(['message' => 'Subscription reactivated']);
        else
            return response()->json(['message' => 'Subscription not found'], 404);
    }

    public function success(Request $request) {
        Log::info('PayPal Successful', $request->all());
        $subscription = Subscription::where('paypal_subscription_id', $request->subscription_id)->first();
        $subscription->status = SubscriptionStatus::Active->value;
        $subscription->user->is_subscribed = true;
        $subscription->push();
        return response()->json(['message' => 'Subscription activated']);
    }

    public function cancel(Request $request) {
        Log::info('PayPal Cancelled', $request->all());
        $subscription = Subscription::where('paypal_subscription_id', $request->subscription_id)->delete();
        return response()->json(['message' => 'Subscription cancelled']);
    }

    public function paypalSubscriptionWebhook(Request $request) {
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
        }
        return response()->noContent();
    }
}
