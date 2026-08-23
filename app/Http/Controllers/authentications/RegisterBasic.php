<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class RegisterBasic extends Controller
{
  public function index()
  {
    return view('content.authentications.auth-register-basic');
  }

  public function register(Request $request)
  {
    // Validation
    $request->validate([
      'name' => 'required|string|max:128',
      'email' => 'required|email|unique:users,email',
      'password' => 'required|string|min:6',
      'terms' => 'accepted',
      'phone_number' => 'required|regex:/^[0-9]+$/'
    ],['terms.accepted' => 'The Privacy policy & Terms must be accepted.']);

    // Create user
    $user = User::create([
      'name' => $request->name,
      'email' => $request->email,
      'password' => Hash::make($request->password),
      'phone' => $request->phone_number,
    ]);

    $role = Role::where(['name' => 'user'])->first();
    $user->assignRole([$role->id]);


    return redirect('/auth/login-basic')->with('success', 'Registration successful. Please login to continue!');
  }
}
