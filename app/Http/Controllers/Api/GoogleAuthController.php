<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\{OtpMail, BookingCreatedMail, BookingStatusMail};
use App\Models\Language;
use Illuminate\Support\Facades\Hash;
use App\Models\UserDevice;
use Google\Client;

class GoogleAuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/auth/google/register",
     *     summary="Register a new user with google login",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"social_id","name","email","role"},
     *
     *             @OA\Property(
     *                 property="social_id",
     *                 type="string",
     *                 example=""
     *             ),
     *             @OA\Property(
     *                 property="social_id_token",
     *                 type="string",
     *                 example=""
     *             ),
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
     *              @OA\Property(property="device_token", type="string", example="fcm_token_here"),
     *             @OA\Property(property="device_name", type="string", example="iPhone 14"),
     *             @OA\Property(property="device_type", type="integer", example=0)
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
    public function handle(Request $request)
    {
        // return env('GOOGLE_CLIENT_ID');
        $validated = $request->validate([
            'email'     => 'required|email|max:255|unique:users,email',
            'social_id' => 'required|string',
            'gender' => 'required|string',
            'phone' => 'nullable|string|max:15|unique:users,phone',
            'device_token'  => 'nullable|string',
            'device_name'   => 'nullable|string',
            'device_type'   => 'nullable|integer',
        ]);
        $validated['name'] = $request->name ?? '';
        $validated['password'] = $request->password ?? Hash::make(str()->random(24));
        //Verify Google Id
        $client = new Client([
            'client_id' => env('GOOGLE_CLIENT_ID'),
        ]);
        $signup = true;
        try{
            $payload = $client->verifyIdToken($request->social_id_token);
            if (!$payload) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Google Token'
                ], 401);
            }
            $user = User::create($validated);

            $user->assignRole($request->role);

            if (!empty($request->languages)) {
                $languages = array_filter(array_map('trim', explode(',', $request->languages)));
                
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

            // Mark verified
            $user->update([
                'email_verified_at' => now(),
                'otp' => null,
                'otp_expires_at' => null
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

        // Active role only for single-role users
        $role = $request->role;
 
        $response = response()->json([
            'success' => true,
            'message' => 'User registered successfully. Please check your email for OTP.',
            'token'   => $token,
            'user'    => [
                'id'                 => $user->id,
                'name'               => $user->name,
                'email'              => $user->email,
                'dob'                => $user->dob,
                'gender'             => $user->gender,
                'phone'              => $user->phone,
                'role'               => $request->role,
                'roles'              => $user->getRoleNames()->values()->toArray(),
                'languages'          => $user->languages
            ],
        ], 201);
 
        $isloggedIn = $user->email_verified_at ? 'true' : 'false';
        if($user->email_verified_at){
            $isloggedIn = 'true';
        }
        return $response
            ->cookie('isLoggedIn', $isloggedIn, 120, '/', null, true, false)
            ->cookie('userRole', $role, 120, '/', null, true, false);
    }


    /**
     * @OA\Post(
     *     path="/auth/google/login",
     *     tags={"Auth"},
     *     summary="Google Api Login",
     *     description="Authenticate user and return access token",
     *     operationId="apiGooleLoginUser",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","social_id"},
     *             @OA\Property(property="email", type="string", format="email", example="provider.morgan@example.com"),
     *             @OA\Property(property="social_id", type="string", format="string", example="abcd"),
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
    public function login(Request $request){
        $request->validate([
            'email'    => 'required|email',
            'social_id'    => 'required',
            'device_token'  => 'nullable|string',
            'device_name'   => 'nullable|string',
            'device_type'   => 'nullable|integer',
        ]);
        $user = User::where('email', $request->email)->first();  
        if(!$user){
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        // Google account login
        if ($user->social_id) {
            if ($request->social_id !== $user->social_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Google account',
                ], 400);
            }
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Missing Google account',
            ], 400);
        }
        $roles = $user->getRoleNames()->values()->toArray();

        // Check if user has only one role
        $hasSingleRole = count($roles) === 1;

        // Active role only for single-role users
        $role = $hasSingleRole ? $roles[0] : null;

        // Only load provider data if user has provider role
        if ($user->hasRole('provider')) {
            $user->load([
                'services',
                'addonServices',
                'professionalDetail',
                'media',
                'media.certificates',
                'bankDetail',
                'languages'
            ]);
        }

        // Only set active role when user has a single role
        if ($hasSingleRole) {
            $user->setAttribute('role', $role);
        }
        
        $request->session()->regenerate();
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
        
        $response = response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'token'   => $token,
            'user'    => $user
        ]);
       if ($hasSingleRole) {
            $response->cookie(
                'isLoggedIn',
                $isLoggedIn,
                120,
                '/',
                null,
                true,
                false
            );
            // Provider profile incomplete
            if ($role === 'provider' && $user->profile_step < 3) {
                $response->cookie(
                    'is_auth_incomplete',
                    'true',
                    120,
                    '/',
                    null,
                    true,
                    false
                );
            } else {
                $response->withoutCookie('is_auth_incomplete');
            }

            // Set active role
            return $response->cookie(
                'userRole',
                $role,
                120,
                '/',
                null,
                true,
                false
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Multiple Role User
        |--------------------------------------------------------------------------
        |
        | Do not set any active role.
        | User must select a role and call switch-role API.
        |
        */

        return $response
            ->withoutCookie('isLoggedIn')
            ->withoutCookie('userRole')
            ->withoutCookie('is_auth_incomplete');
    }
}
