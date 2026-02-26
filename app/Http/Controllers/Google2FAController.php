<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Google2FAController extends Controller
{
    public function enableTwoFactor(Request $request)
    {
        $google2fa = app('pragmarx.google2fa');
        $user = $request->user();

        $secret = $google2fa->generateSecretKey();

        $request->session()->put('google2fa_secret_temp', $secret);

        $QR_Image = $google2fa->getQRCodeInline(
            config('app.name'),
            $user->email,
            $secret
        );

        return view('google2fa.enable', ['QR_Image' => $QR_Image, 'secret' => $secret]);
    }

    public function verifyTwoFactor(Request $request)
    {
        $request->validate([
            'one_time_password' => 'required',
        ]);

        $google2fa = app('pragmarx.google2fa');
        $user = $request->user();

        $secret = $request->session()->get('google2fa_secret_temp');

        if (!$secret) {
            return redirect()->route('2fa.enable')->with('error', 'No secret found. Please start the setup again.');
        }

        $otp = $request->input('one_time_password');

        if ($google2fa->verifyKey($secret, $otp)) {
            $user->google2fa_secret = encrypt($secret);
            $user->save();

            $request->session()->forget('google2fa_secret_temp');

            return redirect()->route('profile.edit')->with('status', 'Two-Factor Authentication enabled.');
        }

        return redirect()->back()->with('error', 'Invalid verification code. Please try again.');
    }

    public function disableTwoFactor(Request $request)
    {
        $user = $request->user();
        $user->google2fa_secret = null;
        $user->save();

        return redirect('profile')->with('status', 'Two-Factor Authentication disabled.');
    }

    public function back(Request $request)
    {
        if($request->user()->google2fa_secret) {
            $user = $request->user();
            $user->google2fa_secret = null;
            $user->save();

            return redirect('profile');
        }

        return redirect('profile');
    }
}
