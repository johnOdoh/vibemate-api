<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Active = 'Active';
    case Suspended = 'Suspended';
    case Cancelled = 'Cancelled';
    case Expired = 'Expired';

    public function label(): string
    {
        return match ($this) {
            Self::Active => 'Subscription Activated',
            Self::Suspended => 'Subscription Suspended',
            Self::Cancelled => 'Subscription Cancelled',
            Self::Expired => 'Subscription Expired',
        };
    }
}
