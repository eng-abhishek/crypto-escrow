<?php

namespace App\Http\Controllers;

use App\Category;
use App\Exceptions\RequestException;
// use App\Http\Requests\CompleteCaptchaRequest;
// use App\Http\Requests\CompleteCaptchaRequest2;
use App\Marketplace\Cart;
use App\Marketplace\FeaturedProducts;
use App\Marketplace\ModuleManager;
use App\Marketplace\Payment\Escrow;
use App\Marketplace\Payment\VergeCoin;
//use App\Marketplace\Utility\Captcha;
use App\Announcement;
use App\Product;
use App\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Captcha;
use Cookie;
use App\User;
use App\Purchase;

/**
 * Controller for all always public routes
 *
 * Class IndexController
 * @package App\Http\Controllers
 */
class IndexController extends Controller
{
    /**
     * Handles the index page request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function home() {

        $searchMode = session()->get('searchMode');

        if ($searchMode == null || $searchMode !== 'simple' && $searchMode !== 'advanced'){
            $searchMode = 'simple';
        }
        if (!ModuleManager::isEnabled('FeaturedProducts'))
            $featuredProducts = null;
        else
            $featuredProducts = FeaturedProducts::get();

        $info = Announcement::with('user')->take('3')->orderBy('created_at','desc')->get();
        
            return view('frontend.home', [
            'productsView' => session() -> get('products_view'),
            'products' => Product::frontPage(),
            'categories' => Category::roots(),
            'featuredProducts' => $featuredProducts,
            'info' => $info,
            'searchMode' => $searchMode
        ]);
    }

    public function switchSearchMode()
    {
        $searchMode = session()->get('searchMode');
        if ($searchMode == null){
            $searchMode = 'simple';
        }
        if ($searchMode == 'simple'){
            $searchMode = 'advanced';
        } else if ($searchMode == 'advanced'){
            $searchMode = 'simple';
        }

        session()->forget('searchMode');
        session()->put('searchMode',$searchMode);

        return redirect()->back();
    }

    /**
     * Redirection to sing in
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login() {

        return redirect()->route('auth.signin');
    }

    public function confirmation(Request $request) {
        return view('confirmation');
    }

    /**
     * Show category page
     *
     * @param Category $category
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function category(Category $category) {

        return view('frontend.category', [
            'productsView' => session() -> get('products_view'),
            'category' => $category,
            'products' => $category->childProducts(),
            'categories' => Category::roots(),
        ]);
    }

    /**
     * Show vendor page, 6 products, and 10 feedbacks
     *
     * @param Vendor $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function vendor(Vendor $user) {
        return view('frontend.vendor.index',[
            'vendor' => $user->user
        ]);

         return view('vendor.index',[
            'vendor' => $user->user
        ]);

    }
    /**
     * Show page with vendors feedbacks
     *
     * @param Vendor $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function vendorsFeedbacks(Vendor $user) {
        return view('frontend.vendor.feedback', [
            'vendor' => $user->user,
            'feedback' => $user->feedback()->orderByDesc('created_at')->paginate(20),
        ]);
    }


    /**
     * Sets in session which view are we using
     *
     * @param $list
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setView($grid)
    {
        session() -> put('products_view', $grid);
        return redirect() -> back();
    }

    public function mirror($mirrorKey)
    {
        $mirrors = config('marketplace.mirrors');
        $foundMirror = null;
        foreach ($mirrors as $mirror){
            if ($mirror['key'] == $mirrorKey){
                $foundMirror = $mirror;
                break;
            }
        }
        if ($foundMirror == null){
            return redirect()->back();
        }

        return view('mirror')->with([
            'mirror' => $foundMirror,

        ]);
    }

    public function captcha()
     {
     $slug = Captcha::groupby('slug')->pluck('slug')->toArray();
     $key = array_rand($slug,1);
     $category_slug = $slug[$key];
     
     $captcha_result_img = Captcha::take(2)->where('slug',$category_slug)->get()->toArray();
     
     Session()->put('captcha_result_slug',$category_slug);
     Session()->put('captcha_result_count',count($captcha_result_img));
   
     $captcha_other_img = Captcha::where('slug','!=',$category_slug)->take(6)->inRandomOrder()->get()->toArray();

     $captcha_all = array_merge($captcha_result_img,$captcha_other_img);
     $collection = collect($captcha_all);
     $shuffled = $collection->shuffle();
     $shuffled_captcha = $shuffled->all();

     $data['slug'] = strtoupper($category_slug);
     $data['captcha_all'] = $shuffled_captcha;

     return view('frontend.captcha.captcha',$data);
    }


    public function completeCaptcha(Request  $request)
    {

     if(is_null($request->post('captcha'))){
  
      return redirect()->route('global.captcha')->with('errormessage','Please enter valid captcha');
      }
     
     $result = '';
   
     foreach($request->post('captcha') as $key => $value){
     if($value == Session()->get('captcha_result_slug')){
        $result = 200;
     }else{
        $result = 404;
     }
     }
    
     if($result == 200){
      
     if(count($request->post('captcha')) == Session()->get('captcha_result_count')){
      
      $result = 200;
      }else{
      $result = 404;
      }
      }
      
      session()->put('chk_captcha',$result);

      if($result == 404){

      return redirect()->route('global.captcha')->with(['errormessage'=>'Please enter valid captcha']);
      
      }else{
      
      return redirect()->route('home');
        
      }
    }

    public function announcement(){
    $data['info'] = Announcement::with('user')->get();
    return view('frontend.announcement',$data);
  }

    public function userControl(){
    //$data['info'] = Announcement::with('user')->get();     
    $data['user'] = User::where('id',auth()->user()->id)->first();
    $purchase = Purchase::where(['state'=>'delivered']);
      // $u = new User;
      // //dd($purchase->sum('to_pay'));
      // dd($u->getLocalPriceFrom((0.0021)));

    $data['total_spend'] = $purchase->sum('to_pay');
    $data['total_order'] = $purchase->count();
    return view('frontend.profile.usercontrol',$data);
  }

}
