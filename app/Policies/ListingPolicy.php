<?php

namespace App\Policies;

use App\Models\Listing;
use App\Models\User;

class ListingPolicy
{
    /**
     * Active and sold listings are public; inactive ones only to their owner.
     */
    public function view(?User $user, Listing $listing): bool
    {
        return $listing->status !== Listing::STATUS_INACTIVE || $this->owns($user, $listing);
    }

    public function update(User $user, Listing $listing): bool
    {
        return $this->owns($user, $listing);
    }

    public function delete(User $user, Listing $listing): bool
    {
        return $this->owns($user, $listing);
    }

    private function owns(?User $user, Listing $listing): bool
    {
        return $user !== null && $user->id === $listing->user_id;
    }
}
