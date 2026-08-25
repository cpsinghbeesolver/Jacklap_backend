<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * @OA\Post(
     *     path="/cart/store",
     *     summary="Store or update cart with recurring, duration type and time slot support",
     *     security={{ "bearerAuth": {} }},
     *     tags={"Cart"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"start_datetime","end_datetime","is_recurring","items","duration_type"},
     *
     *             @OA\Property(property="provider_id", type="integer", example=2),
     *             @OA\Property(property="service_category_id", type="integer", example=1),
     *             @OA\Property(property="transmission_type", type="integer", example=2),
     *             @OA\Property(property="start_datetime", type="string", example="2026-04-25 10:00:00"),
     *             @OA\Property(property="end_datetime", type="string", example="2026-04-25 12:00:00"),
     *             @OA\Property(property="service_requirements", type="string", example="test"),
     *             @OA\Property(
     *                 property="duration_type",
     *                 type="string",
     *                 enum={"single_day","multiple_days"},
     *                 example="single_day"
     *             ),
     *             @OA\Property(property="is_recurring", type="boolean", example=true),
     *             @OA\Property(property="recurring_weeks", type="integer", example=2, nullable=true),
     *             @OA\Property(
     *                 property="selected_days",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"monday","friday"}
     *             ),
     *             @OA\Property(property="address_id", type="integer", example=5),
     *             @OA\Property(
     *                 property="address_json",
     *                 type="object",
     *                 example={"address":"ABC Street","city":"Delhi"}
     *             ),
     *             @OA\Property(
     *                 property="service_use_cases",
     *                 type="array",
     *                 description="Required when service_category_id = 5",
     *                 @OA\Items(type="integer"),
     *                 example={1,2,3}
     *             ),
     *             @OA\Property(
     *                 property="license_types",
     *                 type="array",
     *                 description="Required when service_category_id = 5",
     *                 @OA\Items(type="integer"),
     *                 example={2,5}
     *             ),
     *             @OA\Property(
     *                 property="material_ids",
     *                 type="array",
     *                 description="Required when service_category_id = 1",
     *                 @OA\Items(type="integer"),
     *                 example={2,5}
     *             ),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="service_id", type="integer", example=1),
     *                     @OA\Property(property="service_name", type="string", example="Cleaning"),
     *                     @OA\Property(property="quantity", type="number", example=2),
     *                     @OA\Property(property="price", type="number", example=500),
     *                     @OA\Property(property="service_item_id", type="integer", example=1),
     *                     @OA\Property(property="class_name", type="string", example="Class 1"),
     *                     @OA\Property(property="service_type", type="integer", example=1),
     *                     @OA\Property(
     *                         property="type",
     *                         type="string",
     *                         description="Required when service_category_id = 2",
     *                         example="service"
     *                     ),
     *                     @OA\Property(
     *                         property="subject_type",
     *                         type="integer",
     *                         enum={1,2,3,4,5},
     *                         description="Category 3: 1=academic, 2=non_academic | Category 2: 3=Male, 4=Female, 5=Both",
     *                         example=3
     *                     ),
     *                     @OA\Property(
     *                         property="min_people",
     *                         type="integer",
     *                         nullable=true,
     *                         description="For group packages only (service_category_id = 2, type = package)",
     *                         example=3
     *                     ),
     *                     @OA\Property(
     *                         property="max_people",
     *                         type="integer",
     *                         nullable=true,
     *                         description="For group packages only (service_category_id = 2, type = package)",
     *                         example=5
     *                     ),
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Cart stored successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function storeCart(Request $request)
    {
        // ─────────────────────────────────────────────
        // 1. DEFAULTS
        // ─────────────────────────────────────────────
        $request->merge([
            'recurring_weeks' => $request->recurring_weeks ?? 1,
        ]);

        // ─────────────────────────────────────────────
        // 2. VALIDATION
        // ─────────────────────────────────────────────
        $request->validate([
            // 'start_datetime'  => 'date',
            // 'end_datetime'    => 'date|after:start_datetime',
            'start_datetime' => [
                'required_unless:service_category_id,1,2,3',
                'nullable',
                'date',
            ],

            'end_datetime' => [
                'required_unless:service_category_id,1,2,3',
                'nullable',
                'date',
                'after:start_datetime',
            ],

            'duration_type'   => 'required|in:single_day,multiple_days',

            'is_recurring'    => 'required|boolean',
            'recurring_weeks' => 'required_if:is_recurring,1|nullable|integer|min:1',

            'selected_days'    => 'nullable|array',
            'selected_days.*'  => 'string|in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',

            'selected_dates'   => 'nullable|array',
            'selected_dates.*' => 'date',

            'items'                    => 'required|array|min:1',
            'items.*.service_id'       => 'required|integer',
            'items.*.service_name'     => 'required|string',
            'items.*.quantity'         => 'required|numeric|min:1',
            'items.*.price'            => 'required|numeric|min:0',
            'items.*.service_type'     => 'nullable|integer',

            // Category 1 — Home Cleaning
            'items.*.service_item_id'  => [
                'nullable',
                //'required_if:service_category_id,1',
                'integer',
                //'exists:master_service_items,id',
            ],

            // Category 3 — Teaching
            'items.*.class_name'       => ['nullable', 'string'],
            'items.*.type'             => ['nullable', 'string'],
            'items.*.subject_type'     => ['nullable', 'integer', 'in:1,2,3,4,5'],
            'items.*.min_people'       => ['nullable', 'integer', 'min:0'],
            'items.*.max_people'       => ['nullable', 'integer', 'min:0'],

            // Category 5 — only
            'service_use_cases'        => 'required_if:service_category_id,5|array',
            'service_use_cases.*'      => 'integer',
            'license_types'            => 'required_if:service_category_id,5|array',
            'license_types.*'          => 'integer',

            // Category 1 — only
            'material_ids'             => 'required_if:service_category_id,1|array',
            'material_ids.*'           => 'integer',
        ],
        [
            'start_datetime.required_unless' =>
                'Please select a start date and time.',

            'end_datetime.required_unless' =>
                'Please select an end date and time.',
        ]
        );

        // ─────────────────────────────────────────────
        // 3. PROVIDER / CATEGORY MISMATCH CHECK
        // ─────────────────────────────────────────────
        $user         = auth()->user();
        $existingCart = Cart::where('user_id', $user->id)->first();

        if ($existingCart) {
            if ($request->provider_id && $existingCart->provider_id != $request->provider_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot add services from multiple providers in one cart',
                ], 422);
            }

            if ($request->service_category_id && $existingCart->service_category_id != $request->service_category_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot mix different service categories in one cart',
                ], 422);
            }
        }

        // ─────────────────────────────────────────────
        // 4. PARSE DATE / TIME
        // ─────────────────────────────────────────────
        $start     = \Carbon\Carbon::parse($request->start_datetime);
        $end       = \Carbon\Carbon::parse($request->end_datetime);
        $startTime = $start->format('H:i'); // fallback only
        $endTime   = $end->format('H:i');   // fallback only

        /**
         * Build a date → {start_time, end_time} lookup from frontend time_slots.
         * HIGHEST PRIORITY: frontend sends per-date custom times.
         */
        $frontendSlotMap = [];
        if (!empty($request->time_slots) && is_array($request->time_slots)) {
            foreach ($request->time_slots as $fs) {
                if (!empty($fs['date'])) {
                    $frontendSlotMap[$fs['date']] = [
                        'start_time' => $fs['start_time'],
                        'end_time'   => $fs['end_time'] ?? null ,
                    ];
                }
            }
        }

        // ─────────────────────────────────────────────
        // 5. BUILD TIME SLOTS
        // ─────────────────────────────────────────────
        $timeSlots = [];

        /**
         * SINGLE DAY — one slot only, force is_recurring = false
         */
        if ($request->duration_type === 'single_day') {

            $dateKey   = $start->format('Y-m-d');
            $slotTimes = $frontendSlotMap[$dateKey] ?? [
                'start_time' => $startTime,
                'end_time'   => $endTime,
            ];

            $timeSlots[] = [
                'date'       => $dateKey,
                'start_time' => $slotTimes['start_time'],
                'end_time'   => $slotTimes['end_time'],
            ];

            $request->merge(['is_recurring' => false]);
        }

        /**
         * MULTIPLE DAYS
         *
         * PRIORITY 1: frontend time_slots with per-date custom times → use as-is
         * PRIORITY 2: selected_dates → build using per-date times from frontendSlotMap or fallback
         * PRIORITY 3: selected_days + recurring_weeks calculation
         */
        if ($request->duration_type === 'multiple_days') {

            if (empty($request->time_slots) && empty($request->selected_dates) && empty($request->selected_days)) {
                return response()->json([
                    'success' => false,
                    'message' => 'time_slots, selected_dates, or selected_days is required for multiple_days.',
                ], 422);
            }

            $seenDates = [];

            // ── PRIORITY 1: Use frontend time_slots directly (per-date custom times) ──
            if (!empty($frontendSlotMap)) {

                foreach ($frontendSlotMap as $dateKey => $times) {

                    $slotDate = \Carbon\Carbon::parse($dateKey);

                    if ($slotDate->lt($start->copy()->startOfDay())) continue;

                    if (in_array($dateKey, $seenDates)) continue;
                    $seenDates[] = $dateKey;

                    $timeSlots[] = [
                        'date'       => $dateKey,
                        'start_time' => $times['start_time'],
                        'end_time'   => $times['end_time'],
                    ];
                }

            // ── PRIORITY 2: selected_dates with per-date or fallback times ──
            } elseif (!empty($request->selected_dates)) {

                foreach ($request->selected_dates as $dateStr) {

                    $slotDate = \Carbon\Carbon::parse($dateStr);

                    if ($slotDate->lt($start->copy()->startOfDay())) continue;

                    $dateKey   = $slotDate->format('Y-m-d');
                    $slotTimes = $frontendSlotMap[$dateKey] ?? [
                        'start_time' => $startTime,
                        'end_time'   => $endTime,
                    ];

                    if (in_array($dateKey, $seenDates)) continue;
                    $seenDates[] = $dateKey;

                    $timeSlots[] = [
                        'date'       => $dateKey,
                        'start_time' => $slotTimes['start_time'],
                        'end_time'   => $slotTimes['end_time'],
                    ];
                }

            // ── PRIORITY 3: Recurring — calculate from day names + weeks ──
            } else {

                $daysMap = [
                    'monday' => 1, 'tuesday' => 2, 'wednesday' => 3,
                    'thursday' => 4, 'friday' => 5, 'saturday' => 6, 'sunday' => 7,
                ];

                $currentDate = $start->copy();

                for ($week = 0; $week < $request->recurring_weeks; $week++) {

                    $weekStart = $currentDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY);

                    foreach ($request->selected_days as $day) {

                        $targetDay = $daysMap[strtolower(trim($day))] ?? null;
                        if ($targetDay === null) continue;

                        $slotDate = $weekStart->copy()->addDays($targetDay - 1);
                        if ($slotDate->lt($start->copy()->startOfDay())) continue;

                        $dateKey   = $slotDate->format('Y-m-d');
                        $slotTimes = $frontendSlotMap[$dateKey] ?? [
                            'start_time' => $startTime,
                            'end_time'   => $endTime,
                        ];

                        if (in_array($dateKey, $seenDates)) continue;
                        $seenDates[] = $dateKey;

                        $timeSlots[] = [
                            'date'       => $dateKey,
                            'start_time' => $slotTimes['start_time'],
                            'end_time'   => $slotTimes['end_time'],
                        ];
                    }

                    $currentDate->addWeek();
                }
            }

            if (empty($timeSlots)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid time slots generated. Ensure dates fall on or after start_datetime.',
                ], 422);
            }
        }

        // ─────────────────────────────────────────────
        // 6. CART CREATE / UPDATE
        // ─────────────────────────────────────────────
        $cart = Cart::updateOrCreate(
            ['user_id' => $user->id],
            [
                'provider_id'         => $request->provider_id,
                'service_category_id' => $request->service_category_id,

                'start_datetime'      => $request->start_datetime,
                'end_datetime'        => $request->end_datetime,
                'duration_type'       => $request->duration_type,

                'is_recurring'        => $request->is_recurring,
                'recurring_weeks'     => $request->recurring_weeks,
                'selected_days'       => $request->selected_days,
                'selected_dates'      => $request->selected_dates ?? null,
                'time_slots'          => $timeSlots,

                'address_id'          => $request->address_id,
                'address_json'        => $request->address_json,
                'service_requirements'=> $request->service_requirements ?? null,

                'service_use_cases'   => $request->service_category_id == 5
                                            ? $request->service_use_cases : null,
                'license_types'       => $request->service_category_id == 5
                                            ? $request->license_types : null,
                'material_ids'        => $request->service_category_id == 1
                                            ? $request->material_ids : null,
            ]
        );

        // ─────────────────────────────────────────────
        // 7. TOTAL HOURS (cart-level — full slot hours × slots)
        //    e.g. 2 hrs/slot × 3 slots = 6 total hours
        // ─────────────────────────────────────────────
        $totalHours   = 0;
        $hoursPerSlot = 0;

        foreach ($timeSlots as $slot) {
            $slotStart = \Carbon\Carbon::parse($slot['date'] . ' ' . $slot['start_time']);
            $slotEnd   = \Carbon\Carbon::parse($slot['date'] . ' ' . $slot['end_time']);

            if ($slotEnd->lessThanOrEqualTo($slotStart)) {
                $slotEnd->addDay();
            }

            $hoursPerSlot = $slotStart->diffInMinutes($slotEnd) / 60;
            $totalHours  += $hoursPerSlot;
        }

        $itemsCount      = count($request->items);
        $hoursPerService = $itemsCount > 0 ? $totalHours / $itemsCount : $totalHours;
        if(in_array($request->service_category_id, [1,2,3])) {
            $totalHours = $itemsCount; // Total hours equals number of items for teaching category
        }
        if(in_array($request->service_category_id, [5,1,2,3])) {
            $hoursPerService = 1; // For teaching category, each service is considered 1 hour
        }
        // ─────────────────────────────────────────────
        // 9. CART ITEMS — UPSERT
        // ─────────────────────────────────────────────
        // $incomingIds = [];
        $totalAmount = 0;

        foreach ($request->items as $item) {

            $total = $item['quantity'] * $item['price'] * $hoursPerService;

            $conditions = [
                'service_id' => $item['service_id'],
                'service_type' => $item['service_type'] ?? 0,
                'service_item_id' => $item['service_item_id'] ?? null,
                'class_name' => $item['class_name'] ?? null,
            ];

            $cart->serviceItems()->updateOrCreate(
                $conditions,
                [
                    'service_name'    => $item['service_name'],
                    'quantity'        => $item['quantity'],
                    'price'           => $item['price'],
                    'total_price'     => $total,

                    'service_item_id' => $item['service_item_id'] ?? null,
                    'class_name'      => $item['class_name'] ?? null,
                    'service_type'    => $item['service_type'] ?? 0,
                    'type'            => $item['type'] ?? null,
                    'subject_type'    => $item['subject_type'] ?? null,
                    'min_people'      => $item['min_people'] ?? null,
                    'max_people'      => $item['max_people'] ?? null,
                ]
            );
            $totalAmount += $total;
        }

        $incomingCombos = collect($request->items)
            ->map(function ($item) {
                return implode('|', [
                    $item['service_id'],
                    $item['service_type'] ?? 0,
                    $item['service_item_id'] ?? '',
                    $item['class_name'] ?? '',
                ]);
            })
            ->toArray();
            
        $cart->serviceItems->each(function ($cartItem) use ($incomingCombos) {

            $combo = implode('|', [
                $cartItem->service_id,
                $cartItem->service_type ?? 0,
                $cartItem->service_item_id ?? '',
                $cartItem->class_name ?? '',
            ]);

            if (!in_array($combo, $incomingCombos)) {
                $cartItem->delete();
            }
        });

        // ─────────────────────────────────────────────
        // 11. UPDATE CART TOTALS
        // ─────────────────────────────────────────────
        $cart->update([
            'total_amount' => $totalAmount,
            'total_hours'  => $totalHours,
        ]);

        // ─────────────────────────────────────────────
        // 12. LOAD RELATIONS & RETURN
        // ─────────────────────────────────────────────
        $cart->load([
            'items.service',
            'addonItems',
            'address:id,address_type,type_name',
            'provider:id,name,image,gender',
        ]);

        if ($cart->provider) {
            $cart->provider->average_rating = $cart->provider->average_rating;
        }

        return response()->json([
            'success' => true,
            'message' => 'Cart saved successfully',
            'data'    => $cart,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/cart",
     *     summary="Get logged-in user cart with items",
     *     security={{ "bearerAuth": {} }},
     *     tags={"Cart"},
     *     @OA\Response(
     *         response=200,
     *         description="Cart fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getCart()
    {
        $user = auth()->user();

        $cart = Cart::with([
            'items.service',
            'addonItems',
            'address:id,address_type,type_name',
            'provider:id,name,image,gender'
        ])
        ->where('user_id', $user->id)
        ->first();

        if ($cart && $cart->provider) {
            $cart->provider->average_rating = $cart->provider->average_rating;
        }

        return response()->json([
            'success' => true,
            'data' => $cart
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/cart/clear",
     *     summary="Clear user cart",
     *     security={{"bearerAuth":{}}},
     *     tags={"Cart"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Cart cleared successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Cart cleared successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Cart not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Cart not found")
     *         )
     *     )
     * )
     */
    public function clearCart()
    {
        $user = auth()->user();

        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Cart not found'
            ], 404);
        }

        // Delete cart items first
        $cart->serviceItems()->delete();

        // Delete cart
        $cart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully'
        ]);
    }
}