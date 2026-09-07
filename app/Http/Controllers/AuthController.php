<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = session('windows_user', 'Гость');
        
        return view('dashboard', [
            'user' => $user,
            'authenticated' => session()->has('windows_user')
        ]);
    }
    
    public function status(Request $request)
    {
        return response()->json([
            'authenticated' => session()->has('windows_user'),
            'user' => session('windows_user'),
            'session_id' => session()->getId(),
            'ip' => $request->ip()
        ]);
    }
    
    public function logout(Request $request)
    {
        session()->forget('windows_user');
        session()->invalidate();
        
        return redirect('/')->with('message', 'Вы вышли из системы');
    }
}