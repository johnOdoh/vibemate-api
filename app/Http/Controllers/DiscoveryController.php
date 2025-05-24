<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DiscoveryController extends Controller
{
    public function show(Request $request)
    {
        if (!$request->user()->discovery->exists()) {
            $request->user()->discovery()->create();
        }
        return request()->user()->discovery;
    }

    public function update(Request $request)
    {
        if (!$request->user()->discovery->exists()) {
            $request->user()->discovery()->create();
        }
        $field = $request->query('field');
        $user = $request->user();
        switch ($field) {
            case 'location':
                $user->discovery->update(['location' => $request->data]);
                break;
            case 'distance':
                $user->discovery->update(['distance' => $request->data]);
                break;
            case 'age_range':
                $user->discovery->update(['min_age' => $request->min, 'max_age' => $request->max]);
                break;
            case 'min_photo':
                $user->discovery->update(['min_photo' => $request->data]);
                break;
            case 'open_to':
                $user->discovery->update(['open_to' => $request->data]);
                break;
            case 'languages':
                $user->discovery->update(['languages' => implode(', ', $request->data)]);
                break;
            case 'zodiac':
                $user->discovery->update(['zodiac' => $request->data]);
                break;
            case 'education':
                $user->discovery->update(['education' => $request->data]);
                break;
            case 'family_plan':
                $user->discovery->update(['family_plan' => $request->data]);
                break;
            case 'covid_vaccine':
                $user->discovery->update(['covid_vaccine' => $request->data]);
                break;
            case 'personality_style':
                $user->discovery->update(['personality_style' => $request->data]);
                break;
            case 'love_style':
                $user->discovery->update(['love_style' => $request->data]);
                break;
            case 'communication_style':
                $user->discovery->update(['communication_style' => $request->data]);
                break;
            case 'pets':
                $user->discovery->update(['pets' => $request->data]);
                break;
            case 'drinking':
                $user->discovery->update(['drinking' => $request->data]);
                break;
            case 'smoking':
                $user->discovery->update(['smoking' => $request->data]);
                break;
            case 'communication':
                $user->discovery->update(['communication' => $request->data]);
                break;
            default:
                return response()->json(['error' => 'Invalid field'], 403);
        }
        return response()->json(['message' => 'Discovery updated successfully']);
    }
}
