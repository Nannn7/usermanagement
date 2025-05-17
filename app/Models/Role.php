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
         * The attributes that are mass assignable.
         *
         * @var array
         */
        protected $fillable = [
            'name',
            'guard_name',
            'position_id',
        ];

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

        /**
         * Get the position that owns the role.
         */
        public function position()
        {
            return $this->belongsTo(Position::class);
        }
    }
