<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingConcern;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookingConcernController extends Controller
{
    /**
     * @OA\Post(
     *     path="/booking/concerns",
     *     tags={"Booking Concerns"},
     *     summary="Raise a concern for a booking",
     *     description="Allows an authenticated seeker or provider to raise a concern against the other party involved in the booking. The user_id and against_user_id are determined automatically from the authenticated user and booking.",
     *     operationId="storeBookingConcern",
     *
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="multipart/form-data",
     *                 @OA\Schema(
     *                     required={"booking_id","type","reason"},
     *
     *                     @OA\Property(
     *                         property="booking_id",
     *                         type="integer",
     *                         example=101,
     *                         description="ID of the booking for which the concern is being raised"
     *                     ),
     *
     *                     @OA\Property(
     *                         property="type",
     *                         type="string",
     *                         enum={"seeker","provider"},
     *                         example="seeker",
     *                         description="Type of user raising the concern"
     *                     ),
     *
     *                     @OA\Property(
     *                         property="reason",
     *                         type="string",
     *                         maxLength=255,
     *                         example="Service issue",
     *                         description="Reason for raising the concern"
     *                     ),
     *
     *                     @OA\Property(
     *                         property="description",
     *                         type="string",
     *                         nullable=true,
     *                         example="The provider did not respond during the scheduled consultation.",
     *                         description="Detailed description of the concern"
     *                     ),
     *
     *                     @OA\Property(
     *                         property="attachment",
     *                         type="string",
     *                         format="binary",
     *                         nullable=true,
     *                         description="Optional attachment related to the concern"
     *                     )
     *                 )
     *             )
     *         }
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Concern raised successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Concern raised successfully."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer",
     *                     example=15
     *                 ),
     *                 @OA\Property(
     *                     property="booking_id",
     *                     type="integer",
     *                     example=101
     *                 ),
     *                 @OA\Property(
     *                     property="user_id",
     *                     type="integer",
     *                     example=25
     *                 ),
     *                 @OA\Property(
     *                     property="type",
     *                     type="string",
     *                     example="seeker"
     *                 ),
     *                 @OA\Property(
     *                     property="against_user_id",
     *                     type="integer",
     *                     example=8
     *                 ),
     *                 @OA\Property(
     *                     property="reason",
     *                     type="string",
     *                     example="Service issue"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     nullable=true,
     *                     example="The provider did not respond during the scheduled consultation."
     *                 ),
     *                 @OA\Property(
     *                     property="status",
     *                     type="string",
     *                     example="pending"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Validation failed."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized booking access",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="You are not authorized to raise a concern for this booking."
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Booking not found."
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Something went wrong while raising the concern."
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => [
                'required',
                'integer',
                'exists:bookings,id',
            ],
            'type' => [
                'required',
                'string',
                'in:seeker,provider',
            ],
            'reason' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'attachment' => [
                'nullable',
                'file',
                'max:10240',
                'mimes:jpg,jpeg,png,pdf,doc,docx',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();

        $booking = Booking::find($request->booking_id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found.',
            ], 404);
        }

        /*
         * Determine who is raising the concern
         * and who the concern is against.
         */
        if ($request->type === 'seeker') {

            // Authenticated user must be the seeker
            if ((int) $booking->user_id !== (int) $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to raise a concern as a seeker for this booking.',
                ], 403);
            }

            $userId = $booking->user_id;
            $againstUserId = $booking->provider_id;

        } else {

            // Authenticated user must be the provider
            if ((int) $booking->provider_id !== (int) $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to raise a concern as a provider for this booking.',
                ], 403);
            }

            $userId = $booking->provider_id;
            $againstUserId = $booking->user_id;
        }

        if (!$againstUserId) {
            return response()->json([
                'success' => false,
                'message' => 'The other user associated with this booking could not be found.',
            ], 422);
        }

        try {
            $concern = DB::transaction(function () use (
                $request,
                $userId,
                $againstUserId
            ) {
                $concern = BookingConcern::create([
                    'booking_id' => $request->booking_id,
                    'user_id' => $userId,
                    'type' => $request->type,
                    'against_user_id' => $againstUserId,
                    'reason' => $request->reason,
                    'description' => $request->description,
                    'status' => 'pending',
                ]);

                /*
                 * Store attachment through the existing
                 * polymorphic files relationship.
                 */
                if ($request->hasFile('attachment')) {
                    $file = $request->file('attachment');

                    $path = $file->store(
                        'booking-concerns',
                        'public'
                    );

                    $concern->files()->create([
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                    ]);
                }

                return $concern;
            });

            $concern->load('files');

            return response()->json([
                'success' => true,
                'message' => 'Concern raised successfully.',
                'data' => $concern,
            ], 201);

        } catch (\Throwable $e) {

            \Log::error('Booking concern creation failed', [
                'booking_id' => $request->booking_id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while raising the concern.',
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/booking/concerns/list",
     *     tags={"Booking Concerns"},
     *     summary="Get my booking concerns",
     *     description="Get all booking concerns raised by the authenticated user.",
     *     operationId="getMyBookingConcerns",
     *
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of records per page",
     *         @OA\Schema(
     *             type="integer",
     *             example=10
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Booking concerns retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Booking concerns retrieved successfully."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="current_page",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="per_page",
     *                     type="integer",
     *                     example=10
     *                 ),
     *                 @OA\Property(
     *                     property="total",
     *                     type="integer",
     *                     example=2
     *                 ),
     *                 @OA\Property(
     *                     property="last_page",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(
     *                             property="id",
     *                             type="integer",
     *                             example=15
     *                         ),
     *                         @OA\Property(
     *                             property="booking_id",
     *                             type="integer",
     *                             example=101
     *                         ),
     *                         @OA\Property(
     *                             property="user_id",
     *                             type="integer",
     *                             example=25
     *                         ),
     *                         @OA\Property(
     *                             property="type",
     *                             type="string",
     *                             example="seeker"
     *                         ),
     *                         @OA\Property(
     *                             property="against_user_id",
     *                             type="integer",
     *                             example=8
     *                         ),
     *                         @OA\Property(
     *                             property="reason",
     *                             type="string",
     *                             example="Service issue"
     *                         ),
     *                         @OA\Property(
     *                             property="description",
     *                             type="string",
     *                             example="The provider did not respond during the consultation."
     *                         ),
     *                         @OA\Property(
     *                             property="status",
     *                             type="string",
     *                             example="pending"
     *                         ),
     *                         @OA\Property(
     *                             property="admin_response",
     *                             type="string",
     *                             nullable=true,
     *                             example=null
     *                         ),
     *                         @OA\Property(
     *                             property="resolved_at",
     *                             type="string",
     *                             format="date-time",
     *                             nullable=true,
     *                             example=null
     *                         ),
     *                         @OA\Property(
     *                             property="created_at",
     *                             type="string",
     *                             format="date-time",
     *                             example="2026-08-19T10:30:00.000000Z"
     *                         ),
     *                         @OA\Property(
     *                             property="updated_at",
     *                             type="string",
     *                             format="date-time",
     *                             example="2026-08-19T10:30:00.000000Z"
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthenticated."
     *             )
     *         )
     *     )
     * )
     */
    public function list(Request $request)
    {
        $perPage = min(
            max((int) $request->get('per_page', 10), 1),
            100
        );

        $concerns = BookingConcern::where('user_id', auth()->id())
            ->with([
                'booking',
                'files',
            ])
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Booking concerns retrieved successfully.',
            'data' => $concerns,
        ]);
    }
}