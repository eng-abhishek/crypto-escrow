<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVendorProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->isVendor();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'description' => 'max:15000',
        ];
    }

    public function messages(){
        return [
            'description.max' => 'Description cannot be longer than 15000 characters'
        ];
    }
    public function persist(){

        $pofile_bgs = config('vendor.profile_bgs');
        $bg =$this->profilebg;
        if ($bg == null){
            $bg = array_random($pofile_bgs);
        } else {
            $bg = $pofile_bgs[$bg];
        }
        $vendor =  $this->user()->vendor;
        $vendor->about = $this->description;
        $vendor->profilebg = $bg;
        if ($this->vendor_vacation_check == 'enabled'){
            $vendor->vacation = true;
        } else {
            $vendor->vacation = false;
        }
        $vendor->save();

        session()->flash('success','Vendor profile updated successfully');

    }
}
