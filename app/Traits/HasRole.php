<?php

declare(strict_types=1);

namespace App\Traits;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;

trait HasRole
{
    public function hasRole(string|UserRole $role): bool
    {
        $this->resoleRole(role: $role);

        return $this->role === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Scope a query to only include users with the specified role.
     */
    public function scopeRole(Builder $query, string|UserRole $role): Builder
    {
        $this->resoleRole(role: $role);

        return $query->where('role', $role);
    }

    /**
     * Resolves a string or UserRole input into a UserRole enum instance.
     *
     * @param  string|UserRole  $role  The role to resolve
     * @return UserRole The resolved UserRole enum or null if invalid
     */
    private function resoleRole(string|UserRole $role): UserRole
    {
        if (is_string($role)) {
            $role = UserRole::tryFrom(mb_strtolower($role));
        }

        return $role;
    }
}
