<x-mail::message>
# Invite friends, earn £2 each! 🎁

Hi {{ $firstname }},

Good vibes are better shared. Invite your friends to Vibemate and you both earn £2 when they join.

<x-mail::button :url="''" :style="true">
Invite Friends Now
</x-mail::button>

The more you share, the more you earn.

Join the fun,<br>
The {{ config('app.name') }} Team 💜
</x-mail::message>

