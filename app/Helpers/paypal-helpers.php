<?php

use App\Models\Transaction;
use App\Models\Subscription;
use App\Enums\SubscriptionStatus;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

function activateSubscription($subscription_id)
{
    $subscription = Subscription::where('paypal_subscription_id', $subscription_id)->first();
    if (!$subscription) {
        Log::error('Subscription not found', ['id' => $subscription_id]);
        return false;
    }
    $interval = $subscription->plan->interval;
    $subscription->end = now()->addDays($interval);
    $subscription->start = now();
    $subscription->status = SubscriptionStatus::Active->value;
    $subscription->user->is_subscribed = true;
    $subscription->push();
    Log::info('Subscription activated', ['id' => $subscription_id]);
    return true;
}

function suspendSubscription($subscription_id)
{
    $provider = new PayPalClient();
    $provider->getAccessToken();
    $provider->cancelSubscription($subscription_id, 'Cancelled by user');
    $subscription = Subscription::where('paypal_subscription_id', $subscription_id)->first();
    if (!$subscription) {
        Log::error('Subscription not found', ['id' => $subscription_id]);
        return false;
    }
    $subscription->update([
        'status' => SubscriptionStatus::Suspended->value,
        'auto_renew' => false
    ]);
    Log::info('Subscription suspended by user', ['id' => $subscription_id]);
    return true;
}

function reactivateSubscription($subscription_id)
{
    $provider = new PayPalClient();
    $provider->getAccessToken();
    $provider->cancelSubscription($subscription_id, 'Cancelled by user');
    $subscription = Subscription::where('paypal_subscription_id', $subscription_id)->first();
    if (!$subscription) {
        Log::error('Subscription not found', ['id' => $subscription_id]);
        return false;
    }
    $subscription->update([
        'status' => SubscriptionStatus::Active->value,
        'auto_renew' => true
    ]);
    Log::info('Subscription reactivated by user', ['id' => $subscription_id]);
    return true;
}

function cancelSubscription($subscription_id)
{
    $provider = new PayPalClient();
    $provider->getAccessToken();
    $provider->cancelSubscription($subscription_id, 'Cancelled by user');
    $subscription = Subscription::where('paypal_subscription_id', $subscription_id)->first();
    if (!$subscription) {
        Log::error('Subscription not found', ['id' => $subscription_id]);
        return false;
    }
    $subscription->update([
        'status' => SubscriptionStatus::Cancelled->value,
        'auto_renew' => false
    ]);
    Log::info('Subscription cancelled by user', ['id' => $subscription_id]);
    return true;
}

function expireSubscription($subscription_id)
{
    $provider = new PayPalClient();
    $provider->getAccessToken();
    $provider->cancelSubscription($subscription_id, 'Cancelled by user');
    $subscription = Subscription::where('paypal_subscription_id', $subscription_id)->first();
    if (!$subscription) {
        Log::error('Subscription not found', ['id' => $subscription_id]);
        return false;
    }
    $subscription->update([
        'status' => SubscriptionStatus::Expired->value,
        'auto_renew' => false
    ]);
    $subscription->user->is_subscribed = false;
    $subscription->user->push();
    // return response()->json($response);
    Log::info(SubscriptionStatus::Expired->label(), ['id' => $subscription_id]);
    return true;
}

function approvePayment($transaction_ref)
{
    $transaction = Transaction::where('reference', $transaction_ref)->first();
    if (!$transaction) {
        Log::error('Transaction not found', ['reference' => $transaction_ref]);
        return false;
    }
    if ($transaction->title === 'Verification Badge') {
        $transaction->user->has_badge = true;
        $transaction->status = 'APPROVED';
        $transaction->push();
    }
    return true;
}
