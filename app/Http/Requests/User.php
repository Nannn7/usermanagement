<?php

namespace Modules\Usermanagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

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
    public function rules(): array
    {
        $userId = $this->route('user') ?? $this->route('id') ?? $this->input('id');

        $rules = [
            'name'               => 'required|string|max:255',
            'branch_id'          => 'nullable|exists:branches,id',
            'directorate_id'     => 'nullable|exists:corsec_directorates,id',
            'position_id'        => 'nullable|exists:positions,id',
            'profile_photo_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'sign'               => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];

        if ($this->password !== null) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }
        // if ($this->password !== null) {
        //     $rules['password'] = [
        //         'required',
        //         'confirmed',
        //         Password::min(8)->mixedCase()->numbers()->symbols(),
        //     ];
        // }

        if ($this->method() === 'PUT') {
            $rules['email'] = 'required|email|unique:users,email,' . $userId;
            $rules['nik']   = 'nullable|string|max:6|unique:users,nik,' . $userId;
        } else {
            $rules['email'] = 'required|email|unique:users,email';
            $rules['nik']   = 'nullable|string|max:6|unique:users,nik';
        }

        return $rules;
    }

    public function passedValidation()
    {
        if ($this->password !== null) {
            $this->merge([
                'password' => Hash::make($this->password),
            ]);
        }
    }
}
