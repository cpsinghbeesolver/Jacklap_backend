<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\LicenseType;
use App\Models\MasterService;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceUseCase;
use App\Models\User;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\TransmissionType;
use Database\Seeders\ServiceUseCaseSeeder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\BookingItem;
use Digikraaft\ReviewRating\Models\Review;

class ServiceCategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/service-category/all",
     *     summary="Retrieve all service categories",
     *     description="Fetches a complete list of available service categories along with their pricing details.",
     *     operationId="getServiceCategories",
     *     tags={"Services"},
     *     @OA\Response(
     *         response=200,
     *         description="Service categories retrieved successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 description="Indicates whether the request was successful.",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 description="Response message describing the result.",
     *                 example="Service categories fetched successfully."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 description="List of service category objects.",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         format="int64",
     *                         description="Unique identifier of the service category.",
     *                         example=1
     *                     ),
     *                     @OA\Property(
     *                         property="name",
     *                         type="string",
     *                         description="Name of the service category.",
     *                         example="Premium Cleaning Service"
     *                     ),
     *                     @OA\Property(
     *                         property="price",
     *                         type="number",
     *                         format="decimal",
     *                         description="Price associated with the service category.",
     *                         example=99.99
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Internal Server Error."
     *             )
     *         )
     *     ),
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
    public function getAllServiceCategories(){
        try{
            $serviceCategories = ServiceCategory::with('services')->get();
            return response()->json([
                'success'   => true,
                'message'   => 'Service Categories Fetched Successfully',
                'data'      => $serviceCategories,
            ], 200);
        }
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while fetching service categories.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/get-service-by-category",
     *     summary="Get default services of user with minimum total price for a category",
     *     tags={"Services"},
     *     operationId="getServiceDetail",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="service_category_id",
     *         in="query",
     *         description="ID of the service category",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
    *     @OA\Parameter(
    *         name="subject_type",
    *         in="query",
    *         description="Filter by gender (only applies to Salon category id=2). 3=Male, 4=Female, 5=Both",
    *         required=false,
    *         @OA\Schema(type="integer", enum={3, 4, 5}, example=3)
    *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Services found successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Services found successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=34),
     *                     @OA\Property(property="name", type="string", example="Deep Cleaning"),
     *                     @OA\Property(property="description", type="string", example="Deep cleaning desc"),
     *                     @OA\Property(property="service_category_id", type="integer", example=1),
     *                     @OA\Property(property="is_default", type="integer", example=1),
     *                     @OA\Property(property="price", type="string", example="350.00"),
     *                     @OA\Property(property="user_id", type="integer", example=41),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2026-04-08T06:08:41.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2026-04-08T06:08:41.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No default services found for this category",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No default services found for this category")
     *         )
     *     ),
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
    public function minPriceByCategory(Request $request)
    {
        $request->validate([
            'service_category_id' => 'required|integer|exists:service_categories,id',
            'subject_type'        => 'nullable|integer|in:3,4,5',
        ]);

        $categoryId  = $request->service_category_id;
        $subjectType = $request->subject_type;

        $serviceCategory = ServiceCategory::with('materials')->where('id', $categoryId)->first();

        /*
        |--------------------------------------------------------------------------
        | SALON CATEGORY (id = 2)
        |--------------------------------------------------------------------------
        */
        if ($categoryId == 2) {

            $query = MasterService::with(['items' => function ($q) {
                    $q->where('status', 1)->orderBy('sort_order');
                }])
                ->where('service_category_id', $categoryId)
                ->where('status', 1)
                ->whereNotIn('type',['specialization','product'])
                ->orderBy('sort_order');

            if ($subjectType && $subjectType != 5) {
                $query->whereIn('subject_type', [$subjectType, 5]);
            }

            $allServices = $query->get();

            if ($allServices->isEmpty()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No default master services found'
                ], 404);
            }

            $grouped = $allServices->groupBy('type');

            return response()->json([
                'status'  => true,
                'message' => 'All default services with minimum prices',
                'data'    => [
                    'category'        => $serviceCategory,
                    'services'        => $grouped->get('service', collect())->values(),
                    'packages'        => $grouped->get('package', collect())->values(),
                    'specializations' => $grouped->get('specialization', collect())->values(),
                    'products'        => $grouped->get('product', collect())->values(),
                ]
            ]);
        }

        // YOUR EXISTING CODE UNCHANGED BELOW ↓

        $masterServices = MasterService::with('items')->where('service_category_id', $categoryId)
            ->where('status', 1)
            ->get();

        if ($categoryId == 5) {
            $masterServices = ServiceUseCase::where('service_category_id', $categoryId)
                ->get();
            $licenseTypes = LicenseType::select('id', 'name', 'description')->get();
            $transmissionTypes = TransmissionType::all();
            return response()->json([
                'status' => true,
                'message' => 'All default services with minimum prices',
                'data' => [
                    'category' => $serviceCategory,
                    'licenseTypes' => $licenseTypes,
                    'services' => $masterServices,
                    'transmissionTypes' => $transmissionTypes
                ]
            ]);
        }

        if ($masterServices->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No default master services found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'All default services with minimum prices',
            'data' => [
                'category' => $serviceCategory,
                'services' => $masterServices
            ]
        ]);
    }

    public function minPriceByCategoryOld(Request $request)
    {
        $request->validate([
            'service_category_id' => 'required|integer|exists:service_categories,id',
        ]);

        $categoryId = $request->service_category_id;

        $serviceCategory = ServiceCategory::with('materials')->where('id',$categoryId)->first();
        $masterServices = MasterService::with('items')->where('service_category_id', $categoryId)
        ->where('status', 1)
        ->get();
        if($categoryId == 5){
            $masterServices = ServiceUseCase::where('service_category_id', $categoryId)
            //->where('status', 1)
            ->get();
        }
        // Step 1: Get ALL default master services

        if ($masterServices->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No default master services found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'All default services with minimum prices',
            'data' => [
                'category' => $serviceCategory,
                'services' => $masterServices
            ]
        ]);
    }

    /**
    * @OA\Post(
    *     path="/providers/by-service-category",
    *     summary="Get providers by service category and optionally by services",
    *     tags={"Services"},
    *     operationId="getProvidersList",
    *     security={{ "bearerAuth": {} }},
    *
    *     @OA\Parameter(
    *         name="service_category_id",
    *         in="query",
    *         required=true,
    *         description="ID of the service category (must exist in service_categories table)",
    *         @OA\Schema(type="integer", example=1)
    *     ),
    *
    *     @OA\Parameter(
    *         name="service_ids[]",
    *         in="query",
    *         required=false,
    *         description="Array of service IDs to filter users. Users must have ALL provided services.",
    *         @OA\Schema(type="array", @OA\Items(type="integer"), example={1, 2})
    *     ),
    *
    *     @OA\Parameter(
    *         name="language_ids[]",
    *         in="query",
    *         required=false,
    *         description="Array of language IDs to filter users by spoken/taught languages.",
    *         @OA\Schema(type="array", @OA\Items(type="integer"), example={1, 3})
    *     ),
    *
    *     @OA\Parameter(
    *         name="per_page",
    *         in="query",
    *         required=false,
    *         description="Number of results per page (min: 1, max: 50). Defaults to 10.",
    *         @OA\Schema(type="integer", minimum=1, maximum=50, example=10)
    *     ),
    *
    *     @OA\Parameter(
    *         name="latitude",
    *         in="query",
    *         required=false,
    *         description="Latitude of the user's current location. Required together with longitude for distance-based sorting. Only users within 100 km are returned.",
    *         @OA\Schema(type="number", format="float", example=28.6139)
    *     ),
    *
    *     @OA\Parameter(
    *         name="longitude",
    *         in="query",
    *         required=false,
    *         description="Longitude of the user's current location. Required together with latitude for distance-based sorting.",
    *         @OA\Schema(type="number", format="float", example=77.2090)
    *     ),
    *
    *     @OA\Parameter(
    *         name="teaching_mode",
    *         in="query",
    *         required=false,
    *         description="Filter by teaching mode. 1 = Online, 2 = Offline, 3 = Both. Users with mode 3 always match.",
    *         @OA\Schema(type="integer", enum={1, 2, 3}, example=1)
    *     ),
    *
    *     @OA\Parameter(
    *         name="transmission_type",
    *         in="query",
    *         required=false,
    *         description="Filter by transmission type. 1 = Manual, 2 = Automatic, 3 = Both. Users with type 3 always match.",
    *         @OA\Schema(type="integer", enum={1, 2, 3}, example=2)
    *     ),
    *
    *     @OA\Parameter(
    *         name="license_type_ids[]",
    *         in="query",
    *         required=false,
    *         description="Array of license type IDs to filter users.",
    *         @OA\Schema(type="array", @OA\Items(type="integer"), example={1, 2})
    *     ),
    *
    *     @OA\Parameter(
    *         name="material_type_ids[]",
    *         in="query",
    *         required=false,
    *         description="Array of material type IDs to filter users by provider materials.",
    *         @OA\Schema(type="array", @OA\Items(type="integer"), example={3})
    *     ),
    *
    *     @OA\Parameter(
    *         name="service_usecase_ids[]",
    *         in="query",
    *         required=false,
    *         description="Array of service usecase IDs to filter users.",
    *         @OA\Schema(type="array", @OA\Items(type="integer"), example={2, 5})
    *     ),
 *     @OA\Parameter(
 *         name="dates_match",
 *         in="query",
 *         required=false,
 *         description="How the 'dates' filter (see request body) should be applied. 'all' (default) = provider must be free at every listed date/time. 'any' = provider must be free at at least one listed date/time.",
 *         @OA\Schema(type="string", enum={"all", "any"}, example="all")
 *     ),
 *
 *     @OA\Parameter(
 *         name="slot_duration",
 *         in="query",
 *         required=false,
 *         description="Slot length in minutes, used both to check booking overlaps for the 'dates' filter and to generate each provider's 'requested_availability' slots. Defaults to 60.",
 *         @OA\Schema(type="integer", minimum=1, example=60)
 *     ),
    *     @OA\RequestBody(
    *         required=false,
    *         description="Optional body for service_with_class and service_with_item filters (can also be sent as query params depending on frontend setup)",
    *         @OA\JsonContent(
    *             @OA\Property(
    *                 property="service_with_class",
    *                 type="array",
    *                 description="Filter users by service along with subject type and class names.",
    *                 @OA\Items(
    *                     type="object",
    *                     required={"service_id"},
    *                     @OA\Property(property="service_id", type="integer", description="Service ID (must exist in master_services)", example=1),
    *                     @OA\Property(property="subject_type", type="integer", description="1 = Academic, 2 = Non-Academic. class_names must be empty when subject_type is 2.", nullable=true, enum={1, 2}, example=1),
    *                     @OA\Property(
    *                         property="class_names",
    *                         type="array",
    *                         nullable=true,
    *                         description="Array of class name strings. Only applicable when subject_type is 1 (Academic).",
    *                         @OA\Items(type="string", example="Grade 10")
    *                     )
    *                 )
    *             ),
    *             @OA\Property(
    *                 property="service_with_item",
    *                 type="array",
    *                 description="Filter users by service along with specific service items.",
    *                 @OA\Items(
    *                     type="object",
    *                     required={"service_id"},
    *                     @OA\Property(property="service_id", type="integer", description="Service ID (must exist in master_services)", example=2),
    *                     @OA\Property(
    *                         property="item_ids",
    *                         type="array",
    *                         nullable=true,
    *                         description="Array of item IDs (must exist in master_service_items).",
    *                         @OA\Items(type="integer", example=5)
    *                     )
    *                 )
    *             ),
   * @OA\Property(
 *                 property="dates",
 *                 type="array",
 *                 description="Filter providers by availability at specific date/time slots. Only providers with a matching active availability_slots entry AND no overlapping confirmed/in_progress booking are returned. Governed by 'dates_match' and 'slot_duration' above. If 'time' is omitted for an entry, only the provider's availability on that weekday is checked (no booking-overlap check for that entry).",
 *                 @OA\Items(
 *                     type="object",
 *                     required={"date"},
 *                     @OA\Property(property="date", type="string", format="date", description="Date to check, format Y-m-d.", example="2026-08-06"),
 *                     @OA\Property(property="time", type="string", nullable=true, description="Time to check, format H:i (24-hour). Optional.", example="12:00")
 *                 )
 *             )
    *         )
    *     ),
    *
    *     @OA\Response(
    *         response=200,
    *         description="Users fetched successfully",
    *         @OA\JsonContent(
    *             @OA\Property(property="success", type="boolean", example=true),
    *             @OA\Property(property="message", type="string", example="Users fetched successfully"),
    *             @OA\Property(
    *                 property="data",
    *                 type="object",
    *                 description="Paginated user results",
    *                 @OA\Property(property="current_page", type="integer", example=1),
    *                 @OA\Property(property="per_page", type="integer", example=10),
    *                 @OA\Property(property="total", type="integer", example=50),
    *                 @OA\Property(property="last_page", type="integer", example=5),
    *                 @OA\Property(
    *                     property="data",
    *                     type="array",
    *                     @OA\Items(
    *                         type="object",
    *                         @OA\Property(property="id", type="integer", example=101),
    *                         @OA\Property(property="name", type="string", example="John Doe"),
    *                         @OA\Property(property="latitude", type="number", format="float", nullable=true, example=28.61),
    *                         @OA\Property(property="longitude", type="number", format="float", nullable=true, example=77.20),
    *                         @OA\Property(property="distance", type="number", format="float", nullable=true, description="Distance in km from provided coordinates. NULL if coordinates not provided.", example=12.45),
    *                         @OA\Property(property="professional_detail", type="object", nullable=true),
    *                         @OA\Property(property="services", type="array", @OA\Items(type="object")),
    *                         @OA\Property(property="addon_services", type="array", @OA\Items(type="object")),
    *                         @OA\Property(property="media", type="array", @OA\Items(type="object")),
    *                         @OA\Property(property="availability_slots", type="array", @OA\Items(type="object")),
    *                         @OA\Property(property="license_types", type="array", @OA\Items(type="object")),
    *                         @OA\Property(property="service_usecases", type="array", @OA\Items(type="object"))
    *                     )
    *                 )
    *             )
    *         )
    *     ),
    *
    *     @OA\Response(
    *         response=422,
    *         description="Validation error",
    *         @OA\JsonContent(
    *             @OA\Property(property="message", type="string", example="The service_category_id field is required."),
    *             @OA\Property(
    *                 property="errors",
    *                 type="object",
    *                 example={"service_category_id": {"The service_category_id field is required."}}
    *             )
    *         )
    *     )
    * )
    */

    public function getUsersByServiceCategory(Request $request)
    {
        $request->validate([
            'service_category_id' => 'required|exists:service_categories,id',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'integer|exists:master_services,id',
            'language_ids' => 'nullable|array',
            'language_ids.*' => 'integer|exists:languages,id',
            'per_page' => 'nullable|integer|min:1|max:50',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'teaching_mode' => 'nullable|integer|in:1,2,3',
            'transmission_type' => 'nullable|integer|in:1,2,3',

            'license_type_ids' => 'nullable|array',
            'license_type_ids.*' => 'integer',

            'material_type_ids' => 'nullable|array',
            'material_type_ids.*' => 'integer',

            'service_usecase_ids' => 'nullable|array',
            'service_usecase_ids.*' => 'integer',
            'service_with_class' => 'nullable|array',
            'service_with_class.*.service_id' => 'required_with:service_with_class|integer|exists:master_services,id',
            'service_with_class.*.subject_type' => 'nullable|integer|in:1,2',
            'service_with_class.*.class_names' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) use ($request) {
                    preg_match('/service_with_class\.(\d+)\.class_names/', $attribute, $matches);
                    $index = $matches[1] ?? null;

                    if (!is_null($index)) {
                        $subjectType = $request->input("service_with_class.$index.subject_type");

                        if ($subjectType == 2 && !empty($value)) {
                            $fail('class_names must be empty when subject_type is 2 (non-academic).');
                        }
                    }
                }
            ],
            'service_with_class.*.class_names.*' => 'string',
            'service_with_item' => 'nullable|array',
            'service_with_item.*.service_id' => 'required_with:service_with_item|integer|exists:master_services,id',
            'service_with_item.*.item_ids' => 'nullable|array',
            'service_with_item.*.item_ids.*' => 'integer',
            'dates'               => 'nullable|array',
            'dates.*.date'        => 'required_with:dates|date_format:Y-m-d',
            'dates.*.time'        => 'nullable|date_format:H:i',
            'dates_match'         => 'nullable|in:any,all', // default: all
            'slot_duration'       => 'nullable|integer|min:1',
        ]);

        $perPage = $request->per_page ?? 10;

        $query = User::query()
            ->with(['services','languages', 'addonServices', 'professionalDetail', 'media','availabilitySlots','licenseTypes',
            'serviceUsecases.serviceUseCase'])
            ->whereHas('professionalDetail', function ($q) use ($request) {
                $q->where('service_category_id', $request->service_category_id);
                if ($request->filled('teaching_mode')) {

                    $teachingMode = $request->teaching_mode;
            
                    $q->where(function ($subQ) use ($teachingMode) {
            
                        $subQ->where('teaching_mode', $teachingMode)
                            ->orWhere('teaching_mode', 3);
            
                    });
                }
            
                if ($request->filled('transmission_type')) {
            
                    $transmissionType = $request->transmission_type;
            
                    $q->where(function ($subQ) use ($transmissionType) {
            
                        $subQ->where('transmission_type', $transmissionType)
                            ->orWhere('transmission_type', 3);
            
                    });
                }
            })
            // ->when($request->service_ids, function ($q) use ($request) {
            //     $q->whereHas('services', function ($q2) use ($request) {
            //         $q2->whereIn('service_id', $request->service_ids);
            //     });
            // })
            ->when($request->service_ids, function ($q) use ($request) {

                $serviceIds = $request->service_ids;

                $q->whereHas('services', function ($q2) use ($serviceIds) {
                    $q2->whereIn('service_id', $serviceIds);
                }, '=', count($serviceIds));

            })
            ->when($request->service_with_class, function ($q) use ($request) {

                foreach ($request->service_with_class as $filter) {
            
                    $serviceId   = $filter['service_id'];
                    $subjectType = $filter['subject_type'] ?? null;
            
                    // class_names only valid for academic (subject_type = 1)
                    $classNames  = ($subjectType == 2) ? null : ($filter['class_names'] ?? null);
            
                    $q->whereHas('services', function ($q2) use ($serviceId, $classNames, $subjectType) {
            
                        $q2->where('service_id', $serviceId);
            
                        if (!is_null($subjectType)) {
                            $q2->where('subject_type', $subjectType);
                        }
            
                        if (!empty($classNames)) {
                            $q2->whereIn('class_name', $classNames);
                        }
                    });
                }
            })
            ->when($request->service_with_item, function ($q) use ($request) {

                foreach ($request->service_with_item as $filter) {
            
                    $serviceId = $filter['service_id'];
                    $itemIds   = $filter['item_ids'] ?? null;
            
                    $q->whereHas('services', function ($q2) use ($serviceId, $itemIds) {
            
                        $q2->where('service_id', $serviceId);
            
                        if (!empty($itemIds)) {
                            $q2->whereIn('service_item_id', $itemIds);
                        }
                    });
                }
            })
            ->when($request->license_type_ids, function ($q) use ($request) {

                $licenseTypeIds = $request->license_type_ids;
            
                $q->whereHas('licenseTypes', function ($q2) use ($licenseTypeIds) {
            
                    $q2->whereIn('license_type_id', $licenseTypeIds);
            
                });
            
            })
            ->when($request->material_type_ids, function ($q) use ($request) {

                $materialIds = $request->material_type_ids;
            
                $q->whereHas('providerMaterials', function ($q2) use ($materialIds) {
            
                    $q2->whereIn('material_type_id', $materialIds);
            
                });
            
            })
            ->when($request->service_usecase_ids, function ($q) use ($request) {

                $usecaseIds = $request->service_usecase_ids;
            
                $q->whereHas('serviceUsecases', function ($q2) use ($usecaseIds) {
            
                    $q2->whereIn('service_usecase_id', $usecaseIds);
            
                });
            
            })
            ->when($request->language_ids, function ($q) use ($request) {
                $q->whereHas('languages', function ($q2) use ($request) {
                    $q2->whereIn('language_id', $request->language_ids);
                });
            })->when($request->dates, function ($q) use ($request) {

                $dates      = $request->dates;
                $matchType  = $request->dates_match ?? 'all'; // 'all' = provider must be free at every listed slot
                $duration   = (int) ($request->slot_duration ?? 60);

                $applySlotConstraint = function ($subQ, $day, $time) use ($duration) {
                    // Must have an active weekly availability slot covering this day...
                    $subQ->whereHas('availabilitySlots', function ($aq) use ($day, $time) {
                        $aq->where('day', $day)->where('status', 1);

                        if ($time) {
                            $aq->where('opening_time', '<=', $time)
                            ->where('closing_time', '>=', $time);
                        }
                    });

                    // ...and must not have a confirmed/in_progress booking overlapping that slot.
                    if ($time) {
                        $subQ->whereDoesntHave('providerBookings', function ($bq) use ($time, $duration) {
                            $bq->whereIn('status', ['confirmed', 'in_progress'])
                                ->whereRaw(
                                    'start_datetime < DATE_ADD(?, INTERVAL ? MINUTE) AND end_datetime > ?',
                                    [$time, $duration, $time]
                                );
                        });
                    }
                };

                $q->where(function ($outer) use ($dates, $matchType, $applySlotConstraint) {
                    foreach ($dates as $entry) {
                        $day  = strtolower(\Carbon\Carbon::parse($entry['date'])->format('l'));
                        $time = $entry['time'] ?? null;

                        // For "time" comparisons above we need a full datetime, not a
                        // bare time, since bookings are stored as start_datetime/end_datetime.
                        $timeForQuery = $time ? $entry['date'] . ' ' . $time . ':00' : null;

                        if ($matchType === 'any') {
                            $outer->orWhere(function ($inner) use ($applySlotConstraint, $day, $timeForQuery) {
                                $applySlotConstraint($inner, $day, $timeForQuery);
                            });
                        } else {
                            $applySlotConstraint($outer, $day, $timeForQuery);
                        }
                    }
                });
            });

        // Apply distance logic ONLY if lat & long present
        if ($request->filled('latitude') && $request->filled('longitude')) {

            $lat = $request->latitude;
            $lng = $request->longitude;

            $distanceQuery = "ROUND((6371 * acos(
                cos(radians($lat)) 
                * cos(radians(users.latitude)) 
                * cos(radians(users.longitude) - radians($lng)) 
                + sin(radians($lat)) 
                * sin(radians(users.latitude))
            )), 2)";

            $query->select('*')
                ->selectRaw("$distanceQuery AS distance")
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->having('distance', '<=', 100)
                ->orderBy('distance', 'asc');

        } else {
            $query->select('*')
            ->selectRaw('NULL as distance')
            ->latest();
        }

        $users = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Users fetched successfully',
            'data' => $users
        ]);
    }

    /**
     * @OA\Get(
     *     path="/user-detail",
     *     tags={"Provider"},
     *     summary="Get User Profile",
     *     description="Fetch authenticated user profile details using Sanctum Bearer Token. This is for testing purpose.",
     *     operationId="getUserDetail",
     *
     *     security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer", example=2),
     *         description="ID of User"
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User Detail fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User Detail fetched successfully."),
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

    public function getProviderDetail(Request $request)
    {
        $user = User::find($request->user_id);
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'No User detail found'
            ], 400);
        }

        if ($user) {
            $user->setAttribute(
                'role',
                $user->hasRole('provider') ? 'provider' : 'seeker'
            );
        }

        if($user->hasRole('provider')){
            $user->load(['services.service','addonServices', 'professionalDetail','bankDetail','availabilitySlots','languages','serviceUseCases.serviceUseCase','licenseTypes.licenseType','providerMaterials.materialType']);
            $user->setRelation(
                'services',
                $this->appendAcademicClasses($user->services)
            );
        }else{
            $user->load(['languages']);

        }

        return response()->json([
            'success' => true,
            'message' => 'User details fetched successfully.',
            'data'    => $user
        ]);
    }

    private function appendAcademicClasses($services)
    {
        return $services->map(function ($service) use ($services) {

            /*
            |--------------------------------------------------------------------------
            | Academic Subject
            |--------------------------------------------------------------------------
            */

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
        }) ->unique(function ($item) {
            return $item->service_id . '-' . $item->subject_type;
        })->values();
    }
    /**
     * @OA\Get(
     *     path="/languages",
     *     summary="Retrieve paginated list of languages",
     *     description="Fetches a paginated list of available languages.",
     *     operationId="getLanguages",
     *     tags={"Provider"},
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Languages retrieved successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Languages fetched successfully."
     *             ),
     *             
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 
     *                 @OA\Property(
     *                     property="current_page",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         
     *                         @OA\Property(
     *                             property="id",
     *                             type="integer",
     *                             example=1
     *                         ),
     *                         
     *                         @OA\Property(
     *                             property="name",
     *                             type="string",
     *                             example="English"
     *                         )
     *                     )
     *                 ),
     *                 
     *                 @OA\Property(
     *                     property="last_page",
     *                     type="integer",
     *                     example=5
     *                 ),
     *                 
     *                 @OA\Property(
     *                     property="per_page",
     *                     type="integer",
     *                     example=10
     *                 ),
     *                 
     *                 @OA\Property(
     *                     property="total",
     *                     type="integer",
     *                     example=50
     *                 )
     *             )
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal Server Error.")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function getLanguages(Request $request)
    {
        try {
            $perPage = $request->per_page??10;

            $languages = Language::all();

            return response()->json([
                'success' => true,
                'message' => 'Languages fetched successfully.',
                'data'    => $languages,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while fetching languages.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/service-use-cases",
     *     summary="Retrieve service use cases",
     *     description="Fetches service use cases based on service category.",
     *     operationId="getServiceUseCases",
     *     tags={"Provider"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="service_category_id",
     *         in="query",
     *         required=true,
     *         description="Service Category ID",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Service use cases fetched successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Service use cases fetched successfully."
     *             ),
     *             
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 
     *                 @OA\Items(
     *                     type="object",
     *                     
     *                     @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example=1
     *                     ),
     *                     
     *                     @OA\Property(
     *                         property="title",
     *                         type="string",
     *                         example="Personal driver for a few hours"
     *                     ),
     *                     
     *                     @OA\Property(
     *                         property="service_category_id",
     *                         type="integer",
     *                         example=1
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=400,
     *         description="Bad request (missing parameter)",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Service category id is required.")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal Server Error.")
     *         )
     *     )
     * )
     */
    public function getServiceUseCases(Request $request)
    {
        try {

            if (!$request->service_category_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service category id is required.'
                ], 400);
            }

            $data = ServiceUseCase::where('service_category_id', $request->service_category_id)
                        ->select('id', 'title', 'service_category_id')
                        ->get();

            return response()->json([
                'success' => true,
                'message' => 'Service use cases fetched successfully.',
                'data' => $data,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/license-types",
     *     summary="Retrieve list of license types",
     *     description="Fetches all available license types.",
     *     operationId="getLicenseTypes",
     *     tags={"Provider"},
     * 
     *     @OA\Response(
     *         response=200,
     *         description="License types fetched successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="License types fetched successfully."
     *             ),
     *             
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 
     *                 @OA\Items(
     *                     type="object",
     *                     
     *                     @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example=1
     *                     ),
     *                     
     *                     @OA\Property(
     *                         property="name",
     *                         type="string",
     *                         example="LMV"
     *                     ),
     *                     
     *                     @OA\Property(
     *                         property="description",
     *                         type="string",
     *                         example="Light Motor Vehicle"
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal Server Error.")
     *         )
     *     )
     * )
     */
    public function getLicenseTypes()
    {
        try {
            $data = LicenseType::select('id', 'name', 'description')->get();

            return response()->json([
                'success' => true,
                'message' => 'License types fetched successfully.',
                'data' => $data,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/providers/search",
     *     summary="Search providers by a free-text search bar term",
     *     description="Matches the given term against provider name, service category, services, addon services, languages, license types, materials, and service usecases — any field matching is enough (OR logic), unlike /providers/by-service-category which requires exact IDs and AND logic.",
     *     tags={"Services"},
     *     operationId="searchProviders",
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=true,
     *         description="Free-text search term entered in the search bar (min 1 character).",
     *         @OA\Schema(type="string", example="Manual Driving")
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of results per page (min: 1, max: 50). Defaults to 10.",
     *         @OA\Schema(type="integer", minimum=1, maximum=50, example=10)
     *     ),
     *
     *     @OA\Parameter(
     *         name="latitude",
     *         in="query",
     *         required=false,
     *         description="Latitude of the user's current location. Required together with longitude for distance-based sorting. Only users within 100 km are returned.",
     *         @OA\Schema(type="number", format="float", example=28.6139)
     *     ),
     *
     *     @OA\Parameter(
     *         name="longitude",
     *         in="query",
     *         required=false,
     *         description="Longitude of the user's current location. Required together with latitude for distance-based sorting.",
     *         @OA\Schema(type="number", format="float", example=77.2090)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Providers fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Providers fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Paginated user results",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=50),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=101),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="latitude", type="number", format="float", nullable=true, example=28.61),
     *                         @OA\Property(property="longitude", type="number", format="float", nullable=true, example=77.20),
     *                         @OA\Property(property="distance", type="number", format="float", nullable=true, description="Distance in km from provided coordinates. NULL if coordinates not provided.", example=12.45),
     *                         @OA\Property(property="professional_detail", type="object", nullable=true),
     *                         @OA\Property(property="services", type="array", @OA\Items(type="object")),
     *                         @OA\Property(property="addon_services", type="array", @OA\Items(type="object")),
     *                         @OA\Property(property="media", type="array", @OA\Items(type="object")),
     *                         @OA\Property(property="availability_slots", type="array", @OA\Items(type="object")),
     *                         @OA\Property(property="license_types", type="array", @OA\Items(type="object")),
     *                         @OA\Property(property="service_usecases", type="array", @OA\Items(type="object"))
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The search field is required."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={"search": {"The search field is required."}}
     *             )
     *         )
     *     )
     * )
     */
    public function searchProviders(Request $request)
    {
        $request->validate([
            'search'    => 'required|string|min:1',
            'per_page'  => 'nullable|integer|min:1|max:50',
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $perPage = $request->per_page ?? 10;
        $term    = trim($request->search);

        $query = User::query()
            ->with([
                'services', 'languages', 'addonServices', 'professionalDetail', 'media',
                'availabilitySlots', 'licenseTypes', 'serviceUsecases.serviceUseCase',
                'providerMaterials',
            ])
            // Only actual providers are searchable.
            ->whereHas('professionalDetail')
            // Any one of these matching is enough — kept as a single OR'd
            // group so it stays isolated from the distance filter below.
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")

                    // Service category name (ProfessionalDetail::service_category_id).
                    // ADJUST: relation name on ProfessionalDetail — assumed "serviceCategory".
                    ->orWhereHas('professionalDetail.serviceCategory', function ($q2) use ($term) {
                        $q2->where('name', 'like', "%{$term}%");
                    })

                    // Services — User::services() is hasMany(Service::class), each
                    // row has its own service_id (FK to master_services), plus
                    // subject_type / class_name / service_item_id per your original
                    // filter code.
                    ->orWhereHas('services', function ($q2) use ($term) {
                        $q2->where('class_name', 'like', "%{$term}%")
                            // ADJUST: relation name on Service pointing to the
                            // master_services record — assumed "masterService".
                            ->orWhereHas('service', function ($q3) use ($term) {
                                $q3->where('name', 'like', "%{$term}%");
                            });
                    })

                    // Addon services — User::addonServices() is hasMany(AddonService::class).
                    // ADJUST: column name if it's not "name" (e.g. "title").
                    ->orWhereHas('addonServices', function ($q2) use ($term) {
                        $q2->where('name', 'like', "%{$term}%");
                    })

                    // Languages — User::languages() is hasMany(UserLanguage::class).
                    // ADJUST: relation name on UserLanguage to the master Language
                    // record — assumed "language".
                    ->orWhereHas('languages.language', function ($q2) use ($term) {
                        $q2->where('name', 'like', "%{$term}%");
                    })

                    // License types — User::licenseTypes() is hasMany(UserLicenseType::class).
                    // ADJUST: relation name on UserLicenseType — assumed "licenseType".
                    ->orWhereHas('licenseTypes.licenseType', function ($q2) use ($term) {
                        $q2->where('name', 'like', "%{$term}%");
                    })

                    // Provider materials — User::providerMaterials() is hasMany(ProviderMaterial::class).
                    // ADJUST: relation name on ProviderMaterial — assumed "materialType".
                    ->orWhereHas('providerMaterials.materialType', function ($q2) use ($term) {
                        $q2->where('name', 'like', "%{$term}%");
                    })

                    // Service usecases — confirmed nested relation name from your
                    // original controller's eager-load: serviceUsecases.serviceUseCase.
                    ->orWhereHas('serviceUsecases.serviceUseCase', function ($q2) use ($term) {
                        $q2->where('name', 'like', "%{$term}%");
                    });
            });

        // Apply distance logic ONLY if lat & long present — identical to
        // getUsersByServiceCategory so search results support the same
        // "near me" sorting.
        if ($request->filled('latitude') && $request->filled('longitude')) {

            $lat = $request->latitude;
            $lng = $request->longitude;

            $distanceQuery = "ROUND((6371 * acos(
                cos(radians($lat)) 
                * cos(radians(users.latitude)) 
                * cos(radians(users.longitude) - radians($lng)) 
                + sin(radians($lat)) 
                * sin(radians(users.latitude))
            )), 2)";

            $query->select('*')
                ->selectRaw("$distanceQuery AS distance")
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->having('distance', '<=', 100)
                ->orderBy('distance', 'asc');

        } else {
            $query->select('*')
                ->selectRaw('NULL as distance')
                ->latest();
        }

        $users = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Providers fetched successfully',
            'data'    => $users,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/providers/nearby",
     *     summary="List providers within 10 km of the user's location",
     *     description="Returns providers located within a 10 km radius of the given latitude/longitude, sorted by distance (nearest first). Unlike /providers/search, this endpoint has no free-text term — it is purely location-based.",
     *     tags={"Services"},
     *     operationId="getNearbyProviders",
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\Parameter(
     *         name="latitude",
     *         in="query",
     *         required=true,
     *         description="Latitude of the user's current location.",
     *         @OA\Schema(type="number", format="float", example=28.6139)
     *     ),
     *
     *     @OA\Parameter(
     *         name="longitude",
     *         in="query",
     *         required=true,
     *         description="Longitude of the user's current location.",
     *         @OA\Schema(type="number", format="float", example=77.2090)
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of results per page (min: 1, max: 50). Defaults to 10.",
     *         @OA\Schema(type="integer", minimum=1, maximum=50, example=10)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Nearby providers fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Nearby providers fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Paginated user results",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=50),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=101),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="latitude", type="number", format="float", nullable=true, example=28.61),
     *                         @OA\Property(property="longitude", type="number", format="float", nullable=true, example=77.20),
     *                         @OA\Property(property="distance", type="number", format="float", description="Distance in km from provided coordinates.", example=4.32),
     *                         @OA\Property(property="professional_detail", type="object", nullable=true),
     *                         @OA\Property(property="services", type="array", @OA\Items(type="object")),
     *                         @OA\Property(property="addon_services", type="array", @OA\Items(type="object")),
     *                         @OA\Property(property="media", type="array", @OA\Items(type="object")),
     *                         @OA\Property(property="availability_slots", type="array", @OA\Items(type="object")),
     *                         @OA\Property(property="license_types", type="array", @OA\Items(type="object")),
     *                         @OA\Property(property="service_usecases", type="array", @OA\Items(type="object"))
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The latitude field is required."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={"latitude": {"The latitude field is required."}}
     *             )
     *         )
     *     )
     * )
     */
    public function getNearbyProviders(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'per_page'  => 'nullable|integer|min:1|max:50',
        ]);

        $perPage = $request->per_page ?? 10;
        $lat     = $request->latitude;
        $lng     = $request->longitude;

        // Fixed radius for this endpoint — kept as a local constant rather than
        // a request param, since "nearby" is defined as 10 km by spec.
        $radiusKm = 10;

        $distanceQuery = "ROUND((6371 * acos(
            cos(radians($lat)) 
            * cos(radians(users.latitude)) 
            * cos(radians(users.longitude) - radians($lng)) 
            + sin(radians($lat)) 
            * sin(radians(users.latitude))
        )), 2)";

        $users = User::query()
            ->with([
                'services', 'professionalDetail'
            ])
           
            // Only actual providers are returned.
            ->whereHas('professionalDetail')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select('users.*')
             ->selectSub(
                Review::query()
                    ->selectRaw('COALESCE(AVG(rating), 0)')
                    ->whereColumn('reviews.model_id', 'users.id')
                    ->where('reviews.model_type', User::class),
                'average_rating'
            )
            ->selectRaw("$distanceQuery AS distance")
            ->having('distance', '<=', $radiusKm)
            ->orderByRaw("
                CASE availability_status
                    WHEN 1 THEN 1
                    WHEN 2 THEN 2
                    WHEN 0 THEN 3
                    ELSE 4
                END
            ")               
            ->orderByDesc('average_rating') // Highest rated first
            ->orderBy('distance', 'asc') 
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Nearby providers fetched successfully',
            'data'    => $users,
        ]);
    }

    /**
     * Generate the provider's available time slots for a given date,
     * after removing anything that overlaps a confirmed/in_progress booking.
     *
     * Shared by getAvailableSlots() and the "dates" filter/append logic in
     * getUsersByServiceCategory(), so both stay consistent.
     */
    private function generateAvailableSlotsForDate(int $providerId, Carbon $date, int $slotDuration = 60): array
    {
        $day = strtolower($date->format('l'));

        $availability = AvailabilitySlot::where('user_id', $providerId)
            ->where('day', $day)
            ->where('status', 1)
            ->first();

        if (!$availability) {
            return [];
        }

        $slots = [];
        $start = Carbon::parse($availability->opening_time);
        $end   = Carbon::parse($availability->closing_time);

        while ($start->copy()->addMinutes($slotDuration) <= $end) {
            $slots[] = [
                'start' => $start->copy()->format('H:i'),
                'end'   => $start->copy()->addMinutes($slotDuration)->format('H:i'),
            ];
            $start->addMinutes($slotDuration);
        }

        $bookings = Booking::where('provider_id', $providerId)
            ->whereDate('start_datetime', $date)
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->get();

        $availableSlots = [];

        foreach ($slots as $slot) {
            $slotStart = Carbon::parse($date->format('Y-m-d') . ' ' . $slot['start']);
            $slotEnd   = Carbon::parse($date->format('Y-m-d') . ' ' . $slot['end']);

            $isBooked = $bookings->contains(function ($booking) use ($slotStart, $slotEnd) {
                $bookingStart = Carbon::parse($booking->start_datetime);
                $bookingEnd   = Carbon::parse($booking->end_datetime);

                return $slotStart < $bookingEnd && $slotEnd > $bookingStart;
            });

            if (!$isBooked) {
                $availableSlots[] = $slot;
            }
        }

        return $availableSlots;
    }

    /**
     * @OA\Get(
     *     path="/services/recommended",
     *     summary="Get most-booked services, ranked by booking frequency",
     *     description="Aggregates BookingItem records grouped by service, ordered by how many times each was booked. Covers both regular services (service_type=0) and addon services (service_type=1). Cancelled bookings are excluded. Optionally restrict to bookings fulfilled by providers within 10 km of the given location.",
     *     tags={"Services"},
     *     operationId="getRecommendedServices",
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\Parameter(
     *         name="service_category_id",
     *         in="query",
     *         required=false,
     *         description="Restrict results to a specific service category. Only applies to regular services (service_type=0); addon services are excluded when this filter is set.",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="service_type",
     *         in="query",
     *         required=false,
     *         description="Filter by item type. 0 = regular service, 1 = addon service. Omit to include both, ranked together.",
     *         @OA\Schema(type="integer", enum={0, 1}, example=0)
     *     ),
     *
     *     @OA\Parameter(
     *         name="latitude",
     *         in="query",
     *         required=false,
     *         description="User's current latitude. Required together with longitude. When provided, only bookings fulfilled by a provider within 10 km are counted toward booking_count.",
     *         @OA\Schema(type="number", format="float", example=28.6139)
     *     ),
     *
     *     @OA\Parameter(
     *         name="longitude",
     *         in="query",
     *         required=false,
     *         description="User's current longitude. Required together with latitude.",
     *         @OA\Schema(type="number", format="float", example=77.2090)
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of results per page (min: 1, max: 50). Defaults to 10.",
     *         @OA\Schema(type="integer", minimum=1, maximum=50, example=10)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Recommended services fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Recommended services fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Paginated results",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="service_id", type="integer", example=5),
     *                         @OA\Property(property="service_type", type="integer", example=0),
     *                         @OA\Property(property="service_name", type="string", example="Manual Driving"),
     *                         @OA\Property(property="booking_count", type="integer", description="Number of times this service was booked (within 10km if lat/long given).", example=142),
     *                         @OA\Property(property="total_quantity", type="number", format="float", example=210)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The longitude field is required when latitude is present."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={"longitude": {"The longitude field is required when latitude is present."}}
     *             )
     *         )
     *     )
     * )
     */
    public function getRecommendedServices(Request $request)
    {
        $request->validate([
            'service_category_id' => 'nullable|integer|exists:service_categories,id',
            'service_type'        => 'nullable|integer|in:0,1',
            'per_page'            => 'nullable|integer|min:1|max:50',
            'latitude'            => 'nullable|numeric|between:-90,90|required_with:longitude',
            'longitude'           => 'nullable|numeric|between:-180,180|required_with:latitude',
        ]);

        $perPage  = $request->per_page ?? 10;
        $radiusKm = 10;

        $query = BookingItem::query()
            ->select([
                'booking_items.service_id',
                'booking_items.service_type',
                DB::raw('MAX(booking_items.service_name) as service_name'),
                DB::raw('COUNT(*) as booking_count'),
                DB::raw('SUM(booking_items.quantity) as total_quantity'),
            ])
            // Join bookings so we can check status + reach the provider (User).
            ->join('bookings', 'bookings.id', '=', 'booking_items.booking_id')
            ->where('bookings.status', '!=', Booking::STATUS_CANCELLED)
            ->when($request->filled('service_type'), function ($q) use ($request) {
                $q->where('booking_items.service_type', $request->service_type);
            })
            ->when($request->filled('service_category_id'), function ($q) use ($request) {
                $q->where('booking_items.service_type', 0)
                    ->whereHas('service', function ($sq) use ($request) {
                        $sq->where('service_category_id', $request->service_category_id);
                    });
            });

        // Only count bookings whose PROVIDER is within 10 km of the given point.
        if ($request->filled('latitude') && $request->filled('longitude')) {

            $lat = $request->latitude;
            $lng = $request->longitude;

            $distanceQuery = "ROUND((6371 * acos(
                cos(radians($lat)) 
                * cos(radians(providers.latitude)) 
                * cos(radians(providers.longitude) - radians($lng)) 
                + sin(radians($lat)) 
                * sin(radians(providers.latitude))
            )), 2)";

            $query->join('users as providers', 'providers.id', '=', 'bookings.provider_id')
                ->whereNotNull('providers.latitude')
                ->whereNotNull('providers.longitude')
                ->whereRaw("$distanceQuery <= ?", [$radiusKm]);
        }

        $services = $query
            ->groupBy('booking_items.service_id', 'booking_items.service_type')
            ->orderByDesc('booking_count')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Recommended services fetched successfully',
            'data'    => $services,
        ]);
    }
}
