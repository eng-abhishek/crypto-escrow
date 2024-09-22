<?php

namespace App\Http\Controllers\Admin;
use App\Setting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\SettingRequest;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
     $getData = Setting::take(1)->orderBy('id','desc')->first();
     return view('admin.setting',['record'=>$getData]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(SettingRequest $request)
    {
       $getData = Setting::take(1)->orderBy('id','desc')->first();
       $record = array(
               'key_type' => $request->get('key_type'),
               'api_key' => $request->get('key')
                  );
       if(!empty($getData)){
        
        Setting::where('id',$getData->id)->update($record);
        session() -> flash('success', 'You have successfully updated key!');
        return redirect()->route('admin.setting');
       }else{

        Setting::Create($record);
        session() -> flash('success', 'You have successfully created key!');
        return redirect()->route('admin.setting');
       }  
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
     * @param  \App\setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function show(setting $setting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function edit(setting $setting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, setting $setting)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function destroy(setting $setting)
    {
        //
    }
}
