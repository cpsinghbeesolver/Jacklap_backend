<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ServiceCategory;

class ReviewController extends Controller
{

    /**
     * @OA\Post(
     *     path="/submit-review",
     *     summary="Add type of user. 'seeker','provider','service'. In user_id add service_id only in case of service. ",
     *     tags={"Reviews"},
     *     security={{"bearerAuth":{}}},
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id","type"},
     *             @OA\Property(
     *                 property="user_id",
     *                 type="integer",
     *                 example=4,
     *                 description="0 = Not Available, 1 = Available, 2 = Busy"
     *             ),
     *             @OA\Property(
     *                 property="booking_id",
     *                 type="integer",
     *                 example=4,
     *                 description="0 = Not Available, 1 = Available, 2 = Busy"
     *             ),
     *              @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 example="seeker"
     *             ),
     *             @OA\Property(
     *                 property="notes",
     *                 type="string",
     *                 example="Awesome service! I highly recommend it!!"
     *             ),
     *              @OA\Property(
     *                 property="rating",
     *                 type="integer",
     *                 example=4,
     *                 description="0 = Not Available, 1 = Available, 2 = Busy"
     *             ),
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Review added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Review added successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
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

    public function submitReview(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'type' => 'required|in:seeker,provider,service',
        ]);

        $author = auth()->user();
        
        $review = $request->notes ?? '';

        $booking = \App\Models\Booking::find($request->booking_id);
        if(!$booking){
            return response()->json([
                'status' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        if($author->hasRole('provider')){
            $user = User::find($request->user_id);
            if(!$user){
                return response()->json([
                    'status' => false,
                    'message' => 'User not found'
                ], 404);
            }
            //review to seeker
            if(!$user->hasRole('seeker')){
                return response()->json([
                    'status' => false,
                    'message' => 'Provider can only review seeker'
                ], 404);
            }
            //check if review already exists
            if($booking->is_seeker_rated){
                return response()->json([
                    'status' => false,
                    'message' => 'You have already reviewed this user'
                ], 422);
            }
            $user->review($review, $author, $request->rating);
            //update is_rated in booking table
            if($request->filled('booking_id')){
                if($booking && $booking->provider_id == $author->id){
                    $booking->is_seeker_rated = 1;
                    $booking->save();       
                }
            }
        }else if($author->hasRole('seeker')){
            if($request->type == 'provider'){
                $user = User::find($request->user_id);
                if(!$user){
                    return response()->json([
                        'status' => false,
                        'message' => 'User not found'
                    ], 404);
                }
                //seeker to provider
                if(!$user->hasRole('provider')){
                    return response()->json([
                        'status' => false,
                        'message' => 'Provider Not found'
                    ], 404);
                }
                //check if review already exists
                if($booking->is_provider_rated){
                    return response()->json([
                        'status' => false,
                        'message' => 'You have already reviewed this user'
                    ], 422);
                }
                $user->review($review, $author, $request->rating);

                //update is_rated in booking table
                if($request->filled('booking_id')){
                    $booking = \App\Models\Booking::find($request->booking_id);
                    if($booking && $booking->user_id == $author->id){
                        $booking->is_provider_rated = 1;
                        $booking->save();       
                    }
                }
            }
            else if($request->type == 'service'){
                //seeker to service
                $service = ServiceCategory::find($request->user_id);
                if ($booking->service_category_id != $request->user_id) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Service Not found'
                    ], 404);
                }
                //check if review already exists
                if($booking->is_service_rated){
                    return response()->json([
                        'status' => false,
                        'message' => 'You have already reviewed this service'
                    ], 422);
                }
                // if($service->hasReviewed($author)){  
                //     return response()->json([
                //         'status' => false,
                //         'message' => 'You have already reviewed this service'
                //     ], 422);
                // }
                $service->review($review, $author, $request->rating);
                //update is_rated in booking table
                if($request->filled('booking_id')){
                    
                    if($booking){
                        $booking->is_service_rated = 1;
                        $booking->save();       
                    }
                }
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Wrong type'
                ], 422);
            }
        }
        return response()->json([
                'success'   => true,
                'message'   => 'Review Added Successfully',
        ], 200);
        // print_r($seeker->toArray());
        
    }


    /**
     * @OA\Get(
     *     path="/get-review",
     *     tags={"Reviews"},
     *     summary="Get User Profile",
     *     description="Fetch authenticated user profile details using Sanctum Bearer Token. This is for testing purpose.",
     *     operationId="getReviews",
     *
     *     security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer", example=2),
     *         description="ID of User"
     *     ),
     *      @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", example="provider"),
     *         description=""
     *     ),
     *       @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer", example=10),
     *         description=""
     *     ),
     *      @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer", example=2),
     *         description=""
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Reivews fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Reviews fetched successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
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
    
    public function getReview(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'type' => 'required|in:seeker,provider,service',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);
        if ($request->filled('user_id')) {
            if($request->type == 'provider' || $request->type == 'seeker'){
                $model = User::find($request->user_id);
            }else if($request->type == 'service'){
                $model = ServiceCategory::find($request->user_id);
            }
        }else{
            $model = auth()->user();
        }
        if(!$model){
            return response()->json([
                'status' => false,
                'message' => "{$request->type} not found"
            ], 404);
        }

        $perPage = $request->get('per_page', 10);
        // $perPage = 10;
        $reviews = $model->reviews()
        ->latest()
        ->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'reviews' => $reviews->items(),
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
                'has_more_pages' => $reviews->hasMorePages(),
            ],
            'message' => 'Reviews fetched successfully',
        ], 200);
    }
}
