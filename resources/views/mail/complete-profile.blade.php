<x-mail::message>
# You’re almost there… unlock the full Vibemate experience

Hi {{ $firstname }},
Welcome to Vibemate. We’re so glad you joined! 🎶✨. We noticed you haven’t finished setting up your profile yet. Completing your profile helps you:
<ul>
    <li>Connect with people who share your vibe</li>
    <li>Get discovered by new friends faster</li>
    <li>Unlock personalized recommendations</li>
</ul>

It only takes a minute, and you’ll be ready to dive into the full Vibemate experience.

<x-mail::button :url="''" :style="true">
Complete your vibe profile
</x-mail::button>

Don’t miss out on the fun—your next great connection is just a profile away.

See you inside,<br>
The {{ config('app.name') }} Team 💜
</x-mail::message>
