<x-mail::message>
# 🏆 Congratulations {{ $firstname }}! You got {{ $position }} position in the {{ $competition }} Challenge!

Hi {{ $firstname }},

Big congratulations are in order. You’ve officially secured {{ $position }} place in the {{ $competition }} <em>({{ $hashtag }})</em> Challenge on Vibemate! 🎉🔥

Your energy, creativity, and consistency stood out, and the community has noticed. As a reward for your achievement, you’ve earned {{ $prize }}VBM which has been credited to your wallet.

This is your moment to shine—don’t forget to share your win and inspire others to join the next challenge.

<x-mail::button :url="''" :style="true">
Claim My Prize
</x-mail::button>

Thank you for bringing your vibe to Vibemate and making our community even more exciting. We can’t wait to see what you’ll do next!

Keep vibing, <br>
The {{ config('app.name') }} Team 💜
</x-mail::message>
