<?php

namespace App\Http\Controllers;

use App\Captcha;
use Illuminate\Http\Request;
use App\Http\Requests\CompleteCaptchaRequest2;

class CaptchaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function matchCaptcha(CompleteCaptchaRequest2 $request)
    {
     //return $request->post();
     // print_r(Session()->get('captcha_result_slug'));
     // echo"<br>";
     // print_r(Session()->get('captcha_result_count'));
     // echo"<br>";
     // print_r(count($request->post('captcha')));

    
      if($request->post('captcha') == ''){
      return redirect()->route('captcha-image')->with(['errormessage'=>'Please enter valid captcha']);
      }

     $result = '';
     foreach($request->post('captcha') as $key => $value){
     if($value == Session()->get('captcha_result_slug')){
        $result = true;
     }else{
        $result = false;
     }
     }

     if($result == true){
      
     if(count($request->post('captcha')) == Session()->get('captcha_result_count')){
      
      $result = true;
      }else{
      $result = false;
      }
      }

      if($result == false){

      return redirect()->route('captcha-image')->with(['errormessage'=>'Please enter valid captcha']);
      
      }


      // dd($result);
      }

    public function checkCaptcha(){
     echo session()->get('chk_captcha');
      
     return view('frontend.captcha.captcha2');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\captcha  $captcha
     * @return \Illuminate\Http\Response
     */
    public function show(captcha $captcha)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\captcha  $captcha
     * @return \Illuminate\Http\Response
     */
    public function edit(captcha $captcha)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\captcha  $captcha
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, captcha $captcha)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\captcha  $captcha
     * @return \Illuminate\Http\Response
     */
    public function destroy(captcha $captcha)
    {
        //
    }
}
