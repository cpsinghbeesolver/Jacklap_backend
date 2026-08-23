<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EnquiryController extends Controller
{

    /**
     * User enquiry list
     */
    /**
     * @OA\Get(
     *     path="/api/enquiries",
     *     tags={"Enquiries"},
     *     summary="Get user's enquiries",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", enum={0,1,2})
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=50)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Enquiries fetched successfully"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 10;

        $query = Enquiry::with([
            'items',
            'addonItems',
            'provider:id,name,email,phone,image',
            'serviceCategory:id,name',
            'address',
        ])
        ->where('user_id', auth()->id())
        ->latest();

        // Optional status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $enquiries = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Enquiries fetched successfully',
            'data' => $enquiries,
        ]);
    }

    /**
     * Provider enquiry list
     */
    /**
     * @OA\Get(
     *     path="/api/provider/enquiries",
     *     tags={"Provider Enquiries"},
     *     summary="Get provider enquiries",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", enum={0,1,2})
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=50)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Provider enquiries fetched successfully"
     *     )
     * )
     */
    public function providerIndex(Request $request)
    {
        $perPage = $request->per_page ?? 10;

        $query = Enquiry::with([
            'items',
            'addonItems',
            'user:id,name,email,phone,image',
            'serviceCategory:id,name',
            'address',
        ])
        ->where('provider_id', auth()->id())
        ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $enquiries = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Provider enquiries fetched successfully',
            'data' => $enquiries,
        ]);
    }

    /**
     * Get enquiry details using enquiry number.
     */
    /**
     * @OA\Get(
     *     path="/api/enquiries/{enquiry_number}",
     *     tags={"Enquiries"},
     *     summary="Get enquiry details",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="enquiry_number",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         example="ENQ-X8K29ABC"
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Enquiry details fetched successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Enquiry not found"
     *     )
     * )
     */
    public function show(string $enquiryNumber)
    {
        $enquiry = Enquiry::with([
            'items',
            'addonItems',

            'user:id,name,email,phone,image',

            'provider:id,name,email,phone,image',

            'serviceCategory:id,name',

            'address',
        ])
        ->where('enquiry_number', $enquiryNumber)
        ->first();

        if (!$enquiry) {
            return response()->json([
                'success' => false,
                'message' => 'Enquiry not found',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Access control
        |--------------------------------------------------------------------------
        */

        $userId = auth()->id();

        if (
            (int) $enquiry->user_id !== (int) $userId &&
            (int) $enquiry->provider_id !== (int) $userId
        ) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this enquiry',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Enquiry details fetched successfully',
            'data' => $enquiry,
        ]);
    }

    /**
     * Provider enquiry detail
     */
    /**
     * @OA\Get(
     *     path="/api/provider/enquiries/{enquiry_number}",
     *     tags={"Provider Enquiries"},
     *     summary="Get provider enquiry details",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="enquiry_number",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         example="ENQ-X8K29ABC"
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Enquiry details fetched successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Enquiry not found"
     *     )
     * )
     */
    public function providerShow(string $enquiryNumber)
    {
        $enquiry = Enquiry::with([
            'items',
            'addonItems',

            'user:id,name,email,phone,image',

            'serviceCategory:id,name',

            'address',
        ])
        ->where('enquiry_number', $enquiryNumber)
        ->where('provider_id', auth()->id())
        ->first();

        if (!$enquiry) {
            return response()->json([
                'success' => false,
                'message' => 'Enquiry not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Enquiry details fetched successfully',
            'data' => $enquiry,
        ]);
    }

    /**
     * Store a new enquiry.
     */
    /**
     * @OA\Post(
     *     path="/api/enquiries",
     *     tags={"Enquiries"},
     *     summary="Create a new enquiry",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="provider_id", type="integer", nullable=true),
     *             @OA\Property(property="service_category_id", type="integer", nullable=true),
     *             @OA\Property(property="start_datetime", type="string", format="date-time", nullable=true),
     *             @OA\Property(property="end_datetime", type="string", format="date-time", nullable=true),
     *             @OA\Property(property="duration_type", type="string", nullable=true),
     *             @OA\Property(property="total_hours", type="number", format="float", nullable=true),
     *             @OA\Property(property="total_amount", type="number", format="float", nullable=true),
     *             @OA\Property(property="discount", type="number", format="float", nullable=true),
     *             @OA\Property(property="tax", type="number", format="float", nullable=true),
     *             @OA\Property(property="address_id", type="integer", nullable=true),
     *             @OA\Property(property="address_json", type="object", nullable=true),
     *             @OA\Property(property="transmission_type", type="string", nullable=true),
     *             @OA\Property(property="is_recurring", type="boolean", nullable=true),
     *             @OA\Property(property="recurring_weeks", type="integer", nullable=true),
     *             @OA\Property(
     *                 property="selected_days",
     *                 type="array",
     *                 @OA\Items(type="string")
     *             ),
     *             @OA\Property(
     *                 property="time_slots",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="date", type="string"),
     *                     @OA\Property(property="start_time", type="string"),
     *                     @OA\Property(property="end_time", type="string")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="service_use_cases",
     *                 type="array",
     *                 @OA\Items(type="integer")
     *             ),
     *             @OA\Property(
     *                 property="license_types",
     *                 type="array",
     *                 @OA\Items(type="integer")
     *             ),
     *             @OA\Property(
     *                 property="material_ids",
     *                 type="array",
     *                 @OA\Items(type="integer")
     *             ),
     *             @OA\Property(property="service_requirements", type="string", nullable=true),
     *             @OA\Property(property="service_type", type="integer", nullable=true),
     *
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="service_id", type="integer", nullable=true),
     *                     @OA\Property(property="class_name", type="string", nullable=true),
     *                     @OA\Property(property="service_item_id", type="integer", nullable=true),
     *                     @OA\Property(property="service_name", type="string", nullable=true),
     *                     @OA\Property(property="quantity", type="number", nullable=true),
     *                     @OA\Property(property="price", type="number", nullable=true),
     *                     @OA\Property(property="type", type="string", nullable=true),
     *                     @OA\Property(property="subject_type", type="integer", nullable=true),
     *                     @OA\Property(property="min_people", type="integer", nullable=true),
     *                     @OA\Property(property="max_people", type="integer", nullable=true),
     *                     @OA\Property(property="service_type", type="integer", nullable=true)
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Enquiry created successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. VALIDATION
        |--------------------------------------------------------------------------
        | Required validation intentionally removed for now.
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'provider_id'          => 'nullable|integer',
            'service_category_id'  => 'nullable|integer',

            'start_datetime'       => 'nullable|date',
            'end_datetime'         => 'nullable|date',

            'duration_type'        => 'nullable|in:single_day,multiple_days',

            'is_recurring'         => 'nullable|boolean',
            'recurring_weeks'      => 'nullable|integer|min:1',

            'selected_days'        => 'nullable|array',
            'selected_dates'       => 'nullable|array',

            'time_slots'           => 'nullable|array',

            'address_id'           => 'nullable|integer',
            'address_json'         => 'nullable|array',

            'service_use_cases'    => 'nullable|array',
            'license_types'        => 'nullable|array',
            'material_ids'         => 'nullable|array',

            'items'                => 'nullable|array',

            'items.*.service_id'      => 'nullable|integer',
            'items.*.service_name'    => 'nullable|string',
            'items.*.quantity'        => 'nullable|numeric|min:0',
            'items.*.price'           => 'nullable|numeric|min:0',
            'items.*.service_type'    => 'nullable|integer',
            'items.*.service_item_id' => 'nullable|integer',
            'items.*.class_name'      => 'nullable|string',
            'items.*.type'            => 'nullable|string',
            'items.*.subject_type'    => 'nullable|integer',
            'items.*.min_people'      => 'nullable|integer|min:0',
            'items.*.max_people'      => 'nullable|integer|min:0',
        ]);

        $user = auth()->user();

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | 2. ENQUIRY NUMBER
            |--------------------------------------------------------------------------
            */

            do {
                $enquiryNumber = 'ENQ-' . strtoupper(Str::random(8));
            } while (
                Enquiry::where('enquiry_number', $enquiryNumber)->exists()
            );


            /*
            |--------------------------------------------------------------------------
            | 3. BUILD TIME SLOTS
            |--------------------------------------------------------------------------
            */

            $timeSlots = $this->buildTimeSlots($request);


            /*
            |--------------------------------------------------------------------------
            | 4. CREATE ENQUIRY
            |--------------------------------------------------------------------------
            */

            $enquiry = Enquiry::create([

                'enquiry_number' => $enquiryNumber,

                'status' => Enquiry::STATUS_PENDING,

                'user_id' => $user->id,

                'provider_id' => $request->provider_id,

                'service_category_id' => $request->service_category_id,

                'start_datetime' => $request->start_datetime,
                'end_datetime' => $request->end_datetime,

                'duration_type' => $request->duration_type,

                'total_hours' => $request->total_hours ?? 0,

                'total_amount' => $request->total_amount ?? 0,
                'discount' => $request->discount ?? 0,
                'tax' => $request->tax ?? 0,

                'address_id' => $request->address_id,
                'address_json' => $request->address_json,

                'transmission_type' => $request->transmission_type,

                'is_recurring' => $request->is_recurring ?? false,

                'recurring_weeks' => $request->recurring_weeks,

                'selected_days' => $request->selected_days,

                'time_slots' => !empty($timeSlots)
                    ? $timeSlots
                    : $request->time_slots,

                'service_use_cases' => $request->service_use_cases,

                'license_types' => $request->license_types,

                'material_ids' => $request->material_ids,

                'service_requirements' => $request->service_requirements,

                'service_type' => $request->service_type,
            ]);


            /*
            |--------------------------------------------------------------------------
            | 5. STORE ENQUIRY ITEMS
            |--------------------------------------------------------------------------
            */

            if ($request->filled('items') && is_array($request->items)) {

                foreach ($request->items as $item) {

                    EnquiryItem::create([

                        'enquiry_id' => $enquiry->id,

                        'service_id' =>
                            $item['service_id'] ?? null,

                        'class_name' =>
                            $item['class_name'] ?? null,

                        'service_item_id' =>
                            $item['service_item_id'] ?? null,

                        'service_name' =>
                            $item['service_name'] ?? null,

                        'quantity' =>
                            $item['quantity'] ?? 0,

                        'price' =>
                            $item['price'] ?? 0,

                        'total_price' =>
                            ($item['quantity'] ?? 0)
                            * ($item['price'] ?? 0),

                        'type' =>
                            $item['type'] ?? null,

                        'subject_type' =>
                            $item['subject_type'] ?? null,

                        'min_people' =>
                            $item['min_people'] ?? null,

                        'max_people' =>
                            $item['max_people'] ?? null,

                        'service_type' =>
                            $item['service_type'] ?? 0,
                    ]);
                }
            }


            /*
            |--------------------------------------------------------------------------
            | 6. LOAD RELATIONS
            |--------------------------------------------------------------------------
            */

            $enquiry->load([
                'items',
                'addonItems',
                'user:id,name,email,phone',
                'provider:id,name,email,phone',
                'serviceCategory',
                'address',
            ]);


            DB::commit();


            return response()->json([
                'success' => true,
                'message' => 'Enquiry created successfully',
                'data' => $enquiry,
            ], 201);


        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Unable to create enquiry',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Update enquiry using enquiry number.
     *
     * Provider can use this API to respond/update
     * dates, timeslots, amount and other enquiry details.
     */
    /**
     * @OA\Put(
     *     path="/api/provider/enquiries/{enquiry_number}",
     *     tags={"Provider Enquiries"},
     *     summary="Respond to and update an enquiry",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="enquiry_number",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         example="ENQ-X8K29ABC"
     *     ),
     *
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="start_datetime",
     *                 type="string",
     *                 format="date-time",
     *                 example="2026-08-20 10:00:00"
     *             ),
     *             @OA\Property(
     *                 property="end_datetime",
     *                 type="string",
     *                 format="date-time",
     *                 example="2026-08-20 13:00:00"
     *             ),
     *             @OA\Property(
     *                 property="total_hours",
     *                 type="number",
     *                 format="float",
     *                 example=3
     *             ),
     *             @OA\Property(
     *                 property="total_amount",
     *                 type="number",
     *                 format="float",
     *                 example=1500
     *             ),
     *             @OA\Property(
     *                 property="discount",
     *                 type="number",
     *                 format="float",
     *                 example=100
     *             ),
     *             @OA\Property(
     *                 property="tax",
     *                 type="number",
     *                 format="float",
     *                 example=252
     *             ),
     *             @OA\Property(
     *                 property="time_slots",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="date",
     *                         type="string",
     *                         example="2026-08-20"
     *                     ),
     *                     @OA\Property(
     *                         property="start_time",
     *                         type="string",
     *                         example="10:00"
     *                     ),
     *                     @OA\Property(
     *                         property="end_time",
     *                         type="string",
     *                         example="13:00"
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="service_id", type="integer", nullable=true),
     *                     @OA\Property(property="service_item_id", type="integer", nullable=true),
     *                     @OA\Property(property="service_name", type="string", nullable=true),
     *                     @OA\Property(property="quantity", type="number", nullable=true),
     *                     @OA\Property(property="price", type="number", nullable=true),
     *                     @OA\Property(property="service_type", type="integer", nullable=true),
     *                     @OA\Property(property="class_name", type="string", nullable=true),
     *                     @OA\Property(property="type", type="string", nullable=true),
     *                     @OA\Property(property="subject_type", type="integer", nullable=true),
     *                     @OA\Property(property="min_people", type="integer", nullable=true),
     *                     @OA\Property(property="max_people", type="integer", nullable=true)
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Enquiry updated successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized provider"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Enquiry not found"
     *     )
     * )
     */
    public function updateByEnquiryNumber(
        Request $request,
        string $enquiryNumber
    ) {
        /*
        |--------------------------------------------------------------------------
        | 1. NO REQUIRED VALIDATION
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'provider_id'          => 'nullable|integer',

            'service_category_id'  => 'nullable|integer',

            'start_datetime'       => 'nullable|date',

            'end_datetime'         => 'nullable|date',

            'duration_type'        => 'nullable|in:single_day,multiple_days',

            'is_recurring'         => 'nullable|boolean',

            'recurring_weeks'      => 'nullable|integer|min:1',

            'selected_days'        => 'nullable|array',

            'selected_dates'       => 'nullable|array',

            'time_slots'           => 'nullable|array',

            'address_id'           => 'nullable|integer',

            'address_json'         => 'nullable|array',

            'service_use_cases'    => 'nullable|array',

            'license_types'        => 'nullable|array',

            'material_ids'         => 'nullable|array',

            'items'                => 'nullable|array',

            'items.*.service_id'      => 'nullable|integer',
            'items.*.service_name'    => 'nullable|string',
            'items.*.quantity'        => 'nullable|numeric|min:0',
            'items.*.price'           => 'nullable|numeric|min:0',
            'items.*.service_type'    => 'nullable|integer',
            'items.*.service_item_id' => 'nullable|integer',
            'items.*.class_name'      => 'nullable|string',
            'items.*.type'            => 'nullable|string',
            'items.*.subject_type'    => 'nullable|integer',
            'items.*.min_people'      => 'nullable|integer|min:0',
            'items.*.max_people'      => 'nullable|integer|min:0',
        ]);


        $enquiry = Enquiry::where(
            'enquiry_number',
            $enquiryNumber
        )->first();


        if (!$enquiry) {

            return response()->json([
                'success' => false,
                'message' => 'Enquiry not found',
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | PROVIDER SECURITY
        |--------------------------------------------------------------------------
        |
        | If provider_id is already assigned, only that provider
        | should be able to update the enquiry.
        |
        |--------------------------------------------------------------------------
        */

        $user = auth()->user();

        if (
            $enquiry->provider_id &&
            (int) $enquiry->provider_id !== (int) $user->id
        ) {

            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this enquiry',
            ], 403);
        }


        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | 2. BUILD TIME SLOTS
            |--------------------------------------------------------------------------
            */

            $timeSlots = $this->buildTimeSlots($request);


            /*
            |--------------------------------------------------------------------------
            | 3. UPDATE ENQUIRY
            |--------------------------------------------------------------------------
            */

            $updateData = [];


            $fields = [

                'provider_id',
                'service_category_id',

                'start_datetime',
                'end_datetime',

                'duration_type',

                'total_hours',
                'total_amount',
                'discount',
                'tax',

                'address_id',
                'address_json',

                'transmission_type',

                'is_recurring',
                'recurring_weeks',

                'selected_days',

                'service_use_cases',
                'license_types',
                'material_ids',

                'service_requirements',

                'service_type',
            ];


            foreach ($fields as $field) {

                if ($request->has($field)) {
                    $updateData[$field] = $request->input($field);
                }
            }


            /*
            |--------------------------------------------------------------------------
            | TIME SLOTS
            |--------------------------------------------------------------------------
            */

            if (!empty($timeSlots)) {

                $updateData['time_slots'] = $timeSlots;

            } elseif ($request->has('time_slots')) {

                $updateData['time_slots'] =
                    $request->time_slots;
            }


            /*
            |--------------------------------------------------------------------------
            | PROVIDER RESPONDED
            |--------------------------------------------------------------------------
            */

            $updateData['status'] =
                Enquiry::STATUS_RESPONDED;


            $enquiry->update($updateData);


            /*
            |--------------------------------------------------------------------------
            | 4. UPDATE ITEMS
            |--------------------------------------------------------------------------
            |
            | If items are sent, replace the existing enquiry items.
            |
            |--------------------------------------------------------------------------
            */

            if ($request->has('items')) {

                $incomingCombos = [];

                foreach ($request->items ?? [] as $item) {

                    $conditions = [

                        'service_id' =>
                            $item['service_id'] ?? null,

                        'service_type' =>
                            $item['service_type'] ?? 0,

                        'service_item_id' =>
                            $item['service_item_id'] ?? null,

                        'class_name' =>
                            $item['class_name'] ?? null,
                    ];


                    EnquiryItem::updateOrCreate(
                        array_merge(
                            ['enquiry_id' => $enquiry->id],
                            $conditions
                        ),
                        [

                            'service_name' =>
                                $item['service_name'] ?? null,

                            'quantity' =>
                                $item['quantity'] ?? 0,

                            'price' =>
                                $item['price'] ?? 0,

                            'total_price' =>
                                ($item['quantity'] ?? 0)
                                * ($item['price'] ?? 0),

                            'type' =>
                                $item['type'] ?? null,

                            'subject_type' =>
                                $item['subject_type'] ?? null,

                            'min_people' =>
                                $item['min_people'] ?? null,

                            'max_people' =>
                                $item['max_people'] ?? null,

                            'service_type' =>
                                $item['service_type'] ?? 0,
                        ]
                    );


                    $incomingCombos[] = implode('|', [

                        $item['service_id'] ?? '',

                        $item['service_type'] ?? '',

                        $item['service_item_id'] ?? '',

                        $item['class_name'] ?? '',
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Remove old items that were not sent
                |--------------------------------------------------------------------------
                */

                $enquiry->serviceItems->each(
                    function ($enquiryItem) use ($incomingCombos) {

                        $combo = implode('|', [

                            $enquiryItem->service_id,

                            $enquiryItem->service_type ?? '',

                            $enquiryItem->service_item_id ?? '',

                            $enquiryItem->class_name ?? '',
                        ]);


                        if (!in_array($combo, $incomingCombos)) {
                            $enquiryItem->delete();
                        }
                    }
                );
            }


            /*
            |--------------------------------------------------------------------------
            | 5. LOAD UPDATED DATA
            |--------------------------------------------------------------------------
            */

            $enquiry->load([
                'items',
                'addonItems',
                'user:id,name,email,phone',
                'provider:id,name,email,phone',
                'serviceCategory',
                'address',
            ]);


            DB::commit();


            return response()->json([
                'success' => true,
                'message' => 'Enquiry updated successfully',
                'data' => $enquiry,
            ]);


        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Unable to update enquiry',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Build time slots from request.
     *
     * Similar to the existing cart logic.
     */
    private function buildTimeSlots(Request $request): array
    {
        $timeSlots = [];


        /*
        |--------------------------------------------------------------------------
        | If frontend directly sends time_slots
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('time_slots') &&
            is_array($request->time_slots)
        ) {

            foreach ($request->time_slots as $slot) {

                if (empty($slot['date'])) {
                    continue;
                }

                $timeSlots[] = [

                    'date' =>
                        $slot['date'],

                    'start_time' =>
                        $slot['start_time'] ?? null,

                    'end_time' =>
                        $slot['end_time'] ?? null,
                ];
            }

            return $timeSlots;
        }


        /*
        |--------------------------------------------------------------------------
        | No dates/times supplied
        |--------------------------------------------------------------------------
        */

        if (
            !$request->filled('start_datetime') ||
            !$request->filled('end_datetime')
        ) {

            return [];
        }


        $start = Carbon::parse(
            $request->start_datetime
        );

        $end = Carbon::parse(
            $request->end_datetime
        );


        /*
        |--------------------------------------------------------------------------
        | Single day
        |--------------------------------------------------------------------------
        */

        if (
            !$request->duration_type ||
            $request->duration_type === 'single_day'
        ) {

            return [[

                'date' =>
                    $start->format('Y-m-d'),

                'start_time' =>
                    $start->format('H:i'),

                'end_time' =>
                    $end->format('H:i'),
            ]];
        }


        /*
        |--------------------------------------------------------------------------
        | Multiple days with selected dates
        |--------------------------------------------------------------------------
        */

        if (
            !empty($request->selected_dates) &&
            is_array($request->selected_dates)
        ) {

            foreach ($request->selected_dates as $date) {

                $dateObj = Carbon::parse($date);

                $timeSlots[] = [

                    'date' =>
                        $dateObj->format('Y-m-d'),

                    'start_time' =>
                        $start->format('H:i'),

                    'end_time' =>
                        $end->format('H:i'),
                ];
            }

            return $timeSlots;
        }


        /*
        |--------------------------------------------------------------------------
        | Multiple days with selected days + recurring weeks
        |--------------------------------------------------------------------------
        */

        if (
            !empty($request->selected_days) &&
            is_array($request->selected_days)
        ) {

            $daysMap = [

                'monday' => 1,
                'tuesday' => 2,
                'wednesday' => 3,
                'thursday' => 4,
                'friday' => 5,
                'saturday' => 6,
                'sunday' => 7,
            ];


            $weeks =
                $request->recurring_weeks ?? 1;


            $currentDate =
                $start->copy();


            for (
                $week = 0;
                $week < $weeks;
                $week++
            ) {

                $weekStart =
                    $currentDate
                        ->copy()
                        ->startOfWeek(Carbon::MONDAY);


                foreach (
                    $request->selected_days
                    as $day
                ) {

                    $day = strtolower(
                        trim($day)
                    );


                    if (
                        !isset(
                            $daysMap[$day]
                        )
                    ) {
                        continue;
                    }


                    $slotDate =
                        $weekStart
                            ->copy()
                            ->addDays(
                                $daysMap[$day] - 1
                            );


                    if (
                        $slotDate->lt(
                            $start->copy()
                                ->startOfDay()
                        )
                    ) {
                        continue;
                    }


                    $timeSlots[] = [

                        'date' =>
                            $slotDate
                                ->format('Y-m-d'),

                        'start_time' =>
                            $start->format('H:i'),

                        'end_time' =>
                            $end->format('H:i'),
                    ];
                }


                $currentDate->addWeek();
            }
        }


        return $timeSlots;
    }
}