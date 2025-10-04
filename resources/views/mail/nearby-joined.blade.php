<x-mail::message>
# A new vibe is in town—come say hi 🎶

Hi {{ $firstname }},

Good news—someone new just joined Vibemate in your area! 🌍✨

This could be your chance to:

<ul>
    <li>Meet people who share your vibe</li>
    <li>Discover fresh conversations happening nearby</li>
    <li>Grow your circle with real connections close to home</li>
</ul>

<x-mail::button :url="''" :style="true">
See who joined
</x-mail::button>

Don’t let this opportunity pass you by—your next great connection might be just around the corner.

Stay connected, <br>
The {{ config('app.name') }} Team 💜
</x-mail::message>
