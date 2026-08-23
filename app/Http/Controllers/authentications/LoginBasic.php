<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginBasic extends Controller
{
  public function index()
  {
    if (Auth::check()) {
      return redirect('/admin-dashboard');
    }
    return view('content.authentications.auth-login-basic');
  }

  public function userLogin()
  {
    // if (Auth::check()) {
    //   return redirect('/');
    // }
    return view('frontend.auth.login');
  }

  public function register()
  {
    // if (Auth::check()) {
    //   return redirect('/');
    // }
    return view('frontend.auth.register');
  }

  public function registerAccount()
  {
    // if (Auth::check()) {
    //   return redirect('/');
    // }
    return view('frontend.auth.register_account');
  }

  public function registerStepOne()
  {
    // if (Auth::check()) {
    //   return redirect('/');
    // }
    return view('frontend.provider.profile-step-1');
  }

  public function registerStepTwo()
  {
    // if (Auth::check()) {
    //   return redirect('/');
    // }
    return view('frontend.provider.profile-step-2');
  }

  public function registerStepThree()
  {
    // if (Auth::check()) {
    //   return redirect('/');
    // }
    return view('frontend.provider.profile-step-3');
  }

  public function verification()
  {
    // if (Auth::check()) {
    //   return redirect('/');
    // }
    return view('frontend.auth.verification');
  }

  public function forgotPassword()
  {
    // if (Auth::check()) {
    //   return redirect('/');
    // }
    return view('frontend.auth.forgot-password');
  }

  public function logout(Request $request)
  {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/')->with('success', 'You have been logged out.');
  }

  public function login(Request $request)
  {
    if ($request->isMethod('get')) {
      return redirect('/');
    }
    // Validation
    $credentials = $request->validate([
      'email' => 'required|string|email',
      'password' => 'required|string|min:6',
    ]);

    // Attempt to login
    if (Auth::attempt($credentials, $request->has('remember-me'))) {
      $request->session()->regenerate();
      //if (auth()->user()->hasRole('admin')) {
        return redirect(route('admin-dashboard'));
      //}
    }

    return back()->withErrors([
      'email' => 'The provided credentials do not match our records.',
    ]);
  }

}
