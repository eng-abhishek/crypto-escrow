<?php

namespace App;

use App\Exceptions\RequestException;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class DigitalProduct extends User
{
    use Uuids;

    /**
     * Return instance of the product
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function product()
    {
        return $this -> hasOne(\App\Product::class, 'id', 'id');
    }

    public function shippings()
    {
        return null;
    }

    /**
     * Set the new content for the product
     *
     * @param $newContent
     */
    public function setContent(?string $newContent)
    {
        $newContent = empty($newContent) ? '' : $newContent;
        // remove consecutive new lines, and trim balnk chars
        $formatedContent = trim(preg_replace("/[\r\n]{2,}/", "\n", $newContent));
        $this -> content = $formatedContent;
    }

    /**
     * Return new quantity by counting number of lines in product's content
     *
     * @return int
     */
    public function newQuantity()
    {
        return !empty($this -> content) ? substr_count($this -> content, "\n") + 1 : 0;
    }


    /**
     * Get autodelivered products
     *
     * @param int $quantity number of autodelivered products
     * @return array
     * @throws RequestException
     */
    public function getProducts(int $quantity) : array
    {
        if($quantity > $this -> newQuantity())
            throw new RequestException('There is not enough products in the stock!');


        $allProducts = explode("\n",$this->content);

        $productsToDelivery = array_slice($allProducts,0,$quantity);

        $allProducts = array_slice($allProducts,$quantity);
        $this->content = implode("\n",$allProducts);

        $this -> save();
        // update \App\Product quantity
        $this -> product -> updateQuantity();
        $this -> product -> save();


        return $productsToDelivery;
    }
}
