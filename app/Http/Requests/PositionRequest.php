<?php

namespace Modules\Usermanagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PositionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $positionId = $this->route('position') ?? $this->route('id') ?? $this->input('id');

        $rules = [
            'name' => 'required|string',
            'level' => 'required|integer',
        ];

        if ($this->method() === 'PUT') {
            $rules['code'] = 'required|string|unique:positions,code,' . $positionId;
        } else {
            $rules['code'] = 'required|string|unique:positions,code';
        }

        return $rules;
    }
}
