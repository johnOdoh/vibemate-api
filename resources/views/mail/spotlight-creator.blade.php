<x-mail::message>
# Meet this week’s spotlight creator ✨

Hi {{ $firstname }},

Every week we shine a light on creators making waves. This week’s spotlight is <strong>{{ $creator }}</strong>, known for their unique style and content.

<x-mail::button :url="''" :style="true">
👉 Check Out Their Profile
</x-mail::button>

Get inspired and keep building your own vibe.

Join the fun,<br>
The {{ config('app.name') }} Team 💜
</x-mail::message>


