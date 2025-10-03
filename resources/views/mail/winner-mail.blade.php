<x-mail::message>
# You’re this week’s {{ $category }}!

Hi {{ $firstname }},

You did it! 🏆 This week, you climbed to the very top of the {{ $category }} Leaderboard with {{ $points }} points and claimed the #1 spot. That’s no small feat. You’ve been setting the vibe, sparking conversations, and keeping the community buzzing.

Here’s what your win means:

🌟 You’re officially this week’s {{ $category }}

🎖 Your wallet will be credited with 100VBM

💬 Everyone knows you brought the energy that kept Vibemate alive this week

But the fun doesn’t stop here—the leaderboard resets soon, and challengers are already lining up. Can you defend your crown next week?

<x-mail::button :url="''" :style="true">
Check the Leaderboard
</x-mail::button>

Thanks for making Vibemate the vibrant community it is. Keep vibing, keep connecting, and keep inspiring others.

Stay legendary, <br>
The {{ config('app.name') }} Team 💜
</x-mail::message>
