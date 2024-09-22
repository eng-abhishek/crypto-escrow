<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\SignUpRequest;
// use App\Marketplace\Utility\Captcha;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Captcha;

class RegisterController extends Controller {

    /**
     * Show view for sign up, if refid is provided pass it along
     * @param string $refid
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showSignUp($refid = '') {

        return view('frontend.auth.signup')->with([
            'refid' => $refid,
            'captcha' => Captcha::src('default')
        ]);
    }


    /**
     * Try to complete SignUpRequest, if success redirect to mnemonic
     * if fail redirect back
     * @param SignUpRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function signUpPost(SignUpRequest $request) {
          session()->forget('img_url');
        if (!config('marketplace.registration_enabled')){
            session()->flash('error', 'Registration is currently disabled');
            return redirect()->back();
        }
        try {
            $request->persist();
            return redirect()->route('auth.mnemonic');
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back();
        }

    }

    /**
     * If there is mnemonic_key in session, show it to user, if not
     * redirect to signin page
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function showMnemonic() {
        if (!session()->has('mnemonic_key'))
            return redirect()->route('auth.signin');
        return view('frontend.auth.mnemonic')->with('mnemonic', session()->get('mnemonic_key'));
    }
}
