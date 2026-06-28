<?php

namespace App\Policies;

use App\Models\BlogPost;
use App\Models\User;
use App\Policies\Concerns\ChecksSchoolAccess;

class BlogPostPolicy
{
    use ChecksSchoolAccess;

    public function before(User $user): ?bool
    {
        return $this->isAdmin($user) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, BlogPost $blogPost): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, BlogPost $blogPost): bool
    {
        return false;
    }

    public function delete(User $user, BlogPost $blogPost): bool
    {
        return false;
    }
}
