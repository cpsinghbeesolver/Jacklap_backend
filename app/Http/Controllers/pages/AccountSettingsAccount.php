<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountSettingsAccount extends Controller
{
  public function index()
  {
    $userId = Auth::id();
    $user = User::findOrFail($userId);
    // $countries = Country::all();
    return view('content.pages.pages-account-settings-account')
          ->with(['user' => $user]);
  }
}
