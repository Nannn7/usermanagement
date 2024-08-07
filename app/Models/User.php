<?php

    namespace Modules\Usermanagement\Models;

    use Illuminate\Database\Eloquent\SoftDeletes;
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Notifications\Notifiable;
    use Spatie\Permission\Traits\HasRoles;
    use Wildside\Userstamps\Userstamps;

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
        use Notifiable, Userstamps, HasRoles, softDeletes;

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
        protected function casts()
        : array
        {
            return [
                'email_verified_at' => 'datetime',
                'password'          => 'hashed',
                'id'                => 'string',
            ];
        }
    }

