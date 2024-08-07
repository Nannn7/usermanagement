<?php

    namespace Modules\Usermanagement\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Support\Facades\Hash;

    class RoleRequest extends FormRequest
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
                'guard_names' => 'required|string|in:web,api',
            ];

            if ($this->method() === 'PUT') {
                $rules['name'] = 'required|string|max:255|unique:roles,name,' . $this->id;
            } else {
                $rules['name'] = 'required|string|max:255';
            }

            return $rules;
        }

        public function prepareForValidation()
        {
            $this->merge([
                'guard_names' => 'web',
            ]);
        }
    }



