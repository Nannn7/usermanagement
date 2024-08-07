<?php

    namespace Modules\Usermanagement\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class Login extends FormRequest
    {
        /**
         * Returns an array of validation rules for the login form.
         *
         * @return array The validation rules.
         */
        public function rules()
        : array
        {
            return [
                'email'    => 'required|email',
                'password' => 'required'
            ];
        }
    }
