<?php

use Illuminate\Support\Facades\Auth;

    if (!function_exists('check_permission')) {
        function check_permission(string $permission, bool $abort = true): bool
        {
            $user = Auth::user();

            if (!$user || !$user->can($permission)) {
                if ($abort) {
                    abort(403, 'Unauthorized');
                }
                return false;
            }

            return true;
        }
    }

    if (!function_exists('user_has_role')) {
        function user_has_role(array $roles): bool
        {
            $user = Auth::user();

            if (!$user) return false;

            return $user->roles->pluck('name')->intersect($roles)->isNotEmpty();
        }
    }
