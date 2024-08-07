<?php

    namespace Modules\Usermanagement\Models;

    use Illuminate\Database\Eloquent\SoftDeletes;
    use Spatie\Activitylog\LogOptions;
    use Spatie\Activitylog\Traits\LogsActivity;
    use Spatie\Permission\Models\Role as SpatieRole;

    class Role extends SpatieRole
    {
        use softDeletes, LogsActivity;

        /**
         * Retrieve the activity log options for this role.
         *
         * @return LogOptions The activity log options.
         */
        public function getActivitylogOptions()
        : LogOptions
        {
            return LogOptions::defaults()->logAll()->useLogName('User Management|Roles : ');
        }

    }
