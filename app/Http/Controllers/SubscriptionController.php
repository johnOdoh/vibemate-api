<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Stripe\StripeClient;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Enums\SubscriptionStatus;
use Illuminate\Support\Facades\Http;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class SubscriptionController extends Controller
{
    public function createPaypalSubscription(Request $request, Plan $plan)
    {
        $provider = new PayPalClient();
        $provider->getAccessToken();
        $response = $provider->addPaymentFailureThreshold(1)
                    ->addProductById($plan->paypal_product_id)
                    ->addBillingPlanById($plan->paypal_plan_id)
                    ->setReturnAndCancelUrl(route('subscription.paypal.success'), route('subscription.paypal.cancel'))
                    ->setupSubscription($request->user()->profile->username, $request->user()->email, now()->addSeconds(60));

        if ($response['status'] !== 'APPROVAL_PENDING') {
            return response()->json(['message' => 'Failed to create subscription'], 500);
        }
        $request->user()->subscription ? $request->user()->subscription->delete() : null;
        Subscription::create([
            'user_id' => $request->user()->id,
            'plan_id' => $plan->id,
            'subscription_id' => $response['id'],
            'mode' => 'paypal',
            'start' => now(),
            'end' => now()->addDays($plan->interval)
        ]);
        // return redirect()->away($response['links'][0]['href']);
        return response()->json(['url' => $response['links'][0]['href']]);
    }

    public function paypalSubscriptionSuccess(Request $request)
    {
        $subscription = Subscription::where('subscription_id', $request->subscription_id)->firstOrFail();
        activateSubscription($subscription->subscription_id);
        return response()->json(['message' => 'Subscription activated']);
    }

    public function paypalSubscriptionCancel(Request $request)
    {
        Subscription::where('subscription_id', $request->subscription_id)->delete();
        return response()->json(['message' => 'Subscription cancelled']);
    }

    public function cancelPaypalSubscription($subscription_id)
    {
        $provider = new PayPalClient();
        $provider->getAccessToken();
        $provider->cancelSubscription($subscription_id, 'Cancelled by user');
        return cancelSubscription($subscription_id);
    }

    public function suspendPaypalSubscription($subscription_id)
    {
        $provider = new PayPalClient();
        $provider->getAccessToken();
        $provider->suspendSubscription($subscription_id, 'Suspended by user');
        return suspendSubscription($subscription_id);
    }

    public function reactivatePaypalSubscription($subscription_id)
    {
        $provider = new PayPalClient();
        $provider->getAccessToken();
        $provider->activateSubscription($subscription_id, 'Reactivated by user');
        return reactivateSubscription($subscription_id);
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
                'price' => $plan->stripe_price_id, // Use the correct Stripe Price ID
                'quantity' => 1,
            ]],
            'client_reference_id' => uniqid(more_entropy: true),
            'success_url' => route('subscription.stripe.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('subscription.stripe.cancel').'?session_id={CHECKOUT_SESSION_ID}',
        ]);
        if ($response->status !== 'open') {
            return response()->json(['message' => 'Failed to create subscription'], 500);
        }

        $request->user()->subscription ? $request->user()->subscription->delete() : null;
        Subscription::create([
            'user_id' => $request->user()->id,
            'plan_id' => $plan->id,
            'subscription_id' => $response['client_reference_id'],
            'mode' => 'stripe',
            'start' => now(),
            'end' => now()->addDays($plan->interval)
        ]);
        return response()->json([ 'url' => $response['url'] ]);
    }

    public function stripeSubscriptionSuccess(Request $request)
    {
        $stripe = new StripeClient(env('STRIPE_SECRET'));
        $session = $stripe->checkout->sessions->retrieve($request->session_id);
        $subscription = Subscription::where('subscription_id', $session->client_reference_id)->firstOrFail();
        activateSubscription($subscription->subscription_id, $session->subscription);
        // $subscription->status = SubscriptionStatus::Active->value;
        // $subscription->user->is_subscribed = true;
        // $subscription->subscription_id = $session->subscription;
        // $subscription->push();
        return response()->json(['message' => 'Subscription activated']);
    }

    public function stripeSubscriptionCancel(Request $request)
    {
        $stripe = new StripeClient(env('STRIPE_SECRET'));
        $session = $stripe->checkout->sessions->retrieve($request->session_id);
        Subscription::where('subscription_id', $session->client_reference_id)->delete();
        return response()->json(['message' => 'Subscription cancelled']);
    }

    public function suspendStripeSubscription($subscription_id)
    {
        $stripe = new StripeClient(env('STRIPE_SECRET'));
        $stripe->subscriptions->update(
            $subscription_id,
            ['cancel_at_period_end' => true]
        );
        return suspendSubscription($subscription_id);
    }

    public function reactivateStripeSubscription($subscription_id)
    {
        $stripe = new StripeClient(env('STRIPE_SECRET'));
        $stripe->subscriptions->update(
            $subscription_id,
            ['cancel_at_period_end' => false]
        );
        return reactivateSubscription($subscription_id);
    }

    public function createPaystackSubscription(Request $request, Plan $plan)
    {
        Http::withToken(env('PAYSTACK_SECRET_KEY'))
            ->withHeader('Content-Type', 'application/json')
            ->post('https://api.paystack.co/customer', [
                'email' => $request->user()->email,
                'first_name' => $request->user()->profile->firstname,
                'last_name' => $request->user()->profile->lastname,
                'phone' => $request->user()->profile->phone,
            ]);

        $response = Http::withToken(env('PAYSTACK_SECRET_KEY'))
            ->withHeader('Content-Type', 'application/json')
            ->post('https://api.paystack.co/transaction/initialize', [
                'email' => $request->user()->email,
                'amount' => $plan->price,
                'plan' => $plan->paystack_plan_id,
                'callback_url' => route('subscription.paystack.success')
            ]);

        if ($response->failed()) return response()->json(['message' => 'Failed to create subscription'], 500);

        $request->user()->subscription ? $request->user()->subscription->delete() : null;
        $transaction = $response->json();
        Subscription::create([
            'user_id' => $request->user()->id,
            'plan_id' => $plan->id,
            'subscription_id' => $transaction['data']['reference'],
            'mode' => 'paystack',
            'start' => now(),
            'end' => now()->addDays($plan->interval)
        ]);
        return response()->json(['url' => $transaction['data']['authorization_url']]);
    }

    public function paystackSubscriptionSuccess(Request $request)
    {
        $subscription = Subscription::where('subscription_id', $request->reference)->firstOrFail();
        activateSubscription($subscription->subscription_id);
        return response()->json(['message' => 'Subscription activated']);
    }

    public function paystackSubscriptionCancel(Request $request)
    {
        Subscription::where('subscription_id', $request->reference)->delete();
        return response()->json(['message' => 'Transaction Cancelled']);
    }
}
