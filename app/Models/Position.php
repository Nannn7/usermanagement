<?php

namespace Modules\Usermanagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Position extends Model
{
    use SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name',
        'level',
    ];

    /**
     * Retrieve the activity log options for this position.
     *
     * @return LogOptions The activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->useLogName('User Management|Positions : ');
    }

    /**
     * Get the roles associated with this position.
     */
    public function roles()
    {
        return $this->hasMany(Role::class);
    }
}
