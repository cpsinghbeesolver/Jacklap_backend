<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
  public function index()
  {
    //return view('frontend.auth.login');
    return view('content.authentications.auth-login-basic');
  }
}
