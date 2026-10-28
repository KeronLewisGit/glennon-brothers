<?php

namespace App\Policies;

use App\Enums\Area;
use App\Models\QuoteRequest;
use App\Models\User;

/** Sales and administrators. Both regional teams can see every quote, so cover is easy when someone is away. */
class QuoteRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->works(Area::Quotes);
    }

    public function view(User $user, QuoteRequest $quote): bool
    {
        return $user->works(Area::Quotes);
    }

    public function update(User $user, QuoteRequest $quote): bool
    {
        return $user->works(Area::Quotes);
    }

    /** The customer's own cutting list or schedule. */
    public function openSchedule(User $user, QuoteRequest $quote): bool
    {
        return $quote->schedule_path !== null && $this->view($user, $quote);
    }
}
