<?php

namespace App\Services;

use App\Enums\Permission;
use App\Models\User;

class HelperService
{
    public function owner(): Permission
    {
        return Permission::OWNER();
    }

    public function some(): Permission
    {
        return Permission::SOME_USERS();
    }

    public function all(): Permission
    {
        return Permission::ALL_USERS();
    }

    public function public(): Permission
    {
        return Permission::PUBLIC();
    }

    public function permissions(): array
    {
        return Permission::toArray();
    }

    public function users(): array
    {
        return User::pluck('name', 'id')
            ->filter(fn ($name, $id) => $id != auth()->id())
            ->all();
    }
}
