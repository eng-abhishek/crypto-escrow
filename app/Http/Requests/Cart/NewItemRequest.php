<?php

namespace App\Http\Requests\Cart;

use App\Exceptions\RequestException;
use App\Marketplace\Cart;
use App\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NewItemRequest extends FormRequest
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
            'delivery' => 'nullable|exists:shippings,id',
            'amount' => 'numeric|required',
            'message' => 'nullable|string',
            'coin' => ['required' , Rule::in(array_keys(config('coins.coin_list')))],
            'type' => ['required', Rule::in(array_keys(\App\Purchase::$types))],
        ];
    }

    public function persist(Product $product)
    {
      
        $shipping = null;
        $user = auth()->user();
    
        throw_if($product->user->vendor->vacation == true, new RequestException('Vendor is currently on vacation'));
        throw_if($product->user->id == $user->id, new RequestException('You can\'t put your products in cart!'));
        throw_if($user->pgp_key == null, new RequestException('You need to add PGP key in your profile before you can make any purchases'));
        $usersAddress = $user->addresses()->where('coin', $this->coin)->orderByDesc('created_at')->first();
        throw_if($usersAddress == null, new RequestException('You need to add a refund address for coin '.strtoupper($this->coin). ' before you can make any purchases'));

        throw_if($product->active == 0, new RequestException('Oop`s currently this product is under review'));
        throw_if($product->active == 2, new RequestException('Oop`s currently this product has rejected by administrator'));

        // select shipping
        if($product -> isPhysical())
            $shipping = $product -> specificProduct() -> shippings()
                -> where('id', $this -> delivery)
                -> where('deleted', '=', 0) // is not deleted
                -> first();

               // dd($this -> message);
        Cart::getCart() -> addToCart($product, $this -> amount, $this -> coin, $shipping, $this -> message, $this -> type);
    }
}
