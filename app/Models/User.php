<?php

namespace Modules\Usermanagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Basicdata\Models\Branch;
use Modules\Corsec\Models\Directorate;
use Modules\Corsec\Models\MeetingDecision;
use Modules\Usermanagement\Models\Position;
use Spatie\Permission\Traits\HasRoles;
use Mattiverse\Userstamps\Traits\Userstamps;

/**
 * Class User
 *
 * This class extends the Laravel's Authenticatable class and represents a User in the application.
 * It includes traits for using factories, notifications, API tokens, and UUIDs.
 *
 * @property string $name           The name of the user.
 * @property string $email          The email of the user.
 * @property string $password       The hashed password of the user.
 * @property string $remember_token The token used for "remember me" functionality.
 *
 * @package App\Models
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, Userstamps, HasRoles, softDeletes;

    protected $guard_name = ['web'];

    /**
     * The attributes that are mass assignable.
     *
     * These are the attributes that can be set in bulk during a create or update operation.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nik',
        'branch_id',
        'directorate_id',
        'position_id',
        'profile_photo_path',
        'last_login_at',
        'last_login_ip',
        'sign',
        'must_change_password',
        'password_changed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * These are the attributes that will be hidden when the model is converted to an array or JSON.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * This method defines how the attributes should be cast when accessed.
     * In this case, 'email_verified_at' is cast to 'datetime', 'password' is cast to 'hashed', and 'id' is cast to 'string'.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'id'                => 'string',
            'must_change_password' => 'boolean',
            'password_changed_at'  => 'datetime',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'user_branches', 'user_id', 'branch_id');
    }

    public function directorate()
    {
        return $this->belongsTo(Directorate::class, 'directorate_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function supportedMeetingDecisions()
    {
        return $this->belongsToMany(
            MeetingDecision::class,
            'corsec_meeting_decision_support_users',
            'user_id',
            'meeting_decision_id'
        );
    }


    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return \Modules\Usermanagement\Database\Factories\UserFactory::new();
    }
}
