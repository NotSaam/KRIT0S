<?php

namespace App\Policies;

use App\Models\Engagement;
use App\Models\User;

class EngagementPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Engagement $engagement): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isClient()) {
            return $engagement->client_id === $user->client_id && $engagement->isPublished();
        }

        return $engagement->hasMember($user);
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    /** Covers editing the engagement itself and its findings. */
    public function update(User $user, Engagement $engagement): bool
    {
        return $user->isAdmin() || ($user->isPentester() && $engagement->hasMember($user));
    }

    public function delete(User $user, Engagement $engagement): bool
    {
        return $user->isAdmin();
    }

    /** Only the engagement lead (or an admin) signs off the verdict. */
    public function publish(User $user, Engagement $engagement): bool
    {
        return $user->isAdmin() || $engagement->lead_id === $user->id;
    }

    public function generateReport(User $user, Engagement $engagement): bool
    {
        return $this->update($user, $engagement);
    }

    public function downloadReport(User $user, Engagement $engagement): bool
    {
        return $this->view($user, $engagement);
    }
}
