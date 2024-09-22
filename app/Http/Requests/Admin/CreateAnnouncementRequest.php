<?php

namespace App\Http\Requests\Admin;

use App\Announcement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\In;

class CreateAnnouncementRequest extends FormRequest
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
    public function rules()
    {
        return [
            'text' => 'required',
            'type' => ['required',new In(Announcement::$types)]
        ];
    }

    public function persist()
    {
        $announcement = new Announcement;
        $announcement->user_id = auth()->user()->id;
        $announcement->type = $this->type;
        $announcement->text = $this->text;
        $announcement->save();
        Announcement::putAllInCache();
    }
}
