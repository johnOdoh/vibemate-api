<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Gift;
use App\Models\Interest;

class AdController extends Controller
{
    public function incrementViews($id)
    {
        $ad = Ad::find($id);
        $ad->no_of_views++;
        $ad->save();
        return response('success', 200);
    }

    public function incrementClicks($id)
    {
        $ad = Ad::find($id);
        $ad->no_of_clicks++;
        $ad->save();
        return response('success', 200);
    }

    // public function createGift()
    // {
    //    $gifts = [
    //         ['name' => 'Red Rose', 'icon' => 'rose-gift.svg', 'category' => 'Love & Romance', 'price' => '50'],
    //         ['name' => 'Love Letter', 'price' => '75', 'icon' => 'love-letter.svg', 'category' => 'Love & Romance'],
    //         ['name' => 'Heart Balloons', 'price' => '100', 'icon' => 'red-balloons.svg', 'category' => 'Love & Romance'],
    //         ['name' => 'Coins Bag', 'price' => '50', 'icon' => 'coins.svg', 'category' => 'Love & Romance'],
    //         ['name' => 'Chocolate Box', 'price' => '150', 'icon' => 'chocolate.svg', 'category' => 'Love & Romance'],
    //         ['name' => 'Hug Bear', 'price' => '200', 'icon' => 'couple-bear-valentine.svg', 'category' => 'Love & Romance'],
    //         ['name' => 'Cupid\'s Arrow', 'price' => '250', 'icon' => 'cupids-arrow.svg', 'category' => 'Love & Romance'],
    //         ['name' => 'Romantic Candle', 'price' => '3500', 'icon' => 'flame-of-liberty.svg', 'category' => 'Love & Romance'],
    //         ['name' => 'Love Potion', 'price' => '300', 'icon' => 'love-potion.svg', 'category' => 'Love & Romance'],
    //         ['name' => 'Heart Confetti', 'price' => '300', 'icon' => 'confetti-heart.svg', 'category' => 'Love & Romance'],
    //         ['name' => 'Kissing Lips', 'price' => '600', 'icon' => 'kiss.svg', 'category' => 'Love & Romance'],
    //         ['name' => 'Couple Holding Hands', 'price' => '700', 'icon' => 'couple-dating.svg', 'category' => 'Love & Romance'],
    //         ['name' => 'Love Music', 'price' => '500', 'icon' => 'music.svg', 'category' => 'Love & Romance'],
    //         ['name' => 'Diamond Ring', 'price' => '250', 'icon' => 'engagement-ring.svg', 'category' => 'Love & Romance'],
    //         ['name' => 'Romantic Picnic', 'price' => '1200', 'icon' => 'romantic-picnic.svg', 'category' => 'Love & Romance'],
    //         ['name' => 'Heart Beats', 'price' => '2000', 'icon' => 'heartbeat.svg', 'category' => 'Love & Romance'],
    //         ['name' => 'Couples T-shirt', 'price' => '2500', 'icon' => 'couples-tshirt.svg', 'category' => 'Love & Romance'],
    //         ['name' => 'Sunset Walk', 'price' => '500', 'icon' => 'sunset-sunrise.svg ', 'category' => 'Love & Romance'],
    //         ['name' => 'Engagement Box', 'price' => '1800', 'icon' => 'avatar.png', 'category' => 'Love & Romance'],
    //         ['name' => 'I Love You Card', 'price' => '400', 'icon' => 'avatar.png', 'category' => 'Love & Romance'],

    //         // ['name' => 'First Kiss', 'price' => '75', 'icon' => '', 'category' => 'Love & Romance'],
    //         // ['name' => '', 'price' => '75', 'icon' => '', 'category' => 'Love & Romance'],

    //     ];
    //     Gift::insert($gifts);
    //     return response('Gifts created successfully', 200);
    // }

        // public function createInterest()
    // {
        // $interest = [
        //     ['name' => 'Music', 'icon' => 'music.svg', 'description' => 'Find people who share your taste in music'],
        //     ['name' => 'Sports & Outdoor', 'icon' => 'sports.svg', 'description' => 'Connect with sports enthusiasts'],
        //     ['name' => 'Art & Design', 'icon' => 'art.svg', 'description' => 'Meet creative souls who live for visuals and expression'],
        //     ['name' => 'Gaming', 'icon' => 'gaming.svg', 'description' => 'Squad up with gamers and streamers who get your vibe'],
        //     ['name' => 'Food & Cooking', 'icon' => 'food.svg', 'description' => 'Meet foodies and home chefs who love sharing flavours'],
        //     ['name' => 'Travel & Adventure', 'icon' => 'travel.svg', 'description' => 'Link up with globe trotters and thrill-seekers'],
        //     ['name' => 'Movies & TV Shows', 'icon' => 'movies.svg', 'description' => 'Meet fellow binge-watchers and cinema fans'],
        //     ['name' => 'Fashion & Style', 'icon' => 'fashion.svg', 'description' => 'Find stylish matches who love to dress and impress'],
        //     ['name' => 'Dancing', 'icon' => 'dancing.svg', 'description' => 'Move to the rhythm with fellow dance lovers'],
        //     ['name' => 'Photos & Videos', 'icon' => 'photos.svg', 'description' => 'Connect with creators who see life through the lens'],
        //     ['name' => 'Tech & Gadgets', 'icon' => 'tech.svg', 'description' => 'Connect with tech enthusiasts and gadget lovers'],
        //     ['name' => 'Comedy & Memes', 'icon' => 'comedy.svg', 'description' => 'Share laughs and memes with like-minded individuals'],
        //     ['name' => 'Entrepreneurship', 'icon' => 'entrepreneurship.svg', 'description' => 'Network with go-getters and future business moguls'],
        //     ['name' => 'Party & Nightlife', 'icon' => 'party.svg', 'description' => 'Link with party people who love the nightlife energy'],
        //     ['name' => 'Books & Literature', 'icon' => 'books.svg', 'description' => 'Met fellow bookworms and deep thinkers'],
        //     ['name' => 'Self-Care & Wellness', 'icon' => 'self-care.svg', 'description' => 'Find people who value mental health and balance'],
        //     ['name' => 'Animals & Pets', 'icon' => 'animals.svg', 'description' => 'Connect with fellow animal lovers and pet parents'],
        //     ['name' => 'Social Media & Influencers', 'icon' => 'social.svg', 'description' => 'Meet content creators and digital stars like you']
        // ];
        // Interest::insert($interest);
        // return response('Interests created successfully', 200);
    // }
}
