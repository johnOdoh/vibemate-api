<x-mail::message>
@if($long)
# Your vibe still has value — come back today!
@else
# We miss your vibe, {{ $firstname }} 💜
@endif

Hi {{ $firstname }},

@if($long)
It’s been a while since we’ve seen you on Vibemate. Did you know you can still:

<ul>
    <li>💸 Earn rewards for engagement</li>
    <li>🌟 Connect with top creators</li>
    <li>🎶 Join trending challenges</li>
</ul>
@else
It’s been a little quiet without you on Vibemate. Meanwhile, new challenges are trending and creators nearby are making waves. Don’t miss out on the fun!
@endif

<x-mail::button :url="''" :style="true">
@if ($long)
👉Return to Vibemate
@else
Join a Trending Challenge Now
@endif
</x-mail::button>

Your community is waiting.

Stay connected, <br>
The {{ config('app.name') }} Team 🎶
</x-mail::message>
