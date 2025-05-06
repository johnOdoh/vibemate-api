<?php

use App\Models\Interest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EntryController;
use App\Http\Controllers\MusicController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebhookController;
use App\Http\Middleware\VerifyPaypalWebhook;
use App\Http\Controllers\LivestreamController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\CommentReplyController;
use App\Http\Controllers\EntryCommentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\EntryCommentReplyController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\TicketController;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/resend-email-token', [AuthController::class, 'resendEmailToken']);
    Route::post('/send-reset-token', [AuthController::class, 'sendPasswordResetToken']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

Route::get('subscription/paypal/success', [SubscriptionController::class, 'paypalSubscriptionSuccess']);
Route::get('subscription/paypal/cancel', [SubscriptionController::class, 'paypalSubscriptionCancel']);

Route::get('subscription/paystack/success', [SubscriptionController::class, 'paystackSubscriptionSuccess'])->name('subscription.paystack.success');
Route::get('subscription/paystack/cancel', [SubscriptionController::class, 'paystackSubscriptionCancel'])->name('subscription.paystack.cancel');

Route::get('subscription/stripe/success', [SubscriptionController::class, 'stripeSubscriptionSuccess'])->name('subscription.stripe.success');
Route::get('subscription/stripe/cancel', [SubscriptionController::class, 'stripeSubscriptionCancel'])->name('subscription.stripe.cancel');

Route::get('payment/paypal/success', [PaymentController::class, 'paypalPaymentSuccess'])->name('payment.paypal.success');
Route::get('payment/paypal/cancel', [PaymentController::class, 'paypalPaymentCancel'])->name('payment.paypal.cancel');
Route::post('payment/paypal/payouts', [PaymentController::class, 'paypalPayouts']);

Route::get('payment/paystack/success', [PaymentController::class, 'paystackPaymentSuccess'])->name('payment.paystack.success');
Route::get('payment/paystack/cancel', [PaymentController::class, 'paystackPaymentCancel'])->name('payment.paystack.cancel');

Route::get('payment/stripe/success', [PaymentController::class, 'stripePaymentSuccess'])->name('payment.stripe.success');
Route::get('payment/stripe/cancel', [PaymentController::class, 'stripePaymentCancel'])->name('payment.stripe.cancel');

Route::post('paypal/webhooks', [WebhookController::class, 'paypalWebhooks'])->middleware(VerifyPaypalWebhook::class);
Route::post('stripe/webhooks', [WebhookController::class, 'stripeWebhooks']);
Route::post('sightengine/video-moderation/callback', function (Request $request) {
    $data = $request->all();
    Log::info('Sightengine Video Moderation Callback', $data);
    return response()->noContent();
})->name('sightengine.video-moderation.callback');

ROute::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('ad/increment-views/{id}', [AdController::class, 'incrementViews']);
    Route::post('ad/increment-clicks/{id}', [AdController::class, 'incrementClicks']);

    Route::patch('competition/start/{competition}', [CompetitionController::class, 'start']);
    Route::patch('competition/end/{competition}', [CompetitionController::class, 'end']);

    Route::post('entry/like/{entry}', [EntryController::class, 'like']);
    Route::delete('entry/unlike/{entry}', [EntryController::class, 'unlike']);

    Route::get('interest', function () {
        return Interest::orderBy('name')->get(['id', 'name', 'description', 'icon']);
    });

    Route::get('leaderboard/most-followed', [LeaderboardController::class, 'getMostFollowed']);
    Route::get('leaderboard/most-matches', [LeaderboardController::class, 'getMostMatches']);
    Route::get('leaderboard/most-popular', [LeaderboardController::class, 'getMostPopular']);
    Route::get('leaderboard/most-engaged', [LeaderboardController::class, 'getMostEngaged']);

    Route::get('livestream/{livestream}', [LivestreamController::class, 'show']);
    Route::post('livestream', [LivestreamController::class, 'store']);
    Route::post('livestream/gift/{livestream}', [LivestreamController::class, 'sendGift']);
    Route::post('livestream/comment/{livestream}', [LivestreamController::class, 'comment']);
    Route::get('livestream/comments/{livestream}', [LivestreamController::class, 'getComments']);
    Route::post('livestream/viewer-joins/{livestream}', [LivestreamController::class, 'viewerJoins']);
    Route::put('livestream/viewer-leaves/{livestream}', [LivestreamController::class, 'viewerLeaves']);
    Route::put('livestream/remove-viewer/{livestream}', [LivestreamController::class, 'removeViewer']);
    Route::put('livestream/change-moderator/{livestream}', [LivestreamController::class, 'changeModerator']);
    Route::post('livestream/send-request/{livestream}', [LivestreamController::class, 'sendRequest']);
    Route::put('livestream/accept-request/{livestream}', [LivestreamController::class, 'acceptRequest']);
    Route::put('livestream/end/{livestream}', [LivestreamController::class, 'endLivestream']);

    Route::get('music/tracklist', [MusicController::class, 'tracklist']);
    Route::get('music/search', [MusicController::class, 'search']);

    Route::get('notification', [NotificationController::class, 'index']);
    Route::post('notification', [NotificationController::class, 'store']);
    Route::get('notification/{notification}', [NotificationController::class, 'show']);
    Route::put('notification/mark-as-read/{notification}', [NotificationController::class, 'markAsRead']);
    Route::put('notification/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('notification/{notification}', [NotificationController::class, 'delete']);

    Route::post('payment/paypal/create', [PaymentController::class, 'createPaypalPayment']);
    Route::post('payment/paystack/create', [PaymentController::class, 'createPaystackPayment']);
    Route::post('payment/stripe/create', [PaymentController::class, 'createStripePayment']);
    Route::post('payment/reloadly/get-giftcards', [PaymentController::class, 'getGiftCards']);

    Route::get('post/getTopHashtags', [PostController::class, 'getTopHashtags']);
    Route::post('post/like/{post}', [PostController::class, 'like']);
    Route::put('post/unlike/{post}', [PostController::class, 'unlike']);

    Route::post('profile/check-username', [ProfileController::class, 'checkUsername']);
    Route::put('profile/change-password', [ProfileController::class, 'changePassword']);
    Route::put('profile/change-image', [ProfileController::class, 'changeImage']);

    Route::get('status/get-viewers/{status}', [StatusController::class, 'getViewers']);

    Route::post('subscription/create-plan', [SubscriptionController::class, 'createPlan']);
    Route::post('subscription/paypal/create/{plan}', [SubscriptionController::class, 'createPaypalSubscription']);
    Route::patch('subscription/paypal/cancel/{subscription_id}', [SubscriptionController::class, 'cancelPaypalSubscription']);
    Route::patch('subscription/paypal/reactivate/{subscription_id}', [SubscriptionController::class, 'reactivatePaypalSubscription']);
    Route::patch('subscription/paypal/suspend/{subscription_id}', [SubscriptionController::class, 'suspendPaypalSubscription']);

    Route::post('subscription/paystack/create/{plan}', [SubscriptionController::class, 'createPaystackSubscription']);
    Route::patch('subscription/paystack/cancel/{subscription_id}', [SubscriptionController::class, 'cancelPaystackSubscription']);
    Route::patch('subscription/paystack/reactivate/{subscription_id}', [SubscriptionController::class, 'reactivatePaystackSubscription']);
    Route::patch('subscription/paystack/suspend/{subscription_id}', [SubscriptionController::class, 'suspendPaystackSubscription']);

    Route::post('subscription/stripe/create/{plan}', [SubscriptionController::class, 'createStripeSubscription']);
    Route::post('subscription/stripe/suspend/{subscription_id}', [SubscriptionController::class, 'suspendStripeSubscription']);
    Route::post('subscription/stripe/reactivate/{subscription_id}', [SubscriptionController::class, 'reactivateStripeSubscription']);

    Route::get('user/get-affiliate', [UserController::class, 'getAffiliatePage']);
    Route::post('user/follow/{id}', [UserController::class, 'follow']);
    Route::post('user/like/{id}', [UserController::class, 'like']);
    Route::put('user/unfollow/{id}', [UserController::class, 'unfollow']);
    Route::delete('user/unlike/{id}', [UserController::class, 'unlike']);
    Route::delete('user/logout', [UserController::class, 'logout']);

    Route::resource('comment', CommentController::class);
    Route::resource('competition', CompetitionController::class);
    Route::resource('entry', EntryController::class);
    Route::resource('entry-comment', EntryCommentController::class);
    Route::resource('entry-comment-reply', EntryCommentReplyController::class);
    Route::resource('post', PostController::class);
    Route::resource('profile', ProfileController::class);
    Route::resource('reply', CommentReplyController::class);
    Route::resource('status', StatusController::class);
    Route::resource('ticket', TicketController::class);
    Route::resource('user', UserController::class);
});
