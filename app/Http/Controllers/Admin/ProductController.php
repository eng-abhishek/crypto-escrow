<?php

namespace App\Http\Controllers\Admin;

use App\Category;
use App\Exceptions\RequestException;
use App\Http\Requests\Admin\DeleteProductRequest;
use App\Http\Requests\Admin\DisplayProductsRequest;
use App\Http\Requests\Admin\RemoveProductFromFeaturedReuqest;
use App\Http\Requests\Admin\MakeFeaturedProductRequest;
use App\Marketplace\Utility\CurrencyConverter;
use App\Product;
use App\Purchase;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Check if the this admin/moderator has access to edit/remove products
     */
    private function checkProducts(){
      if(Gate::denies('has-access', 'products'))
        abort(403);
}


public function __construct() {
  $this->middleware('admin_panel_access');
}

    /**
     * Displaying list of all products in Admin Panel
     *
     * @param DisplayProductsRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function products(DisplayProductsRequest $request,$status=''){
      
      $this -> checkProducts();
      
        // $products = Product::with(['user','category','featured_product_purchase'])->where('id','95352a40-2603-11ee-818e-6d3d66429919')->get();
        // dd($products);
      
      $request->persist();

      if(!empty($status)){
      $products = $request->getProducts($status);
      }else{
      $products = $request->getProducts();
      }
     // dd($products);
      // dd($products[32]->featured_product_purchase->pay_amount);
      return view('admin.products')->with([
       'products' => $products,
       'uri_segment' => request()->segment(3),

   ]);
  }
  
  public function productsPost(Request $request){
      $this -> checkProducts();

      return redirect()->route('admin.products',[
        'order_by' => $request->order_by,
        'user' => $request->user,
        'product' => $request -> product
    ]);
  }

    /**
     * Deleteing product from Admin panel
     *
     * @param DeleteProductRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteProduct(DeleteProductRequest $request){
     
      $this -> checkProducts();

      try{
        $request->persist();
    } catch (RequestException $e){
        Log::warning($e);
        $e->flashError();
    }
    return redirect()->back();
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

      $myProduct = Product::findOrFail($id);
      $this -> authorize('update', $myProduct);


        // if product is not vendor's
      if($myProduct == null)
        return redirect() -> route('admin.products');

        // digital product cant have delivery section
    if($myProduct -> isDigital() && $section == 'delivery')
        return redirect() -> route('admin.index');

        // physical product cant have digtial section
    if($myProduct -> isPhysical() && $section == 'digital')
        return redirect() -> route('admin.index');

        // set product type section
    session() -> put('product_type', $myProduct -> type);

        // string to view map to retrive which view
    $sectionMap = [
        'basic' =>
        view('admin.product.basic',
          [
            'type' => $myProduct -> type,
            'allCategories' => Category::nameOrdered(),
            'basicProduct' => $myProduct,]),
        'offers' =>
        view('admin.product.offers',
          [
            'basicProduct' => $myProduct,
            'productsOffers' => $myProduct -> offers() -> get()
        ]),
        'images' =>
        view('admin.product.images',
          [
            'basicProduct' => $myProduct,
            'productsImages' => $myProduct -> images() -> get(),
        ]),
        'delivery' =>
        view('admin.product.delivery', [
          'productsShipping' => $myProduct -> isPhysical() ? $myProduct -> specificProduct() -> shippings() -> get() : null,
          'physicalProduct' => $myProduct -> specificProduct(),
          'basicProduct' => $myProduct,
      ]),
        'digital' =>
        view('admin.product.digital', [
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
     * List of all purchases
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function purchases()
    {
      return view('admin.purchases', [
        'purchases' => Purchase::orderByDesc('created_at')->paginate(config('marketplace.products_per_page')),
    ]);
  }
  
  public function featuredProductsShow(){

      $products = Product::where('featured',1)->paginate(25);

      return view('admin.featuredproducts')->with([
        'products' => $products
    ]);
  }
    /**
     * Deleteing product from Admin panel
     *
     * @param DeleteProductRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeFromFeatured(RemoveProductFromFeaturedReuqest $request){
      $this -> checkProducts();

      try{
        $request->persist();
    } catch (RequestException $e){
        Log::warning($e);
        $e->flashError();
    }
    return redirect()->back();
}

/*------ changeProductStatus ------*/

public function changeProductStatus($status,$product){

    try{

        if($status == 'under-review'){
           
           $active = 0;

       }elseif($status == 'reject'){

           $active = 2;

       }elseif($status == 'approve') {

           $active = 1;

       }
       Product::where('id',$product)->update(['active'=>$active]);
       session() -> flash('success', 'Product status change successfully!');

   }catch(\Exceptions $e){

    session() -> flash('success', 'Opp`s something wents wrong.');
}
return redirect()->route('admin.products');
}


/*------ make as Featured Product ------*/
public function activeAsFeatured($product){
 
  Product::where('id',$product)->update(['featured'=>1]);
  session() -> flash('success', 'Product status change successfully!');
  return redirect()->route('admin.products');
}

public function inactiveAsFeatured($product){
      //dd($product);
  Product::where('id',$product)->update(['featured'=>0]);
  session() -> flash('success', 'Product status change successfully!');
  return redirect()->route('admin.products');
}

public function markAsFeatured(Product $product){

  $chkfeaturedPrice = Product::where('id',$product->id)->first();
  
  if($chkfeaturedPrice->featured_fee > 0){
    $featured_fee = $chkfeaturedPrice->featured_fee;
    
}else{
    $featured_fee = '';
}

return view('admin.makeFeaturedProduct',['productId'=>$product->id,'featured_fee'=>$featured_fee]);
}

public function postMarkAsFeatured(MakeFeaturedProductRequest $request,Product $product){

  $fee = CurrencyConverter::convertToUsd($request->post('featured_fee'));
  Product::where('id',$product->id)->update(['featured_fee'=>$fee,'make_featured_by'=>auth()->user()->id]);
  session() -> flash('success', 'Product listing successfully in featured product!');
  return redirect()->route('admin.products');
}

}
