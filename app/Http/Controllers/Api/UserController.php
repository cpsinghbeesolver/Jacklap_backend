<?php

namespace App\Http\Controllers\Api;

use App\Events\LocationUpdated;
use App\Http\Controllers\Controller;
use App\Models\AvailabilitySlot;
use App\Models\BankDetail;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\MediaResource;
use App\Models\Booking;
use App\Models\Language;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/profile",
     *     tags={"Users"},
     *     summary="Get User Profile (For Testing Purpose)",
     *     description="Fetch authenticated user profile details using Sanctum Bearer Token. This is for testing purpose.",
     *     operationId="getUserProfile",
     *
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Profile fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User profile fetched successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", type="object"),
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */

    public function getProfile(Request $request)
    {
        $user = User::find($request->user()->id);
        
        if ($user) {
            $user->setAttribute(
                'role',
                $user->hasRole('provider') ? 'provider' : 'seeker'
            );
        }
        if($user->hasRole('provider')){
            $user->load(['services','addonServices', 'professionalDetail', 'bankDetail','languages']);
        }
        $user->average_rating = 0;
        // $url = Storage::disk('s3')->temporaryUrl(
        //     $user->image,
        //     now()->addMinutes(15)
        // );
        // $user['image_url'] = $url;
        return response()->json([
            'success' => true,
            'message' => 'User profile fetched successfully.',
            'data'    => $user
        ]);
    }

    /**
     * @OA\Post(
     *     path="/change-password",
     *     summary="Change password for authenticated user",
     *     security={{ "bearerAuth": {} }},
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password", "new_password", "new_password_confirmation"},
     *             @OA\Property(property="current_password", type="string", example="oldpass123"),
     *             @OA\Property(property="new_password", type="string", example="newpass456"),
     *             @OA\Property(property="new_password_confirmation", type="string", example="newpass456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password changed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password changed successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation or password error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Current password is incorrect.")
     *         )
     *     )
     * )
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.'
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/logout",
     *     summary="Logout the authenticated user",
     *     tags={"Users"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="User logged out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logged out successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/edit-profile",
     *     summary="Edit authenticated user profile",
     *     description="Allows an authenticated user to update their profile details like name, email, phone, and country code.",
     *     operationId="editProfile",
     *     tags={"Users"},
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email","dob","gender"},
     *             @OA\Property(property="name", type="string", example="test user"),
     *             @OA\Property(property="email", type="string", format="email", example="test@example.com"),
     *             @OA\Property(property="phone", type="string", example="9876543210"),
     *             @OA\Property(property="country_code", type="string", example="+91"),
     *             @OA\Property(
     *                 property="dob",
     *                 type="string",
     *                 format="date",
     *                 example="1993-04-21"
     *             ),
    *             @OA\Property(
    *                 property="languages",
    *                 type="string",
    *                 example="English, Hindi"
    *             ),
     *             @OA\Property(
     *                 property="gender",
     *                 type="string",
     *                 enum={"male","female"},
     *                 example="male"
     *             ),
     *            @OA\Property(
     *                 property="on_call_availability",
     *                 type="boolean",
     *                 example=true,
     *                 description="true = set as true, false = not true"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profile updated successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="test user"),
     *                 @OA\Property(property="email", type="string", example="test@example.com"),
     *                 @OA\Property(property="phone", type="string", example="9876543210"),
     *                 @OA\Property(property="country_code", type="string", example="+91")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function editProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email,' . $user->id,
            'phone'        => 'nullable|string|max:15|unique:users,phone,' . $user->id,
            'country_code' => 'nullable|string|max:5',
            'on_call_availability' =>'boolean',
            'languages' => 'nullable|string',
            'dob'      => ['required_if:role,provider', 'nullable', 'date', 'before:today'],
            'gender'   => ['required_if:role,provider', 'nullable', 'in:male,female'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user->update([
            'name'         => $request->name,
            'email'        => $request->email,
            'phone'        => $request->phone,
            'country_code' => $request->country_code,
            'dob'        => $request->dob,
            'gender' => $request->gender,
            'on_call_availability' => $request->on_call_availability??false,
        ]);

        if ($request->filled('languages')) {

            $languages = array_filter(
                array_map('trim', explode(',', $request->languages))
            );

            $languageIds = [];

            foreach ($languages as $lang) {

                $masterLanguage = Language::firstOrCreate([
                    'name' => $lang
                ]);

                $languageIds[] = $masterLanguage->id;

                $user->languages()->updateOrCreate(
                    [
                        'language_id' => $masterLanguage->id
                    ],
                    [
                        'language' => $lang
                    ]
                );
            }

            $user->languages()
                ->whereNotIn('language_id', $languageIds)
                ->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data'    => $user
        ]);
    }

    /**
     * @OA\Post(
     *     path="/upload-profile",
     *     summary="Upload profile image",
     *     description="Allows an authenticated user to upload or update their profile picture.",
     *     operationId="uploadProfileImage",
     *     tags={"Users"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"image"},
     *                 @OA\Property(property="image", type="file", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Image uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profile image uploaded successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="test user"),
     *                 @OA\Property(property="email", type="string", example="test@example.com"),
     *                 @OA\Property(property="phone", type="string", example="9876543210"),
     *                 @OA\Property(property="country_code", type="string", example="+91"),
     *                 @OA\Property(property="image", type="string", example="storage/profile_images/user1.jpg")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed"
     *     )
     * )
     */
    public function uploadProfileImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,jpeg,png'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $user = User::find($user->id);
        if ($user->image && Storage::disk('s3')->exists($user->image)) {
            Storage::disk('s3')->delete($user->image);
        }

        $path = $request->file('image')->store('profile_images', 's3');

        $user->image = $path;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile image uploaded successfully.',
            'data' => $user
        ]);
    }
    public function uploadProfileImage_old(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,jpeg,png'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $folderPath = 'media/userid' . $user->id;

        // Get or create media row for this user
        $media = Media::firstOrCreate(['user_id' => $user->id]);

        // Delete old profile photo if exists
        if ($media->profile_photo && Storage::disk('s3')->exists($media->profile_photo)) {
            Storage::disk('s3')->delete($media->profile_photo);
        }

        // Store new profile photo
        $path = $request->file('image')->store($folderPath, 's3');

        // Update media table
        $media->profile_photo = $path;
        $media->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile image uploaded successfully.',
            'data' => $media
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/delete-profile-image",
     *     summary="Delete the user's profile image",
     *     tags={"Users"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Profile image deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No profile image to delete",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function deleteProfileImage(Request $request)
    {
        $user = $request->user();

        if (!$user->image) {
            return response()->json([
                'success' => false,
                'message' => 'No profile image to delete.',
            ], 404);
        }
        if(!empty($user->image)){
            $image = str_replace('storage','',$user->image);
            $url =  Storage::disk('s3')->delete($image);
        }
        $user->image = null;
        $user->save();

        return response()->json([
            'success' => true ,
            'message' => 'Profile image deleted successfully.',
        ]);
    }

    public function deleteProfileImage_old(Request $request)
    {
        $user = $request->user();

        $media = Media::where('user_id', $user->id)->first();

        if (!$media || !$media->profile_photo) {
            return response()->json([
                'success' => false,
                'message' => 'No profile image to delete.',
            ], 404);
        }

        // Delete file if exists
        if (Storage::disk('s3')->exists($media->profile_photo)) {
            Storage::disk('s3')->delete($media->profile_photo);
        }

        // Remove path from media table
        $media->profile_photo = null;
        $media->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile image deleted successfully.',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/user/update-availability",
     *     summary="Update User Availability Status",
     *     tags={"Provider"},
     *     security={{"bearerAuth":{}}},
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"availability_status"},
     *             @OA\Property(
     *                 property="availability_status",
     *                 type="integer",
     *                 example=1,
     *                 description="0 = Not Available, 1 = Available, 2 = Busy"
     *             )
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Availability status updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="availability_status", type="integer", example=1)
     *             )
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error"
     *     ),
     * 
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function updateAvailability(Request $request)
    {
        $request->validate([
            'availability_status' => 'required|in:0,1,2',
        ]);

        $user = $request->user();
        $user->availability_status = $request->availability_status;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Availability status updated successfully',
            'data' => [
                'availability_status' => $user->availability_status
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/user/get-availability",
     *     summary="Get User Availability Status",
     *     tags={"Provider"},
     *     security={{"bearerAuth":{}}},
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Availability status fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Availability status fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="availability_status", type="integer", example=1, description="0 = Not Available, 1 = Available, 2 = Busy")
     *             )
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function getAvailability(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'status' => true,
            'message' => 'Availability status fetched successfully',
            'data' => [
                'availability_status' => $user->availability_status
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/user/update-location",
     *     summary="Update User Location (Latitude & Longitude)",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"latitude","longitude"},
     *             
     *             @OA\Property(
     *                 property="latitude",
     *                 type="number",
     *                 format="float",
     *                 example=30.7046,
     *                 description="Latitude value between -90 to 90"
     *             ),
     * 
     *             @OA\Property(
     *                 property="longitude",
     *                 type="number",
     *                 format="float",
     *                 example=76.7179,
     *                 description="Longitude value between -180 to 180"
     *             )
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Location updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Location updated successfully"),
     *             
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 
     *                 @OA\Property(property="latitude", type="number", format="float", example=30.7046),
     *                 @OA\Property(property="longitude", type="number", format="float", example=76.7179)
     *             )
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error"
     *     ),
     * 
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $user = $request->user();
        $user = User::find($user->id);
        // if (!$user->updated_at || now()->diffInSeconds($user->updated_at,true) > 20) {
        $user->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);
        $user->refresh(); 
    
        // Broadcast real-time
        broadcast(new LocationUpdated(
            $user->id,
            $request->latitude,
            $request->longitude
        ))->toOthers();

        return response()->json([
            'message' => 'Location updated successfully',
            'data' => [
                'latitude' => $user->latitude,
                'longitude' => $user->longitude,
            ],
            //'user' => $user
        ]);
    }

    /**
     * @OA\Get(
     *     path="/user/availability-slots",
     *     tags={"Provider"},
     *     summary="Get Availability Slots",
     *     description="Fetch authenticated user's availability slots using Sanctum Bearer Token.",
     *     operationId="getAvailabilitySlots",
     *
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Availability slots fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Availability slots fetched successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="day", type="string", example="Monday"),
     *                     @OA\Property(property="opening_time", type="string", example="09:00:00"),
     *                     @OA\Property(property="closing_time", type="string", example="18:00:00"),
     *                     @OA\Property(property="status", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function getAvailabilitySlots(Request $request)
    {
        $user = $request->user();

        $slots = AvailabilitySlot::with('user')->where('user_id', $user->id)
                    ->orderByRaw("FIELD(day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")
                    ->get();

        return response()->json([
            'success' => true,
            'message' => 'Availability slots fetched successfully.',
            'data'    => $slots
        ]);
    }

    /**
     * @OA\Get(
     *     path="/user/bank-details",
     *     tags={"Provider"},
     *     summary="Get Bank Details",
     *     description="Fetch authenticated user's bank details.",
     *     operationId="getBankDetails",
     *
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Bank details fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Bank details fetched successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="account_holder_name", type="string", example="John Doe"),
     *                 @OA\Property(property="bank_name", type="string", example="HDFC Bank"),
     *                 @OA\Property(property="account_number", type="string", example="XXXXXX1234"),
     *                 @OA\Property(property="ifsc_code", type="string", example="HDFC0001234"),
     *                 @OA\Property(property="account_type", type="string", example="savings")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Bank details not found"
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function getBankDetails(Request $request)
    {
        $bank = BankDetail::where('user_id', $request->user()->id)->first();

        if (!$bank) {
            return response()->json([
                'success' => false,
                'message' => 'Bank details not found.',
                'data'    => null
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Bank details fetched successfully.',
            'data'    => $bank
        ]);
    }

    /**
     * @OA\Get(
     *     path="/user/media",
     *     tags={"Provider"},
     *     summary="Get User Media",
     *     description="Fetch authenticated user's media details including certificates.",
     *     operationId="getUserMedia",
     *
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Media fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Media fetched successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="identity_type_id", type="integer", example=1),
     *                 @OA\Property(property="id_front", type="string", example="storage/media/id_front.jpg"),
     *                 @OA\Property(property="id_back", type="string", example="storage/media/id_back.jpg"),
     *                 @OA\Property(property="profile_photo", type="string", example="storage/media/profile.jpg"),
     *                 @OA\Property(
     *                     property="certificates",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="original_name", type="string", example="certificate.pdf"),
     *                         @OA\Property(property="path", type="string", example="storage/media/cert.pdf"),
     *                         @OA\Property(property="mime_type", type="string", example="application/pdf"),
     *                         @OA\Property(property="size", type="integer", example=204800)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Media not found"
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function getMedia(Request $request)
    {
        $media = Media::with(['certificates'])
                    ->where('user_id', $request->user()->id)
                    ->first();

        if (!$media) {
            return response()->json([
                'success' => false,
                'message' => 'Media not found.',
                'data'    => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Media fetched successfully.',
            'data'    => new MediaResource($media)
        ]);
    }


    /**
     * @OA\Get(
     *     path="/user/media-v2",
     *     tags={"Provider"},
     *     summary="Get User Media",
     *     description="Fetch authenticated user's media details including certificates.",
     *     operationId="getUserMediaV2",
     *
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Media fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Media fetched successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="identity_type_id", type="integer", example=1),
     *                 @OA\Property(property="id_front", type="string", example="storage/media/id_front.jpg"),
     *                 @OA\Property(property="id_back", type="string", example="storage/media/id_back.jpg"),
     *                 @OA\Property(property="profile_photo", type="string", example="storage/media/profile.jpg"),
     *                 @OA\Property(
     *                     property="certificates",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="original_name", type="string", example="certificate.pdf"),
     *                         @OA\Property(property="path", type="string", example="storage/media/cert.pdf"),
     *                         @OA\Property(property="mime_type", type="string", example="application/pdf"),
     *                         @OA\Property(property="size", type="integer", example=204800)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Media not found"
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function getMediaV2(Request $request)
    {

        $user = $request->user();
        $user  = User::find($user->id);
        $media = Media::with(['identityType','files'])
                    ->where('user_id', $request->user()->id)
                    ->get();
        if (!$media) {
            return response()->json([
                'success' => false,
                'message' => 'Media not found.',
                'data'    => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Media fetched successfully.',
            'data'    =>[
                'multiple_proofs' =>  $media,
                'user' =>  $user
            ]
        ]);
        // $userId = $request->user()->id;

        // $identityProofs = Media::with(['identityType', 'files'])
        //     ->where('user_id', $userId)
        //     ->whereNotNull('identity_type_id')
        //     ->get()
        //     ->map(function ($media) {
        //         return [
        //             'identity_type_id' => $media->identity_type_id,
        //             'identity_name' => $media->identityType->name ?? 'Unknown ID Type',
        //             'documents' => $media->files->map(function($file) {
        //                 return [
        //                     'id' => $file->id,
        //                     'url' => asset('storage/' . $file->path), 
        //                     'name' => $file->name ?? 'document.png'
        //                 ];
        //             })->toArray()
        //         ];
        //     });

        // $globalMedia = Media::with(['certificates'])
        //     ->where('user_id', $userId)
        //     ->whereNull('identity_type_id')
        //     ->first();

        // $profilePhotoUrl = null;
        // $certificatesArray = [];

        // if ($globalMedia) {
        //     if ($globalMedia->profile_photo) {
        //         $profilePhotoUrl = asset($globalMedia->profile_photo);
        //     }
            
        //     $certificatesArray = $globalMedia->certificates->map(function($cert) {
        //         return [
        //             'id' => $cert->id,
        //             'url' => asset('storage/' . $cert->path),
        //             'name' => $cert->name ?? 'certificate.png'
        //         ];
        //     })->toArray();
        // }

        // return response()->json([
        //     'success' => true,
        //     'message' => 'Media assets retrieved successfully.',
        //     'data' => [
        //         'multiple_proofs' => $identityProofs, // Maps directly to step-three.tsx state hook
        //         'profile_photo' => $profilePhotoUrl,
        //         'certificates' => $certificatesArray
        //     ]
        // ]);
    }

    /**
     * @OA\Get(
     *     path="/user/professional-details",
     *     tags={"Provider"},
     *     summary="Get Professional Detail",
     *     description="Fetch authenticated user's professional details with active services and add-on services.",
     *     operationId="getProfessionalDetail",
     *
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Professional detail fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Professional detail fetched successfully."),
     *             
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *
     *                 @OA\Property(
     *                     property="professional_detail",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="experience", type="string", example="5 years"),
     *                     @OA\Property(property="bio", type="string", example="Experienced provider")
     *                 ),
     *
     *                 @OA\Property(
     *                     property="services",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Hair Cut"),
     *                         @OA\Property(property="price", type="number", example=200),
     *                         @OA\Property(property="status", type="integer", example=1),
     *
     *                         @OA\Property(
     *                             property="add_on_services",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=10),
     *                                 @OA\Property(property="name", type="string", example="Beard Trim"),
     *                                 @OA\Property(property="price", type="number", example=50)
     *                             )
     *                         )
     *                     )
     *                 ),
     *
     *                 @OA\Property(
     *                     property="add_on_services",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=101),
     *                         @OA\Property(property="name", type="string", example="Head Massage"),
     *                         @OA\Property(property="price", type="number", example=150)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Professional detail not found"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function getProfessionalDetail(Request $request)
    {
        $user = $request->user();

        $user->load([
            'professionalDetail',
            'professionalDetail.serviceCategory',
            'professionalDetail.serviceUseCases.serviceUseCase',
            'professionalDetail.licenseTypes.licenseType',
            'professionalDetail.providerMaterials.materialType',
            'services' => function ($query) {
                $query
                    ->with([
                        'service',
                    ]);
            },
            'addOnServices',
            'languages'
        ]);

        if (!$user->professionalDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Professional detail not found.',
                'data'    => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Professional detail fetched successfully.',
            'data'    => [
                'professional_detail' => $user->professionalDetail,
                'services' => $this->appendAcademicClasses($user->services),
                'add_on_services'     => $user->addOnServices,
                'languages'            => $user->languages,
                //'service_use_cases'    => $user->serviceUseCases,
                //'license_types'        => $user->licenseTypes
            ]
        ]);
    }

    // private function appendAcademicClasses($services)
    // {
    //     return $services->map(function ($service) use ($services) {

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Academic Subject
    //         |--------------------------------------------------------------------------
    //         */

    //         if ($service->subject_type == 1) {

    //             $classes = $services
    //                 ->where('service_id', $service->service_id)
    //                 ->where('subject_type', 1)
    //                 ->map(function ($item) {

    //                     return [
    //                         'id' => $item->id,
    //                         'class_name' => $item->class_name,
    //                         'price' => $item->price,
    //                     ];
    //                 })
    //                 ->values();

    //             $service->classes = $classes;
    //         }

    //         if ($service->service_category_id == 1) {

    //             $items = $services
    //                 ->where('service_id', $service->service_id)
    //                 //->whereIn('subject_type', $service->type)
    //                 ->map(function ($item) {

    //                     return [
    //                         'id' => $item->service_item_id,
    //                         'name' => $item->name,
    //                         'price' => $item->price,
    //                     ];
    //                 })
    //                 ->values();

    //             $service->items = $items;
    //         }

    //         if ($service->service_category_id == 2) {

    //             $items = $services
    //                 ->where('service_id', $service->service_id)
    //                 ->whereIn('subject_type',[3,4,5])
    //                 ->map(function ($item) {

    //                     return [
    //                         'id' => $item->service_item_id,
    //                         'name' => $item->name,
    //                         'price' => $item->price,
    //                     ];
    //                 })
    //                 ->values();

    //             $service->items = $items;
    //         }

    //         return $service;
    //     })->unique('service_id')->values();
    // }
    private function appendAcademicClasses($services)
    {
        return $services->map(function ($service) use ($services) {

            if ($service->service_category_id == 3 && $service->subject_type == 1) {

                $service->classes = $services
                    ->where('service_id', $service->service_id)
                    ->where('subject_type', 1)
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'class_name' => $item->class_name,
                            'price' => $item->price,
                        ];
                    })
                    ->values();
            }

            if ($service->service_category_id == 1) {

                $service->items = $services
                    ->where('service_id', $service->service_id)
                    ->where('subject_type', $service->subject_type)
                    ->map(function ($item) {
                        return [
                            'id' => $item->service_item_id,
                            'name' => $item->name,
                            'price' => $item->price,
                        ];
                    })
                    ->values();
            }

            if ($service->service_category_id == 2) {

                $service->items = $services
                    ->where('service_id', $service->service_id)
                    ->where('subject_type', $service->subject_type)
                    ->map(function ($item) {
                        return [
                            'id' => $item->service_item_id,
                            'name' => $item->name,
                            'price' => $item->price,
                        ];
                    })
                    ->values();
            }

            return $service;
        })
        ->unique(function ($item) {
            return $item->service_id . '-' . $item->subject_type;
        })
        ->values();
    }

    /**
     * @OA\Get(
     *     path="/provider/dashboard",
     *     summary="Get provider dashboard stats",
     *     security={{"bearerAuth":{}}},
     *     tags={"Provider"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard data fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="today_completed_jobs", type="integer", example=5),
     *                 @OA\Property(property="total_completed_jobs", type="integer", example=120),
     *                 @OA\Property(property="today_earnings", type="number", example=2500)
     *             )
     *         )
     *     )
     * )
     */
    public function dashboard()
    {
        $user = auth()->user();

        $today = Carbon::today();

        $todayCompletedJobs = Booking::where('provider_id', $user->id)
            ->where('status', 'completed')
            ->whereDate('updated_at', $today)
            ->count();

        $totalCompletedJobs = Booking::where('provider_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $todayEarnings = Booking::where('provider_id', $user->id)
            ->where('status', 'completed')
            ->whereDate('updated_at', $today)
            ->sum('total_amount'); // adjust column if needed

        return response()->json([
            'success' => true,
            'data' => [
                'today_completed_jobs' => $todayCompletedJobs,
                'total_completed_jobs' => $totalCompletedJobs,
                'today_earnings' => $todayEarnings,
            ]
        ]);
    }

   /**
     * @OA\Get(
     *     path="/provider/earnings",
     *     summary="Get provider earnings report",
     *     security={{"bearerAuth":{}}},
     *     tags={"Provider"},
     *
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=true,
     *         description="Report type",
     *         @OA\Schema(
     *             type="string",
     *             enum={"last_30_days","this_month","last_3_months","six_month","this_year"}
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Earnings fetched successfully"
     *     )
     * )
     */
    public function earnings(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'type' => 'required|in:last_30_days,this_month,last_3_months,six_month,this_year'
        ]);
        $type = $request->type;

        $query = Booking::where('provider_id', $user->id)
            ->where('status', 'completed');
        /**
         * DAY WISE (Current Month Days)
         */
        if ($type == 'day') {

            $data = $query
                ->select(
                    DB::raw('DAY(updated_at) as label'),
                    DB::raw('SUM(total_amount) as earnings')
                )
                ->whereMonth('updated_at', Carbon::now()->month)
                ->whereYear('updated_at', Carbon::now()->year)
                ->groupBy('label')
                ->orderBy('label')
                ->get();
        }
        /**
         * DAY WISE (Last 30 Days)
         */
        elseif ($type == 'last_30_days') {
            $earnings = $query
                ->select(
                    DB::raw('DATE(updated_at) as date'),
                    DB::raw('SUM(total_amount) as earnings')
                )
                ->whereDate('updated_at', '>=', now()->subDays(29)->toDateString())
                ->whereDate('updated_at', '<=', now()->toDateString())
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('earnings', 'date');

            $period = CarbonPeriod::create(
                now()->subDays(29),
                now()
            );
    
            $data = collect($period)->map(function ($date) use ($earnings) {
                $dateString = $date->format('Y-m-d');

                return [
                    'label' => $date->format('d M'),
                    'earnings' => $earnings[$dateString] ?? 0, // or null for blank
                ];
            })->values();
        }
        /**
         * MONTH WISE (Current Year)
         */
        elseif ($type == 'this_month') {
            // Fetch earnings grouped by date
            $earnings = $query
                ->select(
                    DB::raw('DATE(updated_at) as date'),
                    DB::raw('SUM(total_amount) as earnings')
                )
                ->whereYear('updated_at', now()->year)
                ->whereMonth('updated_at', now()->month)
                ->groupBy('date')
                ->pluck('earnings', 'date');

            // Generate all dates from 1st of month to today
            $period = CarbonPeriod::create(
                now()->startOfMonth(),
                now()
            );

            $data = collect($period)->map(function ($date) use ($earnings) {
                $dateString = $date->format('Y-m-d');

                return [
                    'label' => $date->format('d'),
                    'earnings' => $earnings[$dateString] ?? 0, // Use null instead of 0 if you want blank
                ];
            })->values();
        }

        /**
         * LAST 3 MONTHS
         */
        elseif ($type == 'last_3_months') {

            $startDate = Carbon::now()->subMonths(2)->startOfMonth();

            $data = $query
                ->select(
                    DB::raw('MONTH(updated_at) as month'),
                    DB::raw('YEAR(updated_at) as year'),
                    DB::raw('SUM(total_amount) as earnings')
                )
                ->whereDate('updated_at', '>=', $startDate)
                ->groupBy('month', 'year')
                ->orderBy('year')
                ->orderBy('month')
                ->get()
                ->map(function ($item) {

                    $item->label = Carbon::create(
                        $item->year,
                        $item->month,
                        1
                    )->format('F Y');

                    return [
                        'label' => $item->label,
                        'earnings' => $item->earnings,
                    ];
                });
        }

        /**
         * LAST 6 MONTHS
         */
        elseif ($type == 'six_month') {

            $startDate = Carbon::now()->subMonths(5)->startOfMonth();

            $data = $query
                ->select(
                    DB::raw('MONTH(updated_at) as month'),
                    DB::raw('YEAR(updated_at) as year'),
                    DB::raw('SUM(total_amount) as earnings')
                )
                ->whereDate('updated_at', '>=', $startDate)
                ->groupBy('month', 'year')
                ->orderBy('year')
                ->orderBy('month')
                ->get()
                ->map(function ($item) {

                    $item->label = Carbon::create(
                        $item->year,
                        $item->month,
                        1
                    )->format('F Y');

                    return [
                        'label' => $item->label,
                        'earnings' => $item->earnings,
                    ];
                });
        }

        /**
         * YEAR WISE
         */
        else {

            $data = $query
                ->select(
                    DB::raw('YEAR(updated_at) as label'),
                    DB::raw('SUM(total_amount) as earnings')
                )
                ->groupBy('label')
                ->orderBy('label')
                ->get();
        }

        return response()->json([
            'success' => true,
            'type' => $type,
            'data' => $data
        ]);
    }

    /**
     * @OA\Get(
     *     path="/provider/available-slots",
     *     summary="Get provider available slots (with booked slots)",
     *     description="Fetch provider working hours along with already booked slots for a specific date. Used to prevent double booking.",
     *     operationId="getAvailableSlots",
     *     security={{"bearerAuth":{}}},
     *     tags={"Provider"},
     *
     *     @OA\Parameter(
     *         name="provider_id",
     *         in="query",
     *         required=true,
     *         description="ID of the provider",
     *         @OA\Schema(type="integer", example=43)
     *     ),
     *
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         required=true,
     *         description="Selected date for booking (YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date", example="2026-04-25")
     *     ),
     *
     *     @OA\Parameter(
     *         name="slot_duration",
     *         in="query",
     *         required=false,
     *         description="Slot duration in minutes (optional, default 60)",
     *         @OA\Schema(type="integer", example=60)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Slots fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *
     *             @OA\Property(
     *                 property="working_hours",
     *                 type="object",
     *                 @OA\Property(property="start", type="string", example="11:00"),
     *                 @OA\Property(property="end", type="string", example="20:00")
     *             ),
     *
     *             @OA\Property(
     *                 property="booked_slots",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="start", type="string", example="10:00"),
     *                     @OA\Property(property="end", type="string", example="12:00")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Provider not available on selected date"
     *     )
     * )
     */
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'provider_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'slot_duration' => 'nullable|integer' // minutes (default 60)
        ]);

        $providerId = $request->provider_id;
        $date = Carbon::parse($request->date);
        $day = strtolower($date->format('l')); // monday, tuesday...
        $slotDuration = (int) $request->slot_duration ?? 60;

        // 1. Get provider availability
        $availability = AvailabilitySlot::where('user_id', $providerId)
            ->where('day', $day)
            ->where('status', 1)
            ->first();

        if (!$availability) {
            return response()->json([
                'status' => false,
                'message' => 'Provider not available on this day'
            ]);
        }

        // 2. Generate slots
        $slots = [];
        $start = Carbon::parse($availability->opening_time);
        $end = Carbon::parse($availability->closing_time);

        while ($start->copy()->addMinutes($slotDuration) <= $end) {
            $slotStart = $start->copy();
            $slotEnd = $start->copy()->addMinutes($slotDuration);

            $slots[] = [
                'start' => $slotStart->format('H:i'),
                'end' => $slotEnd->format('H:i'),
            ];

            $start->addMinutes($slotDuration);
        }

        // 3. Get booked slots (one-time)
        $bookings = Booking::where('provider_id', $providerId)
            ->whereDate('start_datetime', $date)
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->get();

        // 4. Remove booked slots
        $availableSlots = [];

        foreach ($slots as $slot) {

            $slotStart = Carbon::parse($date->format('Y-m-d') . ' ' . $slot['start']);
            $slotEnd = Carbon::parse($date->format('Y-m-d') . ' ' . $slot['end']);

            $isBooked = false;

            foreach ($bookings as $booking) {

                $bookingStart = Carbon::parse($booking->start_datetime);
                $bookingEnd = Carbon::parse($booking->end_datetime);

                // overlap check
                if ($slotStart < $bookingEnd && $slotEnd > $bookingStart) {
                    $isBooked = true;
                    break;
                }
            }

            if (!$isBooked) {
                $availableSlots[] = $slot;
            }
        }

        return response()->json([
            'status' => true,
            'data' => $availableSlots
        ]);
    }

    /**
     * @OA\Get(
     *     path="/provider/booked-slots",
     *     summary="Get provider booked slots",
     *     description="Fetch provider working hours along with booked and recurring blocked slots for a given date.",
     *     operationId="getBookedSlots",
     *     security={{"bearerAuth":{}}},
     *     tags={"Provider"},
     *
     *     @OA\Parameter(
     *         name="provider_id",
     *         in="query",
     *         required=true,
     *         description="ID of the provider",
     *         @OA\Schema(type="integer", example=43)
     *     ),
     *
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         required=true,
     *         description="Date to check booked slots (YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date", example="2026-04-25")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Booked slots fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *
     *             @OA\Property(
     *                 property="working_hours",
     *                 type="object",
     *                 @OA\Property(property="start", type="string", example="11:00"),
     *                 @OA\Property(property="end", type="string", example="20:00")
     *             ),
     *
     *             @OA\Property(
     *                 property="booked_slots",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="start", type="string", example="10:00"),
     *                     @OA\Property(property="end", type="string", example="12:00")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Validation error (missing or invalid parameters)"
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Provider not available on selected day"
     *     )
     * )
     */
    /*public function getBookedSlots(Request $request)
    {
        $request->validate([
            'provider_id' => 'required|exists:users,id',
            'date' => 'required|date',
        ]);

        $providerId = $request->provider_id;
        $date = \Carbon\Carbon::parse($request->date);
        $day = strtolower($date->format('l'));

        // 1. Get working hours
        $availability = \App\Models\AvailabilitySlot::where('user_id', $providerId)
            ->where('day', $day)
            ->where('status', 1)
            ->first();

        if (!$availability) {
            return response()->json([
                'status' => false,
                'message' => 'Provider not available'
            ]);
        }

        // 2. One-time bookings
        $bookings = \App\Models\Booking::where('provider_id', $providerId)
            ->whereDate('start_datetime', $date)
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->get()
            ->map(function ($booking) {
                return [
                    'start' => \Carbon\Carbon::parse($booking->start_datetime)->format('H:i'),
                    'end' => \Carbon\Carbon::parse($booking->end_datetime)->format('H:i'),
                ];
            });

        // 3. Recurring bookings (if exists)
        $recurringBookings = \App\Models\Booking::where('provider_id', $providerId)
            ->where('is_recurring', 1)
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->get()
            ->filter(function ($booking) use ($day) {
                $days = json_decode($booking->recurring_days, true);
                return in_array($day, $days);
            })
            ->map(function ($booking) use ($date) {
                return [
                    'start' => \Carbon\Carbon::parse($date->format('Y-m-d') . ' ' . $booking->recurring_start_time)->format('H:i'),
                    'end' => \Carbon\Carbon::parse($date->format('Y-m-d') . ' ' . $booking->recurring_end_time)->format('H:i'),
                ];
            });

        return response()->json([
            'status' => true,
            'working_hours' => [
                'start' => $availability->opening_time,
                'end' => $availability->closing_time,
            ],
            'booked_slots' => $bookings->merge($recurringBookings)->values()
        ]);
    }*/
    public function getBookedSlots(Request $request)
    {
        $request->validate([
            'provider_id' => 'required|exists:users,id',
            'date' => 'required|date',
        ]);

        $providerId = $request->provider_id;
        $startDate = \Carbon\Carbon::parse($request->date);

        $result = [];

        // Get all recurring bookings once
        $recurringBookings = \App\Models\Booking::where('provider_id', $providerId)
            ->where('is_recurring', 1)
            ->whereIn('status', [
                'confirmed',
                'start_journey',
                'in_progress'
            ])
            ->get();

        // Get all bookings for next 30 days once
        $allBookings = \App\Models\Booking::where('provider_id', $providerId)
            ->whereBetween('start_datetime', [
                $startDate->copy()->startOfDay(),
                $startDate->copy()->addDays(29)->endOfDay(),
            ])
            ->whereIn('status', [
                'confirmed',
                'start_journey',
                'in_progress'
            ])
            ->get();

        for ($i = 0; $i < 30; $i++) {

            $date = $startDate->copy()->addDays($i);
            $day = strtolower($date->format('l'));

            // Provider availability for this day
            $availability = \App\Models\AvailabilitySlot::where('user_id', $providerId)
                ->where('day', $day)
                ->where('status', 1)
                ->first();

            if (!$availability) {
                $result[] = [
                    'date' => $date->toDateString(),
                    'day' => ucfirst($day),
                    'available' => false,
                    'working_hours' => null,
                    'booked_slots' => [],
                ];

                continue;
            }

            // One-time bookings for current date
            $bookings = $allBookings
                ->filter(function ($booking) use ($date) {
                    return \Carbon\Carbon::parse($booking->start_datetime)
                        ->toDateString() === $date->toDateString();
                })
                ->map(function ($booking) {
                    return [
                        'booking_id' => $booking->id,
                        'start' => \Carbon\Carbon::parse($booking->start_datetime)->format('H:i'),
                        'end' => \Carbon\Carbon::parse($booking->end_datetime)->format('H:i'),
                        'status' => $booking->status,
                    ];
                })
                ->values();

            // Recurring bookings for current day
            $dayRecurringBookings = $recurringBookings
                ->filter(function ($booking) use ($day) {
                    $days = json_decode($booking->recurring_days, true) ?? [];
                    return in_array($day, $days);
                })
                ->map(function ($booking) use ($date) {
                    return [
                        'booking_id' => $booking->id,
                        'start' => \Carbon\Carbon::parse(
                            $date->format('Y-m-d') . ' ' . $booking->recurring_start_time
                        )->format('H:i'),
                        'end' => \Carbon\Carbon::parse(
                            $date->format('Y-m-d') . ' ' . $booking->recurring_end_time
                        )->format('H:i'),
                        'status' => $booking->status,
                    ];
                })
                ->values();

            $result[] = [
                'date' => $date->toDateString(),
                'day' => ucfirst($day),
                'available' => true,
                'working_hours' => [
                    'start' => $availability->opening_time,
                    'end' => $availability->closing_time,
                ],
                'booked_slots' => $bookings
                    ->merge($dayRecurringBookings)
                    ->sortBy('start')
                    ->values(),
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Booked slots fetched successfully',
            'total_days' => 30,
            'data' => $result,
        ]);
    }


    /**
     * @OA\Post(
     *     path="/provider/background-check",
     *     summary="Check authenticated user's account",
     *     security={{ "bearerAuth": {} }},
     *     tags={"Provider"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="integer", example="alex.morgan@example.com"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Backgound checking successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Backgound checking successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found.")
     *         )
     *     )
     * )
     */
    public function backgroundCheck(Request $request)
    {
        $request->validate([
            // 'email' => 'required|exists:users,email',
            'email' => 'required' 
        ]);
        $user = User::where('email', $request->email)->first();
        $response = Http::withHeaders([
            'X-API-Key' => env('CREDIBLED_API_KEY'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post(
            env('CREDIBLED_API_URL') . '/checks/',
            [
                'check_category' => 'background',
                'email' => $request->email,
                'check_types' => [
                    env('CANADIAN_CRIMINAL_CHECK_UUID')
                ],
                "send_email" => true
            ]
        );
        
        if ($response->successful()) {
            $json = $response->json();
            $user->bg_uuid = $json['uuid'];
            $user->save();
            return response()->json([
                'success' => true,
                'data' => $response->json(),
            ]);
        }

        $body = json_decode($response->body());
        return response()->json([
            'success' => false,
            'status' => $response->status(),
            'message' => $body->message,
        ], $response->status());
    }

    /**
     * @OA\Get(
     *     path="/provider/background-check-link",
     *     summary="Check Background Link",
     *     security={{ "bearerAuth": {} }},
     *     tags={"Provider"},
     *     @OA\Response(
     *         response=200,
     *         description="Backgound checked link generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="application_url", type="string", example="https://whitelabel.certn.co/welcome/email?session=68c97ac0-5958-4da0-92f4-7e6998c1c304&token=7ad9584c-878f-45d6-bedb-54dc9dce89f4&onboardingType=HR&inviteRoute=email"),
     *             @OA\Property(property="message", type="string", example="Backgound checked link generated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found.")
     *         )
     *     )
     * )
     */
    public function backgroundCheckLink(Request $request)
    {
        $user = auth()->user();
        if($user->bg_uuid){
            $backgroundCheckId = $user->bg_uuid;
            $response = Http::withHeaders([
                'X-API-Key' => env('CREDIBLED_API_KEY'),
            ])->get(
                env('CREDIBLED_API_URL').'/background-checks/' .
                $backgroundCheckId .
                '/application-link/'
            );
            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json(),
                ]);
            }

            // $body = json_decode($response->body());
            return response()->json([
                'success' => false,
                'status' => $response->status(),
                'message' => $response->body(),
            ], $response->status());
        }else{
            //Authenticate
            $response = Http::withHeaders([
                'X-API-Key' => env('CREDIBLED_API_KEY'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post(
                env('CREDIBLED_API_URL') . '/checks/',
                [
                    'check_category' => 'background',
                    'email' => $request->email,
                    'check_types' => [
                        env('CANADIAN_CRIMINAL_CHECK_UUID')
                    ],
                    "send_email" => true
                ]
            );
            
            if ($response->successful()) {
                $json = $response->json();
                $user->bg_uuid = $json['uuid'];
                $user->save();
                return response()->json([
                    'success' => true,
                    'data' => $response->json(),
                ]);
            }

            $body = json_decode($response->body());
            return response()->json([
                'success' => false,
                'status' => $response->status(),
                'message' => $body->message,
            ], $response->status());
        }
        // print_r($user); return;
    }

    /**
     * @OA\Post(
     *     path="/provider/background-webhook",
     *     summary="Check Background Link",
     *     security={{ "bearerAuth": {} }},
     *     tags={"Provider"},
     *      @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="uuid", type="string", format="text", example="a1b2c3d4-e5f6-7890-abcd-ef1234567890"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Backgound hook generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="application_url", type="string", example="https://whitelabel.certn.co/welcome/email?session=68c97ac0-5958-4da0-92f4-7e6998c1c304&token=7ad9584c-878f-45d6-bedb-54dc9dce89f4&onboardingType=HR&inviteRoute=email"),
     *             @OA\Property(property="message", type="string", example="Backgound checked link generated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found.")
     *         )
     *     )
     * )
     */
    public function backgroundWebHook(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Verify HMAC Signature
        |--------------------------------------------------------------------------
        */

        $signature = $request->header('X-HMAC-Signature');

        if (! $signature) {
            return response()->json([
                'success' => false,
                'message' => 'Missing webhook signature.',
            ], 401);
        }

        $payload = $request->getContent();

        $expectedSignature = hash_hmac(
            'sha256',
            $payload,
            config('services.background_check.webhook_secret')
        );
        if (! hash_equals($expectedSignature, $signature)) {

            Log::warning('Invalid background check webhook signature.', [
                'signature' => $signature,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid webhook signature.',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Payload
        |--------------------------------------------------------------------------
        */


        $data = $request->validate([
            'uuid' => ['required', 'uuid'],
            'email' => ['required', 'email']
        ]);

        /*
        |--------------------------------------------------------------------------
        | Find User
        |--------------------------------------------------------------------------
        */

        $user = User::where('email', $data['email'])->first();

        if (! $user) {

            Log::warning('Background check webhook user not found.', [
                'email' => $data['email'],
                'uuid' => $data['uuid'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }


        if($request->uuid){
            $user = User::where('email', $request->email)
                    ->where('bg_uuid', $request->uuid)
                    ->first();
            if($user){
                if(strtolower($request->application_status) == 'complete'){
                    $user->is_checked = 1;
                    $user->save();
                }
            }
        }
        foreach ($data['scan_list'] ?? [] as $scan) {

            Log::info('Background check scan updated.', [
                'user_id' => $user->id,
                'scan_id' => $scan['id'] ?? null,
                'scan_name' => $scan['scanName'] ?? null,
                'status' => $scan['application_status'] ?? null,
                'score' => $scan['score'] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Webhook processed successfully.',
        ]);
    }
}


    
