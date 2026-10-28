<?php

namespace App\Policies;

use App\Models\FindingTemplate;
use App\Models\User;

class FindingTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, FindingTemplate $template): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, FindingTemplate $template): bool
    {
        return $user->isAdmin();
    }
}
