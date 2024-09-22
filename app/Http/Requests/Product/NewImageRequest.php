<?php

namespace App\Http\Requests\Product;

use App\Image;
use App\Product;
use App\Traits\Uuids;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Webpatser\Uuid\Uuid;
use Illuminate\Support\Facades\File;
use Request;

class NewImageRequest extends FormRequest
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
            'picture' => 'required|mimes:jpeg,jpg,png,gif',
            'first' => 'boolean|nullable',
        ];
    }


    public function persist(Product $product = null)
    {        
        $extension ='png';
        $img = \Intervention\Image\Facades\Image::make($this->picture)->resize(256, 256)->encode($extension);
        $uploadedImage = 'products/'.str_random(32).'.'.$extension;
        \Storage::disk('public')->put( $uploadedImage, $img);

        $images = session('product_images') ?? collect(); // return collection of images or empty collection

        $newimage = new Image;
        $newimage->id = Uuid::generate()->string;
        $newimage->image = $uploadedImage;
        $newimage->first = $this->first ?? false;

        // adding images to old product
        if ($product && $product->exists) {
            // all existring images = not default
            if ($this->first)
                $product->images()->update(['first' => false]);
            $newimage->setProduct($product);
            $newimage->save();

        } else {
            // change all others to not be first
            if ($this->first) {
                $images->transform(function ($img) {
                    $img->first = 0;
                    return $img;
                });
            }

            $images->push($newimage); // put new offer
            session(['product_images' => $images]); // save to session
        }
    }
}
