<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Stripe\StripeClient;
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

    public function createPaypalSubscription(Request $request, Plan $plan) {
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
            'subscription_id' => $response['id'],
            'start' => now(),
            'end' => now()->addDays($plan->interval)
        ]);
        // return redirect()->away($response['links'][0]['href']);
        return response()->json($response);
    }

    public function cancelPaypalSubscription($subscription_id) {
        $provider = new PayPalClient();
        $provider->getAccessToken();
        $provider->cancelSubscription($subscription_id, 'Cancelled by user');
        if (cancelSubscription($subscription_id))
            return response()->json(['message' => 'Subscription canceled']);
        else
            return response()->json(['message' => 'Subscription not found'], 404);
    }

    public function suspendPaypalSubscription($subscription_id) {
        if (suspendSubscription($subscription_id))
            return response()->json(['message' => 'Subscription suspended']);
        else
            return response()->json(['message' => 'Subscription not found'], 404);
    }

    public function reactivatePaypalSubscription($subscription_id) {
        if (reactivateSubscription($subscription_id))
            return response()->json(['message' => 'Subscription reactivated']);
        else
            return response()->json(['message' => 'Subscription not found'], 404);
    }

    public function paypalSubscriptionSuccess(Request $request) {
        $subscription = Subscription::where('subscription_id', $request->subscription_id)->first();
        $subscription->status = SubscriptionStatus::Active->value;
        $subscription->user->is_subscribed = true;
        $subscription->push();
        return response()->json(['message' => 'Subscription activated']);
    }

    public function paypalSubscriptionCancel(Request $request) {
        Log::info('PayPal Cancelled', $request->all());
        Subscription::where('subscription_id', $request->subscription_id)->delete();
        return response()->json(['message' => 'Subscription cancelled']);
    }

    public function createStripeSubscription(Request $request, Plan $plan)
    {
        $stripe = new StripeClient(env('STRIPE_SECRET'));
        $user = $request->user();

        // Check if user has a Stripe customer ID, else create one
        $customer = $stripe->customers->create([
            'email' => $user->email,
            'name' => $user->profile->username,
            'metadata' => ['user_id' => $user->id]
        ]);

        // Create a Stripe Checkout Session for subscription
        $response = $stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'mode' => 'subscription',
            'customer' => $customer->id, // Link to the user
            'line_items' => [[
                'price' => 'price_1R8l3kRaYsRYZa7ehpearIPJ', // Use the correct Stripe Price ID
                'quantity' => 1,
            ]],
            'client_reference_id' => uniqid(more_entropy: true),
            'success_url' => route('payment.stripe.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payment.stripe.cancel').'?session_id={CHECKOUT_SESSION_ID}',
        ]);

        $request->user()->subscription ? $request->user()->subscription->delete() : null;
        Subscription::create([
            'user_id' => $request->user()->id,
            'plan_id' => $plan->id,
            'subscription_id' => $response['client_reference_id'],
            'start' => now(),
            'end' => now()->addDays($plan->interval)
        ]);
        return response()->json($response);
    }

    public function stripeSubscriptionSuccess(Request $request) {
        $stripe = new StripeClient(env('STRIPE_SECRET'));
        $session = $stripe->checkout->sessions->retrieve($request->session_id);
        $subscription = Subscription::where('subscription_id', $session->client_reference_id)->first();
        $subscription->status = SubscriptionStatus::Active->value;
        $subscription->user->is_subscribed = true;
        $subscription->subscription_id = $session->subscription;
        $subscription->push();
        return response()->json(['message' => 'Subscription activated']);
    }

    public function stripeSubscriptionCancel(Request $request) {
        $stripe = new StripeClient(env('STRIPE_SECRET'));
        $session = $stripe->checkout->sessions->retrieve($request->session_id);
        Subscription::where('subscription_id', $session->client_reference_id)->delete();
        return response()->json(['message' => 'Subscription cancelled']);
    }

    public function suspendStripeSubscription($subscription_id) {
        $stripe = new StripeClient(env('STRIPE_SECRET'));
        $stripe->subscriptions->update(
            $subscription_id,
            ['cancel_at_period_end' => true]
        );

        return response()->json(['message' => 'Auto renewals disabled']);
    }
}
