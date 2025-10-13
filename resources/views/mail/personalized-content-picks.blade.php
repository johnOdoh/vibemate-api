<x-mail::message>
# Fresh picks just for you 🎯

Hi {{ $firstname }},

We’ve handpicked new creators and content based on what you love. From {{ $interests[0] }} to {{ $interests[1] }}, there’s plenty to explore.

<x-mail::button :url="''" :style="true">
Discover New Creators
</x-mail::button>

Keep vibing,<br>
The {{ config('app.name') }} Team 💜
</x-mail::message>
