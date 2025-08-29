<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
        $this->middleware('auth');
    }

    // 2FA setup page show karna
    public function show()
    {
        $user = Auth::user();
        
        if (!$user->google2fa_secret) {
            $user->google2fa_secret = $this->google2fa->generateSecretKey();
            $user->save();
        }

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->google2fa_secret
        );

        $qrCode = $this->generateQrCode($qrCodeUrl);

        return view('auth.2fa.setup', compact('qrCode', 'user'));
    }

    // QR Code generate karna
    private function generateQrCode($qrCodeUrl)
    {
        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        
        return base64_encode($writer->writeString($qrCodeUrl));
    }

    // 2FA enable karna
    public function enable(Request $request)
    {
        $request->validate([
            'one_time_password' => 'required|numeric',
        ]);

        $user = Auth::user();
        $valid = $this->google2fa->verifyKey($user->google2fa_secret, $request->one_time_password);

        if ($valid) {
            $user->google2fa_enabled = true;
            $user->save();

            return redirect()->route('2fa.show')->with('success', '2FA successfully enabled!');
        }

        return back()->withErrors(['one_time_password' => 'Invalid OTP code']);
    }

    // 2FA disable karna
    public function disable(Request $request)
    {
        $request->validate([
            'one_time_password' => 'required|numeric',
        ]);

        $user = Auth::user();
        $valid = $this->google2fa->verifyKey($user->google2fa_secret, $request->one_time_password);

        if ($valid) {
            $user->google2fa_enabled = false;
            $user->google2fa_secret = null;
            $user->save();

            return redirect()->route('2fa.show')->with('success', '2FA successfully disabled!');
        }

        return back()->withErrors(['one_time_password' => 'Invalid OTP code']);
    }

    // Login ke time OTP verify karna
    public function verify(Request $request)
    {
        $request->validate([
            'one_time_password' => 'required|numeric',
        ]);

        $user = Auth::user();
        $valid = $this->google2fa->verifyKey($user->google2fa_secret, $request->one_time_password);

        if ($valid) {
            session(['2fa_verified' => true]);
            return redirect()->intended('/admin/dashboard');
        }

        return back()->withErrors(['one_time_password' => 'Invalid OTP code']);
    }

    // OTP verification page show karna
    public function showVerifyForm()
    {
        return view('auth.2fa.verify');
    }
}