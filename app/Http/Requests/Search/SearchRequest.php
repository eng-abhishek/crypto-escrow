<?php

namespace App\Http\Requests\Search;

use App\Exceptions\RequestException;
use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'search' => 'string',
            'user' => 'string',
            'minimum_price' => 'string',
            'maximum_price' => 'string',
        ];
    }

  
}
