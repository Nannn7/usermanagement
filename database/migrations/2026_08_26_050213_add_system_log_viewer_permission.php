<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Usermanagement\Models\Permission;
use Modules\Usermanagement\Models\PermissionGroup;
use Modules\Usermanagement\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Fix for QAX Pentest Report 2026-08-19, finding 4.2 "Log Viewer exposed
 * without authentication" (Medium Risk).
 *
 * opcodesio/log-viewer ships with a default `viewLogViewer` Gate that only
 * blocks access when APP_ENV=production — in any other environment
 * (local, staging, uat, ...) it is open to everyone, authenticated or not,
 * unless the app explicitly defines its own authorization rule. Our
 * staging box is not running APP_ENV=production, which is exactly why QAX
 * could hit /log-viewer anonymously.
 *
 * This migration adds a granular `system.log_viewer` permission (same
 * pattern as the letter/meeting/workplan *_action permissions added in
 * 2026_07_21_000001) so log access can be granted per-role from the Role
 * management screen instead of being tied to environment name or a
 * hardcoded role. It is mirrored onto the existing `administrator` role
 * only — nobody else gets it by default, since raw log files can contain
 * stack traces, file paths, and other sensitive debugging data.
 *
 * The actual Gate::define('viewLogViewer', ...) wiring lives in
 * App\Providers\AppServiceProvider (host app), checking
 * $user->can('system.log_viewer').
 */
return new class extends Migration
{
    public function up(): void
    {
        $group = PermissionGroup::withTrashed()->firstOrCreate(
            ['name' => 'system'],
            ['slug' => 'system']
        );

        if ($group->trashed()) {
            $group->restore();
        }

        Permission::withTrashed()->updateOrCreate(
            ['name' => 'system.log_viewer', 'guard_name' => 'web'],
            ['permission_group_id' => $group->id]
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $administrator = Role::withTrashed()->where('name', 'administrator')->first();

        if ($administrator && !$administrator->hasPermissionTo('system.log_viewer')) {
            $administrator->givePermissionTo('system.log_viewer');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Intentionally non-destructive, same rationale as
        // 2026_07_21_000001_add_workflow_stage_permissions — roll back
        // manually if genuinely needed.
    }
};