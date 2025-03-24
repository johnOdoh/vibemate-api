<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
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
        $provider = new PayPalClient();
        $provider->getAccessToken();
        $response = $provider->cancelSubscription($subscription_id, 'Cancelled by user');
        $subscription = Subscription::where('paypal_subscription_id', $subscription_id)->first();
        if (!$subscription) return response()->json(['message' => 'Subscription not found'], 404);
        $subscription->update([
            'status' => "cancelled",
            'auto_renew' => false
        ]);
        $subscription->user->is_subscribed = false;
        $subscription->user->push();
        // return response()->json($response);
        return response()->json(['message' => 'Subscription canceled']);
    }

    public function suspendSubscription($subscription_id) {
        $provider = new PayPalClient();
        $provider->getAccessToken();
        $response = $provider->suspendSubscription($subscription_id, 'User disabled auto-renewal');
        $subscription = Subscription::where('paypal_subscription_id', $subscription_id)->first();
        if (!$subscription) return response()->json(['message' => 'Subscription not found'], 404);
        $subscription->update([
            'auto_renew' => false
        ]);
        return response()->json(['message' => 'Subscription suspended']);
        // return response()->json($response);
    }

    public function reactivateSubscription($subscription_id) {
        $provider = new PayPalClient();
        $provider->getAccessToken();
        $response = $provider->activateSubscription($subscription_id, 'User enabled auto-renewal');
        $subscription = Subscription::where('paypal_subscription_id', $subscription_id)->first();
        if (!$subscription) return response()->json(['message' => 'Subscription not found'], 404);
        $subscription->update([
            'auto_renew' => true
        ]);
        // return response()->json($response);
        return response()->json(['message' => 'Subscription reactivated']);
    }

    public function success(Request $request) {
        Log::info('PayPal Successful', $request->all());
        $subscription = Subscription::where('paypal_subscription_id', $request->subscription_id)->first();
        $subscription->status = 'active';
        $subscription->user->is_subscribed = true;
        $subscription->push();
        return response()->json(['message' => 'Subscription activated']);
    }

    public function cancel(Request $request) {
        Log::info('PayPal Cancelled', $request->all());
        $subscription = Subscription::where('paypal_subscription_id', $request->subscription_id)->first();
        $subscription->delete();
        return response()->json(['message' => 'Subscription cancelled']);
    }

    public function paypalSubscriptionWebhook(Request $request) {
        Log::info('PayPal Webhook', $request->all());
        response()->json(['message' => 'Webhook received']);
    }

}
