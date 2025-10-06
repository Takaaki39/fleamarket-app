<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    public function show()
    {
        return view('auth.verify-manual');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'verify_code' => 'required|string'
        ]);

        $user = Auth::user();
        $savedCode = Cache::get('verify_code_'.$user->id);

        if ($savedCode && $savedCode === $request->verify_code) 
        {
            $user->markEmailAsVerified();
            Cache::forget('verify_code_'.$user->id);
            return redirect('/mypage/profile')->with('success', 'メール認証が完了しました！');
        }

        return back()->with('error', '認証コードが正しくありません。');
    }
}
