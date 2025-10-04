<?php

use Carbon\Carbon;
use App\Models\Post;
use App\Models\User;
use App\Models\Status;
use App\Models\Profile;
use App\Mail\WinnerMail;
use App\Models\Competition;
use App\Models\Subscription;
use App\Mail\CompleteProfile;
use App\Enums\SubscriptionStatus;
use App\Http\Controllers\CompetitionController;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    $competitionController = new CompetitionController();
    Status::where('created_at', '<', Carbon::yesterday())->delete();
    Competition::where('start', '<=', Carbon::today())->update(['phase' => 'active']);
    Competition::where('phase', 'active')
        ->where('end', '<', Carbon::today())
        ->get()
        ->each(function ($competition) use ($competitionController) {
            $competitionController->end($competition);
        });
})->everyMinute();

Schedule::call(function () {
    $most_popular_user = User::orderBy('most_popular_points', 'desc')->first();
    if ($most_popular_user->most_popular_points != 0) {
        $most_popular_user->increment('wallet', 100);
        Mail::to($most_popular_user)->send(new WinnerMail($most_popular_user->profile->firstname, 'Most Popular Profile', $most_popular_user->most_popular_points));
    }
    $most_followed_user = User::orderBy('most_followed_points', 'desc')->first();
    if ($most_followed_user->most_followed_points != 0) {
        $most_followed_user->increment('wallet', 100);
        Mail::to($most_followed_user)->send(new WinnerMail($most_followed_user->profile->firstname, 'Most Followed Profile', $most_followed_user->most_followed_points));
    }
    $most_matched_user = User::orderBy('most_matches_points', 'desc')->first();
    if ($most_matched_user->most_matches_points != 0) {
        $most_matched_user->increment('wallet', 100);
        Mail::to($most_matched_user)->send(new WinnerMail($most_matched_user->profile->firstname, 'Most Matched Profile', $most_matched_user->most_matches_points));
    }
    $most_engaged_post = Post::orderBy('most_engagements_points', 'desc')->first();
    if ($most_engaged_post->most_engagements_points != 0) {
        $most_engaged_post->user->increment('wallet', 100);
        Mail::to($most_engaged_post->user)->send(new WinnerMail($most_engaged_post->user->profile->firstname, 'Most Engaged Post', $most_engaged_post->most_engagements_points));
    }
    Profile::query()->update(['weekly_profile_views' => 0]);
    Post::query()->update(['most_engagements_points' => 0]);
    User::query()->update([
        'most_popular_points' => 0,
        'most_followed_points' => 0,
        'most_matches_points' => 0
    ]);
})->weekly();

Schedule::call(function () {
    DB::table('daily_active_users')->insert([
        'no_of_users' => User::where('was_active', true)->count(),
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
    ]);
    User::query()->update([
        'no_of_swipes' => 0,
        'was_active' => false
    ]);
    DB::transaction(function () {
        User::whereHas('subscriptions', callback: function ($q) {
                $q->where('end', '<', Carbon::today())
                ->where('status', SubscriptionStatus::Active->value);
            })
            ->update(['is_subscribed' => false]);
        Subscription::where('end', '<', Carbon::today())
            ->where('status', SubscriptionStatus::Active->value)
            ->update(['status' => SubscriptionStatus::Expired->value]);
    });
})->daily();

schedule::call(function () {
    User::without('profile')
        ->where('created_at', '<', Carbon::today())
        ->latest()
        // ->limit('5')
        ->chunk(100, function ($users) {
            $users->each(function ($user) {
                Mail::to($user)->send(new CompleteProfile($user->profile->firstname));
            });
        });
})->dailyAt('17:15');
// Schedule::command('queue:work')->everyMinute();
