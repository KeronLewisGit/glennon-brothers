<?php

namespace App\Policies;

use App\Enums\Area;
use App\Models\DeliveryBooking;
use App\Models\User;

class DeliveryBookingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->works(Area::Deliveries);
    }

    public function view(User $user, DeliveryBooking $booking): bool
    {
        return $user->works(Area::Deliveries);
    }

    public function update(User $user, DeliveryBooking $booking): bool
    {
        return $user->works(Area::Deliveries);
    }
}
