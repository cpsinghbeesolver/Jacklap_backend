<?php

namespace App\Http\Controllers\seeker;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
  public function index()
  {
    return view('frontend.seeker.homepage');
  }
}
