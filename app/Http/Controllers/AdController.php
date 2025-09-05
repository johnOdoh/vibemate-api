<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Gift;
use App\Models\Interest;

class AdController extends Controller
{
    public function index()
    {
        return Ad::latest()->get()->map(fn ($ad) => [
            'id' => $ad->id,
            'action' => $ad->action,
            'is_image' => $ad->is_image,
            'target_url' => $ad->target_url,
            'file' => $ad->is_image ? "http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/images/ForBiggerJoyrides.jpg" : "http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4",
        ]);
    }

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
    //         ['name' => 'Prayer Hands', 'price' => '50', 'icon' => 'prayer-hands.svg', 'category' => 'Religion'],
    //         ['name' => 'Holy Water', 'price' => '75', 'icon' => 'holy-water.svg', 'category' => 'Religion'],
    //         ['name' => 'Bible', 'price' => '100', 'icon' => 'bible.svg', 'category' => 'Religion'],
    //         ['name' => 'Rosary Beads', 'price' => '150', 'icon' => 'rosary-beads.svg', 'category' => 'Religion'],
    //         ['name' => 'Dove of Peace', 'price' => '200', 'icon' => 'dove-of-peace.svg', 'category' => 'Religion'],
    //         ['name' => 'Candlelight Vigil', 'price' => '250', 'icon' => 'candlelight-vigil.svg', 'category' => 'Religion'],
    //         ['name' => 'Cross Necklace', 'price' => '300', 'icon' => 'cross-necklace.svg', 'category' => 'Religion'],
    //         ['name' => 'Blessing Scroll', 'price' => '350', 'icon' => 'blessing-scroll.svg', 'category' => 'Religion'],
    //         ['name' => 'Sacred Music', 'price' => '400', 'icon' => 'sacred-music.svg', 'category' => 'Religion'],
    //         ['name' => 'Faith Bracelet', 'price' => '500', 'icon' => 'faith-bracelet.svg', 'category' => 'Religion'],
    //         ['name' => 'Angel Figurine', 'price' => '600', 'icon' => 'angel-figurine.svg', 'category' => 'Religion'],
    //         ['name' => 'Worship Song', 'price' => '700', 'icon' => 'worship-song.svg', 'category' => 'Religion'],
    //         ['name' => 'Spiritual Retreat', 'price' => '1000', 'icon' => 'spiritual-retreat.svg', 'category' => 'Religion'],
    //         ['name' => 'Heavenly Glow', 'price' => '1200', 'icon' => 'heavenly-glow.svg', 'category' => 'Religion'],
    //         ['name' => 'Sacred Offering', 'price' => '1500', 'icon' => 'sacred-offering.svg', 'category' => 'Religion'],
    //         ['name' => 'Peaceful Mind', 'price' => '1800', 'icon' => 'peaceful-mind.svg', 'category' => 'Religion'],
    //         ['name' => 'Pilgrimage Token', 'price' => '2000', 'icon' => 'pilgrimage-token.svg', 'category' => 'Religion'],
    //         ['name' => 'Prayer Rug', 'price' => '2500', 'icon' => 'prayer-rug.svg', 'category' => 'Religion'],
    //         ['name' => 'Holy Flame', 'price' => '3000', 'icon' => 'holy-flame.svg', 'category' => 'Religion'],
    //         ['name' => 'Divine Blessing', 'price' => '3500', 'icon' => 'divine-blessing.svg', 'category' => 'Religion'],

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
