<?php

    namespace Modules\Usermanagement\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Support\Facades\Hash;

    class User extends FormRequest
    {
        public function authorize()
        {
            return true;
        }

        /**
         * Returns an array of validation rules for the registration form.
         *
         * @return array The validation rules.
         */
        public function rules()
        : array
        {

            $rules = [
                'name' => 'required|string|max:255',
            ];

            if ($this->password || $this->method() === 'POST') {
                $rules['email'] = 'required|email|unique:users,email';
                $rules['password'] = 'required|string|min:8|confirmed';
            }

            if ($this->method() === 'PUT') {
                $rules['email'] = 'required|email|unique:users,email,' . $this->id;
            }

            return $rules;
        }

        public function passedValidation()
        {
            $this->merge([
                'password' => Hash::make($this->password)
            ]);
        }
    }



