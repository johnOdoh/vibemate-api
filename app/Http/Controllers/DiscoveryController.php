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
        return $request->user()->discovery()->get()->map(fn($discovery) => [
            'location' => $discovery->location,
            'distance' => $discovery->distance,
            'min_age' => $discovery->min_age,
            'max_age' => $discovery->max_age,
            'min_photos' => $discovery->min_photo,
            'relationship_type' => $discovery->relationship_type,
            'open_to' => explode(', ', $discovery->open_to),
            'languages' => explode(', ', $discovery->languages),
            'zodiac' => $discovery->zodiac,
            'height' => $discovery->height,
            'education' => $discovery->education,
            'family_plan' => $discovery->family_plan,
            'covid_vaccine' => $discovery->covid_vaccine,
            'personality_style' => $discovery->personality_style,
            'love_style' => $discovery->love_style,
            'communication_style' => $discovery->communication_style,
            'pets' => $discovery->pets,
            'drinking' => $discovery->drinking,
            'communication' => $discovery->communication,
            'working' => [
                'company' => $discovery->employment_company,
                'role' => $discovery->employment_role,
                'location' => $discovery->employment_location,
                'type' => $discovery->employment_type
            ],
            'down_to_meet' => explode(', ', $discovery->down_to_meet)
        ]);
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
            case 'min_photos':
                $user->discovery->update(['min_photo' => $request->data]);
                break;
            case 'relationship_type':
                $user->discovery->update(['relationship_type' => $request->data]);
                break;
            case 'open_to':
                $user->discovery->update(['open_to' => implode(', ', $request->data)]);
                break;
            case 'languages':
                $user->discovery->update(['languages' => implode(', ', $request->data)]);
                break;
            case 'zodiac':
                $user->discovery->update(['zodiac' => $request->data]);
                break;
            case 'height':
                $user->discovery->update(['height' => $request->data]);
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
            case 'communication':
                $user->discovery->update(['communication' => $request->data]);
                break;
            case 'working':
                $user->discovery->update([
                    'employment_company' => $request->company,
                    'employment_role' => $request->role,
                    'employment_location' => $request->location,
                    'employment_type' => $request->type,
                ]);
                break;
            case 'down_to_meet':
                $user->discovery->update(['down_to_meet' => implode(', ', $request->data)]);
                break;
            default:
                return response()->json(['error' => 'Invalid field'], 403);
        }
        return response()->json(['message' => 'Discovery updated successfully']);
    }
}
