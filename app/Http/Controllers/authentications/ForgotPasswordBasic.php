<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ForgotPasswordBasic extends Controller
{
  public function index()
  {
    return view('content.authentications.auth-forgot-password-basic');
  }

  public function sendResetLinkEmail(Request $request)
  {
      // Validate email
      $request->validate([
          'email' => 'required|email|exists:users,email',
      ]);

      // Send reset link
      // $status = Password::sendResetLink(
      //     $request->only('email')
      // );

      return back()->with('success', 'We have emailed you password reset link!');
  }
}
