<x-mail::message>
# 🔥 This week’s top trends on Vibemate

Hi {{ $firstname }},

Here’s what’s buzzing this week:

🎶 Top hashtag: <strong><em>{{ $data['top_hashtag'] }}</em></strong>

🌟 Most Popular Creator: <strong>{{ $data['most_popular'] }}</strong>

🌟 Most Followed Creator: <strong>{{ $data['most_followed'] }}</strong>

🌟 Most Matched Creator: <strong>{{ $data['most_matched'] }}</strong>

🌟 Most Engaged Post: <strong>{{ $data['most_engaged'] }}</strong>

🏆 Featured Challenge of the week: <strong>{{ $data['featured_challenge'] }}</strong>

<x-mail::button :url="''" :style="true">
Join the Fun
</x-mail::button>

Stay in the loop and keep vibing.

Start vibing,<br>
The {{ config('app.name') }} Team
</x-mail::message>

