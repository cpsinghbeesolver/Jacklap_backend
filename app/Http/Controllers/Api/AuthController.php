<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Models\Faq;
use App\Models\Language;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\UserDevice;
use Illuminate\Support\Facades\Auth;
use App\Mail\{OtpMail, BookingCreatedMail, BookingStatusMail};


class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/user/login",
     *     tags={"Web Auth"},
     *     summary="Login",
     *     description="Authenticate user and return access token",
     *     operationId="loginUser",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="alex.morgan@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="Password@123"),
     *             @OA\Property(property="device_token", type="string", example="fcm_token_here"),
     *             @OA\Property(property="device_name", type="string", example="iPhone 14"),
     *             @OA\Property(property="device_type", type="integer", example=0)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login successful."),
     *             @OA\Property(property="token", type="string", example="1|asdkjlasdkljasdklj"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Alex Morgan"),
     *                 @OA\Property(property="email", type="string", example="alex.morgan@example.com"),
     *                 @OA\Property(property="dob", type="string", format="date", example="1998-01-01"),
     *                 @OA\Property(property="gender", type="string", example="male"),
     *                 @OA\Property(property="phone", type="string", example="9876543210"),
     *                 @OA\Property(property="role", type="string", example="provider"),
     *                 @OA\Property(property="email_verfified_at", type="string", example="2026-02-24T05:36:45.000000Z"),
     *                 @OA\Property(property="professionalDetail", type="string", example="yes")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid credentials")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
            'device_token'  => 'nullable|string',
            'device_name'   => 'nullable|string',
            'device_type'   => 'nullable|integer',
        ]);
        $user = User::where('email', $request->email)->first();  
        
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 400);
        }

        // $credentials = $request->only('email', 'password');

        // if(!Auth::attempt($credentials)) {
        //     return response()->json(['message' => 'Invalid credentials'], 400);
        // }

        // $request->session()->regenerate();
        // Create Sanctum token
        $token = $user->createToken(
            $request->device_name ?? 'NextJS'
        )->plainTextToken;

        //$user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 500);
        }
        if ($user) {
            $user->setAttribute(
                'role',
                $user->hasRole('provider') ? 'provider' : 'seeker'
            );
        }
        if($user->hasRole('provider')){
            // if (!$user->is_checked) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Your account is under review. Please wait for approval.',
            //     ], 400);
            // }
            $user->load(['services','addonServices', 'professionalDetail', 'media','media.certificates', 'bankDetail','languages']);
        }

        $role = $user->hasRole('provider') ? 'provider' : 'seeker';

        if ($request->device_token) {
            UserDevice::updateOrCreate(
                [
                    'device_token' => $request->device_token,
                ],
                [
                    'user_id'     => $user->id,
                    'device_name' => $request->device_name,
                    'device_type' => $request->device_type,
                ]
            );
        }

        $response = response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'token'   => $token,
            'token_type' => 'Bearer',
            'user'    => $user
        ]);
        $isloggedIn = $user->email_verified_at ? 'true' : 'false';
        if($user->email_verified_at){
            $isloggedIn = 'true';
        }
        return $response
            ->cookie('isLoggedIn', $isloggedIn, 120, '/', null, true, false)
            ->cookie('userRole', $role, 120, '/', null, true, false);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['success' => true, 'message' => 'Logged out'])
        ->withCookie(cookie()->forget('laravel_session'))
        ->withCookie(cookie()->forget('isLoggedIn'))
        ->withCookie(cookie()->forget('userRole'))
        ->withCookie(cookie()->forget('is_auth_incomplete'))
        ->withCookie(cookie()->forget('XSRF-TOKEN'));
    }

    /**
     * @OA\Post(
     *     path="/user/api-login",
     *     tags={"Auth"},
     *     summary="Api Login",
     *     description="Authenticate user and return access token",
     *     operationId="apiLoginUser",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="provider@gmail.com"),
     *             @OA\Property(property="password", type="string", format="password", example="Test@123"),
     *             @OA\Property(property="device_token", type="string", example="fcm_token_here"),
     *             @OA\Property(property="device_name", type="string", example="iPhone 14"),
     *             @OA\Property(property="device_type", type="integer", example=0)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login successful."),
     *             @OA\Property(property="token", type="string", example="1|asdkjlasdkljasdklj"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Alex Morgan"),
     *                 @OA\Property(property="email", type="string", example="alex.morgan@example.com"),
     *                 @OA\Property(property="dob", type="string", format="date", example="1998-01-01"),
     *                 @OA\Property(property="gender", type="string", example="male"),
     *                 @OA\Property(property="phone", type="string", example="9876543210"),
     *                 @OA\Property(property="role", type="string", example="provider"),
     *                 @OA\Property(property="email_verfified_at", type="string", example="2026-02-24T05:36:45.000000Z"),
     *                 @OA\Property(property="professionalDetail", type="string", example="yes")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid credentials")
     *         )
     *     )
     * )
     */
    public function apiLogin(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
            'device_token'  => 'nullable|string',
            'device_name'   => 'nullable|string',
            'device_type'   => 'nullable|integer',
        ]);
        $user = User::where('email', $request->email)->first();  
        
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 400);
        }

        if (filled($user->google_id)) {
            return response()->json([
                'success' => false,
                'message' => 'This account was created using Google Sign-In. Please sign in with Google to continue.',
            ], 400);
        }

        if ($user) {
            $user->setAttribute(
                'role',
                $user->hasRole('provider') ? 'provider' : 'seeker'
            );
        }
        if($user->hasRole('provider')){
            // if (!$user->is_checked) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Your account is under review. Please wait for approval.',
            //     ], 400);
            // }
            $user->load(['services','addonServices', 'professionalDetail', 'media','media.certificates', 'bankDetail','languages']);
        }
        

        $token = $user->createToken('auth-token')->plainTextToken;

        if ($request->device_token) {
            UserDevice::updateOrCreate(
                [
                    'device_token' => $request->device_token,
                ],
                [
                    'user_id'     => $user->id,
                    'device_name' => $request->device_name,
                    'device_type' => $request->device_type,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'token'   => $token,
            'user'    => $user
        ]);
    }

    /**
     * @OA\Post(
     *     path="/user/signup",
     *     summary="Register a new user and send OTP",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","dob","phone","gender","role"},
     *
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="Alex Morgan"
     *             ),
     *
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="alex.morgan@example.com"
     *             ),
     *
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 format="password",
     *                 example="StrongPass@123"
     *             ),
     *
     *             @OA\Property(
     *                 property="dob",
     *                 type="string",
     *                 format="date",
     *                 example="1993-04-21"
     *             ),
     *
     *             @OA\Property(
     *                 property="phone",
     *                 type="string",
     *                 example="9876543210"
     *             ),
     *
     *             @OA\Property(
     *                 property="gender",
     *                 type="string",
     *                 enum={"male","female"},
     *                 example="male"
     *             ),
     *             @OA\Property(
     *                 property="languages",
     *                 type="string",
     *                 example="English, Hindi"
     *             ),
     *
     *             @OA\Property(
     *                 property="role",
     *                 type="string",
     *                 enum={"provider","seeker"},
     *                 example="provider"
     *             ),
     *             @OA\Property(
     *                 property="device_token",
     *                 type="string",
     *                 example=""
     *             ),
     *              @OA\Property(
     *                 property="device_name",
     *                 type="string",
     *                 example="Samsung"
     *             ),
     *              @OA\Property(
     *                 property="device_type",
     *                 type="string",
     *                 example="0"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully and OTP sent",
     *
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User registered successfully. Please check your email for OTP.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation Failed"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={
     *                     "email": {"The email has already been taken."},
     *                     "password": {"The password field is required."}
     *                 }
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function signup(RegisterUserRequest $request)
    {
        // $response = Http::withHeaders([
        //     'X-API-Key' => env('CREDIBLED_API_KEY'),
        // ])->get('https://api.credibled.com/api/external/v1/check-types/');

        // if ($response->successful()) {
        //     $data = $response->json();

        //     return response()->json([
        //         'success' => true,
        //         'data' => $data,
        //     ]);
        // }

        // return response()->json([
        //     'success' => false,
        //     'message' => 'Credibled API request failed.',
        //     'status' => $response->status(),
        //     'response' => $response->body(),
        // ], $response->status());
        
        
        $validated = $request->validated();
        $otp = rand(100000, 999999);
        $user = User::where('email', $validated['email'])->first();
        
        if (!$user) {
            $user = User::create([
                'name'           => $validated['name'],
                'email'          => $validated['email'],
                'password'       => Hash::make($validated['password']),
                'dob'            => $validated['dob'] ?? null,
                'gender'         => $validated['gender'] ?? null,
                'phone'          => $validated['phone'],
                'otp'            => $otp,
                'otp_expires_at' => now()->addMinutes(2),
            ]);
        } else {
            $user->update([
                'otp' => $otp,
                'otp_expires_at' => now()->addMinutes(2),
            ]);
        }

        $user->assignRole($validated['role']);

        // Send OTP email
        // Mail::raw("Your OTP code is: $otp", function ($message) use ($user) {
        //     $message->to($user->email)
        //             ->subject('Your OTP Code');
        // });
        Mail::to($user->email)->send(
            new OtpMail($user, $otp, 'signup')
        );

        if (!empty($validated['languages'])) {
            $languages = array_filter(array_map('trim', explode(',', $validated['languages'])));
            
            $languageIds = [];

            foreach ($languages as $lang) {
                // Check or create master language
                $masterLanguage = Language::firstOrCreate(['name' => $lang]);
                $languageIds[] = $masterLanguage->id;

                // Insert or update user's language
                $user->languages()->updateOrCreate(
                    ['language_id' => $masterLanguage->id],
                    [
                        'language'    => $lang
                    ]
                );
            }

            // Remove any user languages that are no longer selected
            $user->languages()->whereNotIn('language_id', $languageIds)->delete();
        }

        //Save user device
        if ($request->device_token) {
            UserDevice::updateOrCreate(
                [
                    'device_token' => $request->device_token,
                ],
                [
                    'user_id'     => $user->id,
                    'device_name' => $request->device_name,
                    'device_type' => $request->device_type,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully. Please check your email for OTP.',
            'user'    => [
                'id'                 => $user->id,
                'name'               => $user->name,
                'email'              => $user->email,
                'dob'                => $user->dob,
                'gender'             => $user->gender,
                'phone'              => $user->phone,
                'role'               => $user->getRoleNames()->first(),
                'languages'          => $user->languages
            ],
        ], 201);
    }
    
    /**
     * @OA\Post(
     *     path="/user/api-verify-otp",
     *     summary="Verify OTP and generate authentication token",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","otp"},
     *
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="alex.morgan@example.com"
     *             ),
     *
     *             @OA\Property(
     *                 property="otp",
     *                 type="string",
     *                 example="123456"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="OTP verified successfully and token generated",
     *
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OTP verified successfully."),
     *
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *
     *                 @OA\Property(
     *                     property="token",
     *                     type="string",
     *                     example="1|AbCdEfGhIjKlMnOpQrStUvWxYz"
     *                 ),
     *
     *                 @OA\Property(
     *                     property="token_type",
     *                     type="string",
     *                     example="Bearer"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Invalid OTP or OTP expired",
     *
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid OTP.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={
     *                     "email": {"The selected email is invalid."},
     *                     "otp": {"The otp field is required."}
     *                 }
     *             )
     *         )
     *     )
     * )
     */
    public function apiVerifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp'   => 'required|digits:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        if ($user->otp == null && $user->email_verified_at !== null) {
            return response()->json([
                'success' => false,
                'message' => 'User already verified.'
            ], 400);
        }

        if ($user->otp !== $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP.'
            ], 400);
        }

        if ($user->otp_expires_at === null || now()->greaterThan($user->otp_expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired.'
            ], 400);
        }

        // Mark verified
        $user->update([
            'email_verified_at' => now(),
            'otp' => null,
            'otp_expires_at' => null
        ]);

        // Generate Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        // if ($request->device_token) {
        //     UserDevice::updateOrCreate(
        //         [
        //             'device_token' => $request->device_token,
        //         ],
        //         [
        //             'user_id'     => $user->id,
        //             'device_name' => $request->device_name,
        //             'device_type' => $request->device_type,
        //         ]
        //     );
        // }

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully.',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/user/verify-otp",
     *     summary="Verify OTP and generate authentication token",
     *     tags={"Web Auth"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","otp"},
     *
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="alex.morgan@example.com"
     *             ),
     *
     *             @OA\Property(
     *                 property="otp",
     *                 type="string",
     *                 example="123456"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="OTP verified successfully and token generated",
     *
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OTP verified successfully."),
     *
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *
     *                 @OA\Property(
     *                     property="token",
     *                     type="string",
     *                     example="1|AbCdEfGhIjKlMnOpQrStUvWxYz"
     *                 ),
     *
     *                 @OA\Property(
     *                     property="token_type",
     *                     type="string",
     *                     example="Bearer"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Invalid OTP or OTP expired",
     *
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid OTP.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={
     *                     "email": {"The selected email is invalid."},
     *                     "otp": {"The otp field is required."}
     *                 }
     *             )
     *         )
     *     )
     * )
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp'   => 'required|digits:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        if ($user->otp == null && $user->email_verified_at !== null) {
            return response()->json([
                'success' => false,
                'message' => 'User already verified.'
            ], 400);
        }

        if ((string)$user->otp !== (string)$request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP.'
            ], 400);
        }

        if ($user->otp_expires_at === null || now()->greaterThan($user->otp_expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired.'
            ], 400);
        }

        // Mark verified
        $user->update([
            'email_verified_at' => now(),
            'otp' => null,
            'otp_expires_at' => null
        ]);

        // Generate Sanctum token
        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        $user->load(['roles', 'professionalDetail', 'media', 'bankDetail']);
        $role = $user->hasRole('provider') ? 'provider' : 'seeker';
        $response = response()->json([
            'success' => true,
            'message' => 'OTP verified successfully.',
            'user'    => $user
        ]);

        $response->cookie('isLoggedIn', 'true', 120, '/', null, true, false);
        $response->cookie('userRole', $role, 120, '/', null, true, false);
        
        if ($role === 'provider') {
            $response->cookie('is_auth_incomplete', 'true', 120, '/', null, true, false);
        } else {
            $response->withoutCookie('is_auth_incomplete');
        }

        return $response;
    }


     /**
     * @OA\Post(
     *     path="/user/verify-otp-forgot-password",
     *     summary="Verify OTP for forget password and generate authentication token",
     *     tags={"Web Auth"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","otp"},
     *
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="alex.morgan@example.com"
     *             ),
     *
     *             @OA\Property(
     *                 property="otp",
     *                 type="string",
     *                 example="123456"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="OTP verified successfully and token generated",
     *
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OTP verified successfully."),
     *
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *
     *                 @OA\Property(
     *                     property="token",
     *                     type="string",
     *                     example="1|AbCdEfGhIjKlMnOpQrStUvWxYz"
     *                 ),
     *
     *                 @OA\Property(
     *                     property="token_type",
     *                     type="string",
     *                     example="Bearer"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Invalid OTP or OTP expired",
     *
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid OTP.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={
     *                     "email": {"The selected email is invalid."},
     *                     "otp": {"The otp field is required."}
     *                 }
     *             )
     *         )
     *     )
     * )
     */
    public function verifyOtpForgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp'   => 'required|digits:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        if ($user->otp == null && $user->email_verified_at !== null) {
            return response()->json([
                'success' => false,
                'message' => 'User already verified.'
            ], 400);
        }

        if ((string)$user->otp !== (string)$request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP.'
            ], 400);
        }

        if ($user->otp_expires_at === null || now()->greaterThan($user->otp_expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired.'
            ], 400);
        }

        // Mark verified
        $user->update([
            'email_verified_at' => now(),
            'otp' => null,
            'otp_expires_at' => null
        ]);

        // Generate Sanctum token
        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        $user->load(['roles', 'professionalDetail', 'media', 'bankDetail']);
        $role = $user->hasRole('provider') ? 'provider' : 'seeker';
        $response = response()->json([
            'success' => true,
            'message' => 'OTP verified successfully.',
            'user'    => $user
        ]);

        
        if ($role === 'provider') {
            $response->cookie('is_auth_incomplete', 'true', 120, '/', null, true, false);
        } else {
            $response->withoutCookie('is_auth_incomplete');
        }

        return $response;
    }


    /**
     * @OA\Post(
     *     path="/user/resend-otp",
     *     summary="Resend OTP to user's email",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP resent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OTP resent to your email.")
     *         )
     *     )
     * )
     */

    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        $otp = rand(100000, 999999);
        $user->otp = $otp;
        $user->otp_expires_at = now()->addMinutes(2);
        $user->save();

        // Send email
        // Mail::raw("Your new OTP is: $otp", function ($message) use ($user) {
        //     $message->to($user->email)
        //             ->subject('Your OTP Code');
        // });
        Mail::to($user->email)->send(
            new OtpMail($user, $otp, 'resend_signup')
        );

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to your email.'
        ]);
    }

    /**
    * @OA\Post(
    *     path="/user/forgot-password",
    *     summary="Send OTP to email for password reset",
    *     tags={"Auth"},
    *     @OA\RequestBody(
    *         required=true,
    *         @OA\JsonContent(
    *             required={"email"},
    *             @OA\Property(property="email", type="string", example="user@example.com")
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="OTP sent successfully",
    *         @OA\JsonContent(
    *             @OA\Property(property="success", type="boolean", example=true),
    *             @OA\Property(property="message", type="string", example="OTP sent to your email.")
    *         )
    *     ),
    *     @OA\Response(
    *         response=422,
    *         description="Invalid input or email not found"
    *     )
    * )
    */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        // Check if email is verified
        if (empty($user->email_verified_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your email first.'
            ], 400);
        }

        $otp = rand(100000, 999999);
        $user->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(2)
        ]);

        // Mail::raw("Your OTP for password reset is: $otp", function ($message) use ($user) {
        //     $message->to($user->email)
        //         ->subject('Reset Password OTP');
        // });
        Mail::to($user->email)->send(
            new OtpMail($user, $otp, 'forgot_password')
        );

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to your email.'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/user/reset-password",
     *     summary="Reset password after OTP verification",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "new_password"},
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="new_password", type="string", format="password", example="newpass123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password has been reset successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'new_password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $user->password = Hash::make($request->new_password);
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully.',
        ]);
    }

    // /**
    //  * @OA\Get(
    //  *     path="/pages",
    //  *     summary="Get all active pages",
    //  *     description="Returns a list of active static pages (title and slug only)",
    //  *     operationId="getPages",
    //  *     tags={"Pages"},
    //  *     @OA\Response(
    //  *         response=200,
    //  *         description="List of pages",
    //  *         @OA\JsonContent(
    //  *             @OA\Property(property="success", type="boolean", example=true),
    //  *             @OA\Property(property="message", type="string", example="Pages fetched successfully"),
    //  *             @OA\Property(
    //  *                 property="data",
    //  *                 type="array",
    //  *                 @OA\Items(
    //  *                     @OA\Property(property="id", type="integer", example=1),
    //  *                     @OA\Property(property="title", type="string", example="About Us"),
    //  *                     @OA\Property(property="slug", type="string", example="about-us")
    //  *                 )
    //  *             )
    //  *         )
    //  *     )
    //  * )
    //  */
    public function pageList()
    {
        $pages = Page::where('is_active', true)
                     ->select('id', 'title', 'slug')
                     ->get();

        return response()->json([
            'success' => true,
            'message' => 'Pages fetched successfully',
            'data'    => $pages
        ]);
    }

    // /**
    //  * @OA\Get(
    //  *     path="/pages/{slug}",
    //  *     summary="Get page details by slug",
    //  *     description="Returns full content of a specific page by its slug if active",
    //  *     operationId="getPageBySlug",
    //  *     tags={"Pages"},
    //  *     @OA\Parameter(
    //  *         name="slug",
    //  *         in="path",
    //  *         required=true,
    //  *         description="Slug of the page (e.g., about-us)",
    //  *         @OA\Schema(type="string")
    //  *     ),
    //  *     @OA\Response(
    //  *         response=200,
    //  *         description="Page found",
    //  *         @OA\JsonContent(
    //  *             @OA\Property(property="success", type="boolean", example=true),
    //  *             @OA\Property(property="message", type="string", example="Page retrieved successfully"),
    //  *             @OA\Property(
    //  *                 property="data",
    //  *                 type="object",
    //  *                 @OA\Property(property="id", type="integer", example=1),
    //  *                 @OA\Property(property="title", type="string", example="About Us"),
    //  *                 @OA\Property(property="slug", type="string", example="about-us"),
    //  *                 @OA\Property(property="content", type="string", example="This is our about us page."),
    //  *                 @OA\Property(property="meta_title", type="string", example="About Our Company"),
    //  *                 @OA\Property(property="meta_description", type="string", example="Learn more about our values."),
    //  *                 @OA\Property(property="is_active", type="boolean", example=true),
    //  *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-01T12:00:00Z"),
    //  *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-01T12:00:00Z")
    //  *             )
    //  *         )
    //  *     ),
    //  *     @OA\Response(
    //  *         response=404,
    //  *         description="Page not found",
    //  *         @OA\JsonContent(
    //  *             @OA\Property(property="success", type="boolean", example=false),
    //  *             @OA\Property(property="message", type="string", example="Page not found")
    //  *         )
    //  *     )
    //  * )
    //  */
     public function pageDetail($slug)
    {
        $page = Page::where('slug', $slug)->where('is_active', true)->first();

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Page fetched successfully',
            'data'    => $page
        ]);
    }

    // /**
    //  * @OA\Get(
    //  *     path="/faqs",
    //  *     tags={"Pages"},
    //  *     summary="Get list of active FAQs",
    //  *     description="Returns a list of FAQs with active status",
    //  *     @OA\Response(
    //  *         response=200,
    //  *         description="Successful response",
    //  *         @OA\JsonContent(
    //  *             @OA\Property(property="success", type="boolean", example=true),
    //  *             @OA\Property(property="message", type="string", example="FAQ list fetched successfully"),
    //  *             @OA\Property(property="data", type="array", @OA\Items(
    //  *                 @OA\Property(property="id", type="integer", example=1),
    //  *                 @OA\Property(property="question", type="string", example="What is your return policy?"),
    //  *                 @OA\Property(property="answer", type="string", example="We offer a 7-day return policy."),
    //  *                 @OA\Property(property="status", type="boolean", example=true),
    //  *             ))
    //  *         )
    //  *     )
    //  * )
    //  */
    public function faqs()
    {
        $faqs = Faq::where('status', true)->get();

        return response()->json([
            'success' => true,
            'message' => 'FAQ list fetched successfully',
            'data' => $faqs
        ]);
    }
     
}