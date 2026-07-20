<?php

    namespace Modules\Usermanagement\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Support\Str;

    class PermissionRequest extends FormRequest
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
                'slug'        => 'required|string|max:255',
            ];

            if ($this->method() === 'PUT') {
                $rules['name'] = 'required|string|max:255|unique:permission_groups,name,' . $this->id;
            } else {
                $rules['name'] = 'required|string|max:255|unique:permission_groups';
            }

            return $rules;
        }

        public function prepareForValidation()
        {
            $this->merge([
                'slug' => Str::slug($this->input('name')),
            ]);
        }
    }



