<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function verify(User $user, Document $document): bool
    {
        return $user->canAccessAdminModule();
    }
}
