<?php

    namespace Modules\Usermanagement\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class ChangePassword extends FormRequest
    {

        /**
         * Returns an array of validation rules for the password and current password fields.
         *
         * @return array The validation rules for the password and current password fields.
         */
        public function rules()
        : array
        {
            return [
                'password'         => 'required|string|min:8|confirmed',
                'current_password' => 'required|string|min:8'
            ];
        }
    }
