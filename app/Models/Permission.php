<?php

    namespace Modules\Usermanagement\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
    use Spatie\Activitylog\Traits\LogsActivity;
    use Spatie\Permission\Models\Permission as SpatiePermission;

    class Permission extends SpatiePermission
    {
        use LogsActivity, SoftDeletes;

        /**
         * Retrieve the activity log options for this permission.
         *
         * @return LogOptions The activity log options.
         */
        public function getActivitylogOptions()
        : LogOptions
        {
            return LogOptions::defaults()->logAll()->useLogName('User Management|Permissions : ');
        }

        /**
         * Retrieve the permission group associated with this permission.
         *
         * @return \Illuminate\Database\Eloquent\Relations\BelongsTo The permission group relationship.
         */
        public function group()
        {
            return $this->belongsTo(PermissionGroup::class, 'permission_group_id');
        }
    }
