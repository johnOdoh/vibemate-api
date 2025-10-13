<x-mail::message>
# @if($isUrgent) subscription ends soon — don't lose your perks
@else Your Premium Vibe ends soon — keep your perks going Your @endif

Hi {{ $firstname }},

@if($isUrgent)
Your exclusive Premium offer is about to expire. Don’t miss out on perks like:
@else
Your Vibemate Premium subscription is set to expire in 3 days. Don't lose access to all the exclusive features and benefits that come with your Premium membership. Premium perks like:
@endif

<ul>
    <li>🚀 Boosted reach</li>
    <li>🎶 Exclusive challenges</li>
    <li>💬 Priority connections</li>
</ul>

To ensure uninterrupted access to these benefits, simply click the button below to enable Auto-Renewal for your subscription.

<x-mail::button :url="''" :style="true">
Enable Auto-Renewal
</x-mail::button>

Stay in the spotlight,<br>
The {{ config('app.name') }} Premium Team 💜
</x-mail::message>
