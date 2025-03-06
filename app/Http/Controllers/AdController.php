<?php

namespace App\Http\Controllers;

use App\Models\Ad;

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
}
