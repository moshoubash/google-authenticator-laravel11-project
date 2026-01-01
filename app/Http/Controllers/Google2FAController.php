<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Google2FAController extends Controller
{
    /**
     * Enable Two-Factor Authentication.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function enableTwoFactor(Request $request)
    {
        $google2fa = app('pragmarx.google2fa');
        $user = $request->user();

        // Generate the secret key
        $secret = $google2fa->generateSecretKey();

        // Save the secret key to the user
        $user->google2fa_secret = $secret;
        $user->save();

        // Generate the QR code URL
        $QR_Image = $google2fa->getQRCodeInline(
            config('app.name'),
            $user->email,
            $secret
        );

        return view('google2fa.enable', ['QR_Image' => $QR_Image, 'secret' => $secret]);
    }

    /**
     * Disable Two-Factor Authentication.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disableTwoFactor(Request $request)
    {
        $user = $request->user();
        $user->google2fa_secret = null;
        $user->save();

        return redirect('profile')->with('status', 'Two-Factor Authentication disabled.');
    }
}
