<?php

namespace App\Http\Controllers;

use App\Category;
use App\DigitalProduct;
use App\Exceptions\RedirectException;
use App\Exceptions\RequestException;
use App\Http\Requests\Product\NewBasicRequest;
use App\Http\Requests\Product\NewDigitalRequest;
use App\Http\Requests\Product\NewImageRequest;
use App\Http\Requests\Product\NewOfferRequest;
use App\Http\Requests\Product\NewProductRequest;
use App\Http\Requests\Product\NewShippingOptionsRequest;
use App\Http\Requests\Product\NewShippingRequest;
use App\Http\Requests\Profile\ChangeAddressRequest;
use App\Http\Requests\Profile\UpdateVendorProfileRequest;
use App\Marketplace\Utility\MoneroConvert;
use App\Marketplace\Utility\BitcoinConverter;
use App\Marketplace\Utility\CurrencyConverter;
use App\Image;
use App\VendorPurchase;
use App\PhysicalProduct;
use App\Product;
use App\Purchase;
use App\FeaturedProuctPurchase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Marketplace\Payment\BitcoinPayment;
use App\Marketplace\Payment\MoneroPayment;
use Carbon\Carbon;
use Config;
use App\Events\Admin\ProductDeleted;

class VendorController extends Controller
{
    /**
     * VendorController constructor.
     * Logged in user must be vendor to use this controller
     */
    public function __construct()
    {
        $this -> middleware('auth');
        $this -> middleware('can_edit_products');
    }

    /**
     * Return vendor profile page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function vendor()
    {
        //dd(auth() -> user() -> all_products() -> paginate(config('marketplace.products_per_page')));
        return view('frontend.profile.vendor',
            [
                'myProducts' => auth() -> user() -> all_products() -> paginate(config('marketplace.products_per_page')),
                'vendor' => auth()->user()->vendor
            ]
        );
    }


    public function makeFeaturedProduct($product_id){

      $product = Product::where('id',$product_id)->first();
      
      $userData = FeaturedProuctPurchase::where(['product_id'=>$product_id,'user_id'=>auth()->user()->id])->where('coin','!=','stb')->first();

      $obj_bitcoin_pay = new BitcoinPayment();
      $obj_monerocoin_pay = new MoneroPayment();

      $obj_btc_convert = new BitcoinConverter();
      $obj_xmr_convert = new MoneroConvert();
      
      if(!is_null($product->featured_fee) OR ($product->featured_fee)>0){

      if(is_null($userData)){

        $coinsClasses = config('coins.coin_list');

        // vendor fee in usd
        $marketVendorFee =  config('marketplace.vendor_fee');

        // for each supported coin generate instance of the coin
        foreach ($coinsClasses as $short => $coinClass){
            $coinsService = new $coinClass();
            try {
                // Add new deposit address
                $newDepositAddress = new FeaturedProuctPurchase;
                $newDepositAddress->user_id = auth()->user()->id;
                
                $newDepositAddress->product_id = $product_id;

                $newDepositAddress->address = $coinsService->generateAddress(['user' => auth()->user()->id]);
                $newDepositAddress->coin = $coinsService->coinLabel();
                if($coinsService->coinLabel() == 'btc'){

                    $needtopayAmt = round($obj_btc_convert->usdToBtc((float)$product->featured_fee),8);

                }elseif($coinsService->coinLabel() == 'xmr'){

                    $needtopayAmt = round($obj_xmr_convert->usdToXmr((float)$product->featured_fee),8);
                }
                
                //dd($product->featured_fee);
                $newDepositAddress->required_btc_amount = $needtopayAmt;
                
                $newDepositAddress->save();

            }catch(\Exception $e){
                \Illuminate\Support\Facades\Log::error($e);
            }

            $user_data = FeaturedProuctPurchase::where(['product_id'=>$product_id,'user_id'=>auth()->user()->id])->where('coin','!=','stb')->first();

            return view('frontend.featured_product',['vendorPurchases'=>$user_data,'featured_fee'=>$product->featured_fee]);
        }
    }else{

    $addressActualAmount = $obj_bitcoin_pay->getBalance(['address'=>$userData->address]);
    //dd($userData->address);

        if($userData->coin == 'btc'){

            $addressActualAmount = $obj_bitcoin_pay->getBalance(['address'=>$userData->address]);

        }elseif($userData->coin == 'xmr') {

            $addressActualAmount = $obj_monerocoin_pay->getBalance(['address'=>$userData->address]);   

        }else{

           $addressActualAmount = '';        
       }

     //dd($addressActualAmount);
       if(!is_null($addressActualAmount)){
        if($addressActualAmount == $userData->required_btc_amount){

          Product::where('id',$product_id)->update(['featured'=>1,'make_featured_by'=>auth()->user()->id]);
          
          $update = array(
           'status' =>'1',
           'pay_amount' =>$product->featured_fee,
       );

          FeaturedProuctPurchase::where(['product_id'=>$product_id,'user_id'=>auth()->user()->id])->where('coin','!=','stb')->update($update);


          session() -> flash('success', 'Your product is successfully listing in featured');
          
      }
  }else{

    session() -> flash('errormessage', 'Without payment you can`t make it as featured product.');

}       
return view('frontend.featured_product',['vendorPurchases'=>$userData,'featured_fee'=>$product->featured_fee]);
}
}else{

    session() -> flash('errormessage', 'Oop`s you can not make it as featured product contact to admin.');
    return redirect()->route('profile.vendor');

}
}


public function removeFromFeaturedProduct($product){

    Product::where('id',$product)->update(['featured'=>0,'make_featured_by'=>auth()->user()->id]);
    session() -> flash('success', 'Your product is successfully remove from featured listing');
    return redirect()->route('profile.vendor');
}

    /**
     * Show form for basic adding
     *
     * @param $type
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addBasicShow($type = 'physical')
    {
        if($type == null) $type = 'physical';
        // save type
        session()->put('product_type', $type);

        // return view('profile.vendor.addbasic', [
        //     'type' => $type,
        //     'allCategories' => Category::nameOrdered(),
        //     'basicProduct' => session('product_adding'),
        // ]);
        return view('frontend.profile.vendor.addbasic', [
            'type' => $type,
            'allCategories' => Category::nameOrdered(),
            'basicProduct' => session('product_adding'),
        ]);
    }

    /**
     * Authorizing editign or creating product
     *
     * @param Product|null $product
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    private function authorizeEditOrCreate(?Product $product)
    {
        // authorization for editing or creating
        if(!is_null($product)) $this -> authorize('update', $product);
        else auth() -> user() -> can('create', Product::class);
    }

    /**
     * Accepts POST request for adding new product or editing old one
     *
     * @param NewBasicRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addShow(NewBasicRequest $request, Product $product = null)
    {
        $this -> authorizeEditOrCreate($product);

        try{
            return $request -> persist($product);
        }
        catch (RequestException $e){
            $e -> flashError();
        }
        catch (\Exception $e){
            Log::error($e);
            session() -> flash('errormessage', 'Something went wrong try again!');
        }


        return redirect() -> back();
    }

    /**
     * Form for adding offers
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addOffersShow()
    {

        // return view('profile.vendor.addoffers',
        //     [
        //         'productsOffers' => session('product_offers'),
        //         'basicProduct' => null,
        //     ]);

        return view('frontend.profile.vendor.addoffers',
            [
                'productsOffers' => session('product_offers'),
                'basicProduct' => null,
            ]);
    }

    /**
     * Generates offer and saves them to session
     *
     * @param NewOfferRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addOffer(NewOfferRequest $request, Product $product = null)
    {
        $this -> authorizeEditOrCreate($product);

        try{
            $request -> persist($product);
            session() -> flash('success', 'You have added new offer!');
        }
        catch (RequestException $e){
            session() -> flash('errormessage', $e -> getMessage());
        }
        catch (\Exception $e){
            session() -> flash('errormessage', 'Something went wrong, try again!');
        }


        return redirect() -> back();
    }

    /**
     * Remove offer from product or from db
     *
     * @param $quantity
     * @param Product|null $editingProduct
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeOffer($quantity, Product $product = null)
    {
        $this -> authorizeEditOrCreate($product);

        try{
            // old offers on products
            if($product && $product -> exists()){
                if($product -> offers() -> count() == 1)
                    throw new RequestException('You must have at least one offer!');

                $product -> offers() -> where('min_quantity', $quantity) -> update(['deleted' => 1]);
                session() -> flash('success', 'You have deleted offer!');
            }
            // new offers
            else{

                $offers = session('product_offers') ?? collect();

                $offers = $offers -> reject(function($offer, $keys) use($quantity){
                    return $offer -> quantity != $quantity;
                });

                session() -> put('product_offers', $offers);
            }
        }
        catch (RequestException $e){
            session() -> flash('errormessage', $e -> getMessage());
        }

        return redirect() -> back();
    }

    /**
     * Showing form for adding shipping and delivery options
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addDeliveryShow()
    {
        // get physical product from session
        $physicalProduct = session('product_details');
        if(!($physicalProduct instanceof  PhysicalProduct))
            $physicalProduct = new PhysicalProduct();
         //dd(session()->get('product_type'));
        // return view('profile.vendor.adddelivery',
        //     [
        //         'physicalProduct' => $physicalProduct ?? new PhysicalProduct,
        //         'productsShipping' => session('product_shippings'),
        //     ]
        // );

        return view('frontend.profile.vendor.adddelivery',
            [   
                'type' => session()->get('product_type'),
                'physicalProduct' => $physicalProduct ?? new PhysicalProduct,
                'productsShipping' => session('product_shippings'),
            ]
        );

    }

    /**
     * New Delivery option request added
     *
     * @param NewShippingRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function newShipping(NewShippingRequest $request, Product $product = null)
    {
        $this -> authorizeEditOrCreate($product);

        try{
            $request -> persist($product);
            session() -> flash('success', 'You have added shipping options!');
        }
        catch (RequestException $e){
            session() -> flash('errormessage', $e -> getMessage());
        }

        return redirect() -> back();
    }

    /**
     * Remove shipping request, removes from session with index
     *
     * @param $index
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeShipping($index, Product $product = null)
    {
        $this -> authorizeEditOrCreate($product);

        try{
            // changeing product
            if($product && $product -> exists()){
                if($product -> specificProduct() -> shippings() -> count() <= 1)
                    throw new RequestException('You must have at least one delivery option!');

                $product -> specificProduct() -> shippings() -> where('id', $index) -> update(['deleted' => 1]);
                session() -> flash('success', 'You have removed selected shipping!');
            }
            // for new product
            else{
                $shippingsArray =  session('product_shippings') ?? [];
                $shippingsCollection = collect();
                foreach ($shippingsArray as $ship){
                    $shippingsCollection -> push($ship);
                }
                $shippingsCollection = $shippingsCollection -> reject(function($shipping, $key) use($index){
                    return $index == $key;
                });

                session() -> put('product_shippings', $shippingsCollection);
            }
        }
        catch (RequestException $e){

        }

        return redirect() -> back();
    }

    /**
     * Shipping settings added
     *
     * @param NewShippingOptionsRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function newShippingOption(NewShippingOptionsRequest $request, PhysicalProduct $product)
    {
        $this -> authorizeEditOrCreate(optional($product) -> product);

        try{
            return $request -> persist($product);
        }
        catch (RequestException $e){
            session() -> flash('errormessage', $e -> getMessage());
        }
        return redirect() -> back();
    }

    /**
     * Page that displays digital form
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addDigitalShow()
    {

        // return view('profile.vendor.adddigital',
        //     [
        //         'digitalProduct' => session('product_details') ?? new DigitalProduct()
        //     ]);

        return view('frontend.profile.vendor.adddigital',
            [
                'type' => session()->get('product_type'),
                'digitalProduct' => session('product_details') ?? new DigitalProduct()
            ]);
    }

    /**
     * Add digital parameters
     *
     * @param NewDigitalRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addDigital(NewDigitalRequest $request, DigitalProduct $product = null)
    {
        $this -> authorizeEditOrCreate(optional($product) -> product);

        try{
            return $request -> persist($product);
        }
        catch (RequestException $e){
            session() -> flash('errormessage', $e -> getMessage());
        }

        return redirect() -> back();
    }

    /**
     * Returns form for adding the images
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addImagesShow()
    {

        // return view('profile.vendor.addimages',
        //     [
        //         'basicProduct' => null,
        //         'productsImages' => session('product_images'),
        //     ]);

        return view('frontend.profile.vendor.addimages',
            [
                'type' => session()->get('product_type'),
                'basicProduct' => null,
                'productsImages' => session('product_images'),
            ]);

    }

    /**
     * Add image request
     *
     * @param NewImageRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addImage(NewImageRequest $request, Product $product = null)
    {

        $this -> authorizeEditOrCreate($product);

        try{
            $request -> persist($product);
            session() -> flash('success', 'You have added new image!');
        }
        catch (RequestException $e){
            session() -> flash('errormessage', $e -> getMessage());
        }

        return redirect() -> back();
    }

    /**
     * Remove image form database or session and delete file from server
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeImage( $id )
    {
        $removingImage = Image::find($id);
        $this -> authorizeEditOrCreate(optional($removingImage) -> product);

        try {
            if ($removingImage && $removingImage->exists()) {
                if ($removingImage->product->images()->count() <= 1)
                    throw new RequestException('You must have at least one image!');
                $imagesProduct = $removingImage->product;
                $removingImage->delete();

                // new default
                if (!$imagesProduct->images()->where('first', 1)->exists()) {
                    $newDefaultImage = $imagesProduct->images()->first();
                    $newDefaultImage->first = 1;
                    $newDefaultImage->save();
                }
            } else {
                $sessionImagesArray = session('product_images') ?? [];
                $sessionImages = collect();
                // fill the collection
                foreach ($sessionImagesArray as $image) {
                    // delete image from storage
                    if ($image->id == $id) {
                        //asset('storage/' . $image -> image)
                        // delete file from sever
                        File::delete('storage/' . $image->image);
                    }
                    $sessionImages->push($image);

                }

                $sessionImages = $sessionImages->reject(function ($image) use ($id) {
                    return $image->id == $id;
                });

                // save updated collection
                session(['product_images' => $sessionImages]);
            }

            session() -> flash('success', 'You have removed the image');

        }
        catch (RequestException $e){
            session() -> flash('errormessage', $e -> getMessage());
        }

        return redirect() -> back();
    }

    /**
     * Make image default for the product
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function defaultImage( $id )
    {
        $newDefaultImage = Image::find($id);

        if($newDefaultImage && $newDefaultImage -> exists()){

            $newDefaultImage -> product -> images() -> update(['first' => 0]);
            $newDefaultImage -> first = 1;
            $newDefaultImage -> save();

        }else{
            $images = collect();
            $imagesArray = session('product_images') ?? [];
            // fill the collection with images
            foreach ($imagesArray as $image){
                $images -> push($image);
            }

            // change collection to have only one front image
            $images -> transform(function($image) use($id){
                if($image -> id == $id)
                    $image -> first = 1;
                else
                    $image -> first = 0;

                return $image;
            });

            // save session
            session() -> put('product_images', $images);
        }

        session() -> flash('success', 'You have set new default image!');

        return redirect() -> back();
    }

    /**
     * New Product request
     *
     * @param NewProductRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function newProduct(NewProductRequest $request)
    {
        auth() -> user() -> can('create', Product::class);

        try{
            $request -> persist();
            session() -> flash('success', 'You have added new product!');
        }
        catch (RequestException $e){
            session() -> flash('errormessage', $e -> getMessage());
        }
        catch (RedirectException $e){
            session() -> flash('errormessage', $e -> getMessage());
            return redirect() -> to($e -> getRoute());
        }

        // Return to Vendor page
        return redirect() -> route('profile.vendor');
    }


    public function updateProduct($productId){
      Product::where('id',$productId)->searchable();
      session() -> flash('success', 'Your product updated successfully.');
      return redirect() -> route('profile.vendor');
    }

    /**
     * Modal to confirm to delete the product
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function confirmProductRemove($id)
    {
        $product = Product::findOrFail($id);

        return view('frontend.profile.product.confirmdelete', [
            'product' => $product
        ]);
    }

    /**
     * Remove product
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function removeProduct($id)
    {
        $product = Product::where('id',$id)->with('user')->first();
        event(new ProductDeleted($product,$product->user,auth()->user()));

        $product = Product::where('id',$id)->delete();
        session() -> flash('success', 'You have successfully deleted product!');
        return redirect() -> route('profile.vendor');
    }


    /**
     * Method for showing all editing forms for the product
     *
     * @param $id of the product
     * @param string $section
     * @return \Illuminate\Http\RedirectResponse|mixed
     *
     */
    public function editProduct($id, $section = 'basic')
    {

        $myProduct = auth() -> user() -> products() -> where('id', $id) -> first();
        $this -> authorize('update', $myProduct);


        // if product is not vendor's
        if($myProduct == null)
            return redirect() -> route('profile.vendor');

        // digital product cant have delivery section
        if($myProduct -> isDigital() && $section == 'delivery')
            return redirect() -> route('profile.vendor');

        // physical product cant have digtial section
        if($myProduct -> isPhysical() && $section == 'digital')
            return redirect() -> route('profile.vendor');

        // set product type section
        session() -> put('product_type', $myProduct -> type);

        // string to view map to retrive which view

   // $sectionMap = [
   //          'basic' =>
   //              view('profile.vendor.addbasic',
   //                  [
   //                      'type' => $myProduct -> type,
   //                      'allCategories' => Category::nameOrdered(),
   //                      'basicProduct' => $myProduct,]),
   //          'offers' =>
   //              view('profile.vendor.addoffers',
   //                  [
   //                      'basicProduct' => $myProduct,
   //                      'productsOffers' => $myProduct -> offers() -> get()
   //                  ]),
   //          'images' =>
   //              view('profile.vendor.addimages',
   //                  [
   //                      'basicProduct' => $myProduct,
   //                      'productsImages' => $myProduct -> images() -> get(),
   //                  ]),
   //          'delivery' =>
   //              view('profile.vendor.adddelivery', [
   //                  'productsShipping' => $myProduct -> isPhysical() ? $myProduct -> specificProduct() -> shippings() -> get() : null,
   //                  'physicalProduct' => $myProduct -> specificProduct(),
   //                  'basicProduct' => $myProduct,
   //              ]),
   //          'digital' =>
   //              view('profile.vendor.adddigital', [
   //                  'digitalProduct' => $myProduct -> specificProduct(),
   //                  'basicProduct' => $myProduct,
   //              ]),

   //      ];


        $sectionMap = [
            'basic' =>
            view('frontend.profile.vendor.addbasic',
                [
                    'type' => $myProduct -> type,
                    'allCategories' => Category::nameOrdered(),
                    'basicProduct' => $myProduct,]),
            'offers' =>
            view('frontend.profile.vendor.addoffers',
                [
                    'basicProduct' => $myProduct,
                    'productsOffers' => $myProduct -> offers() -> get()
                ]),
            'images' =>
            view('frontend.profile.vendor.addimages',
                [
                    'basicProduct' => $myProduct,
                    'productsImages' => $myProduct -> images() -> get(),
                ]),
            'delivery' =>
            view('frontend.profile.vendor.adddelivery', [
                'productsShipping' => $myProduct -> isPhysical() ? $myProduct -> specificProduct() -> shippings() -> get() : null,
                'physicalProduct' => $myProduct -> specificProduct(),
                'basicProduct' => $myProduct,
            ]),
            'digital' =>
            view('frontend.profile.vendor.adddigital', [
                'digitalProduct' => $myProduct -> specificProduct(),
                'basicProduct' => $myProduct,
            ]),

        ];

        // if the section is not allowed strings
        if(!in_array($section, array_keys($sectionMap)))
            $section = 'basic';

        return $sectionMap[$section];
    }

    /**
     * Table with the sales
     */
    public function sales($state = '')
    {
        $sales = auth() -> user() -> vendor -> sales() -> with('offer') -> paginate(20);
        if(array_key_exists($state, Purchase::$states))
            $sales = auth() -> user() -> vendor -> sales() -> where('state', $state) -> paginate(20);

        // update unvisited sales
        auth() -> user() -> vendor -> sales() -> where('read', false) -> update(['read' => true]);

        // return view('profile.vendor.sales', [
        //     'sales' => $sales,
        //     'state' => $state
        // ]);

        return view('frontend.profile.vendor.sales', [
            'sales' => $sales,
            'state' => $state
        ]);
    }

    /**
     * Return view for the sale
     *
     * @param Purchase $sale
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function sale(Purchase $sale)
    {
        if(!$sale -> isAllowed())
            return abort(404);

      $time = Config::get('constants.order_expiry_time');

      if($sale->coin_balance <= 0){

       $paymrntLastDay = Carbon::parse($sale->created_at)->addMinute($time);
       // echo $purchase->created_at;
       // echo"<br>";
       // echo $paymrntLastDay;
       // die;
       if(strtotime(Carbon::now()) >= strtotime($paymrntLastDay)){
        $sale -> cancel();
       }
      }

        return view('frontend.profile.vendor.sale', [
            'purchase' => $sale,
        ]);
    }

    /**
     * Returns view for confirming sent
     *
     * @param Purchase $sale
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View\
     */
    public function confirmSent(Purchase $sale)
    {
        return view('frontend.profile.purchases.confirmsent', [
            'backRoute' => redirect() -> back() -> getTargetUrl(),
            'sale' => $sale
        ]);
    }

    /**
     * Runs procedure for marking sale as sent
     *
     * @param Purchase $purchase
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAsSent(Purchase $sale)
    {
        try{
            $sale -> sent();
            session() -> flash('success', 'You have successfully marked sale as sent!');
        }
        catch (RequestException $e){
            $e -> flashError();
        }

        return redirect() -> route('profile.sales.single', $sale);
    }

    /**
     * Update profile description and background for vendor
     */
    public function updateVendorProfilePost(UpdateVendorProfileRequest $request){

        try{
            $request->persist();
        } catch (RequestException $e){
            $e -> flashError();
        }
        return redirect()->back();
    }


}
