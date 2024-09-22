<?php

namespace App\Http\Requests\Admin;

use App\Exceptions\RequestException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class SettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'key' => 'required',
            'key_type' => 'required|min:1',
        ];
    }

    public function messages()
    {
        return [
            'message.min' => 'At least one type must be selected!'
        ];
    }
}
