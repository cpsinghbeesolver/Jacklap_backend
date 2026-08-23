<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMediaRequest;
use App\Http\Requests\StoreMediaRequestV2;
use App\Http\Requests\StoreProfessionalDetailRequest;
use App\Models\AvailabilitySlot;
use App\Models\BankDetail;
use App\Models\IdentityType;
use App\Models\Language;
use App\Models\MasterService;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
class UserDetailsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/id-type/all",
     *     summary="Retrieve all identity types",
     *     description="Fetches a complete list of available identity types",
     *     tags={"Provider"},
     *     @OA\Response(
     *         response=200,
     *         description="Identity Types retrieved successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Id Types fetched successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 description="List of service category objects.",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Driving License"),
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getAllIdTypes(){
        $idTypes = IdentityType::orderBy('is_required','DESC')->get();
        return response()->json([
            'success' => true,
            'message' => 'Id Types fetched successfullly',
            'data'    => $idTypes
        ]);
    }

    /**
     * @OA\Post(
     *     path="/user/professional-details",
     *     summary="Create professional details with services",
     *     tags={"Provider"},
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="
     *         If service_category_id = 5 (Driver), then safety_record, transmission_type=1,2,3 and driver_details are required.
     *
     *         If service_category_id = 3 (Teaching), then:
     *         - teaching_mode is required (1=online, 2=offline, 3=both)
     *         - services[].subject_type is required (1=academic, 2=non_academic)
     *
     *         If service_category_id = 2 (Salon), then:
     *         - services[].type is required (service, package)
     *         - services[].subject_type is required (3=Male, 4=Female, 5=Both)
     *         - services[].items is required when type=service
     *         - services[].price is required when type=package
     *         - skills = comma separated specializations (e.g. Bridal Makeup,Party Makeup)
     *         - tools = comma separated products/brands (e.g. MAC,Huda Beauty,Lakme)
     *
     *
     *         ==============================
     *         SERVICE CATEGORY ID = 1
     *         ==============================
     *
     *         {
     *           'service_category_id': 1,
     *           'experience': 5,
     *           'languages': 'English, Hindi',
     *           'tools': '',
     *           'skills': '',
     *           'material_type_ids': [
     *             1,
     *             2,
     *             3
     *           ],
     *           'services': [
    *              {
     *               'name': 'Bathroom Cleaning',
     *               'price': 500,
     *               'description': 'Bathroom cleaning service',
     *               'is_default': true
     *             },
     *             {
     *               'name': 'Kitchen Cleaning',
     *               'price': 500,
     *               'description': 'Kitchen cleaning service',
     *               'is_default': true
     *             },
     *             {
     *               'name': 'Move-in / Move-Out',
     *               'price': 400,
     *               'description': 'Move-in / Move-Out service',
     *               'is_default': true,
     *               'items': [
     *                 {
     *                   'id': 1,
     *                   'name': 'Full cleaning',
     *                   'price': 250
     *                 }
     *               ]
     *             }
     *           ],
     *           'addon_services': [
     *             {
     *               'name': 'Extra Deep Cleaning',
     *               'price': 300
     *             }
     *           ]
     *         }
     *
     *         ==============================
     *         SERVICE CATEGORY ID = 4
     *         ==============================
     *
     *         {
     *           'service_category_id': 4,
     *           'experience': 5,
     *           'languages': 'English, Hindi',
     *           'tools': '',
     *           'skills': '',
     *           'services': [
     *             {
     *               'name': 'Cleaning',
     *               'price': 500,
     *               'description': 'Basic cleaning service',
     *               'is_default': true
     *             },
     *             {
     *               'name': 'Garage Cleaning',
     *               'price': 300,
     *               'description': 'Basic Garage service',
     *               'is_default': false
     *             }
     *           ],
     *           'addon_services': [
     *             {
     *               'name': 'Extra Roof Cleaning',
     *               'price': 300
     *             }
     *           ]
     *         }
     *
     *
     *         ==============================
     *         SERVICE CATEGORY ID = 5
     *         ==============================
     *
     *         {
     *           'service_category_id': 5,
     *           'experience': 5,
     *           'languages': 'English, Hindi',
     *           'skills': '',
     *           'transmission_type': 1,
     *           'safety_record': 'Clean driving history',
     *           'license_type_ids': [
     *             1,
     *             2
     *           ],
     *           'service_usecase_ids': [
     *             1,
     *             2,
     *             3
     *           ],
     *           'addon_services': [
     *             {
     *               'name': 'Extra Driving',
     *               'price': 500
     *             }
     *           ]
     *         }
     *
     *
     *         ==============================
     *         SERVICE CATEGORY ID = 3
     *         ==============================
     *
     *         {
     *           'service_category_id': 3,
     *           'experience': 5,
     *           'languages': 'English, Hindi',
     *           'skills': '',
     *           'teaching_mode': 1,
     *           'services': [
     *             {
     *               'subject_type': 1,
     *               'name': 'Math',
     *               'price': 500,
     *               'description': 'Basic Math class',
     *               'is_default': true,
     *               'classes': [
     *                 {
     *                   'class_name': 'Class 10',
     *                   'price': 500
     *                 },
     *                 {
     *                   'class_name': 'Class 12',
     *                   'price': 600
     *                 }
     *               ]
     *             },
     *             {
     *               'subject_type': 1,
     *               'name': 'Social Studies',
     *               'price': 500,
     *               'description': 'Basic Social Studies class',
     *               'is_default': false,
     *               'classes': [
     *                 {
     *                   'class_name': 'Class 9',
     *                   'price': 500
     *                 },
     *                 {
     *                   'class_name': 'Class 11',
     *                   'price': 600
     *                 }
     *               ]
     *             },
     *             {
     *               'subject_type': 2,
     *               'name': 'Music',
     *               'price': 500,
     *               'description': 'Basic Music class',
     *               'is_default': true
     *             },
     *             {
     *               'subject_type': 2,
     *               'name': 'Skating',
     *               'price': 400,
     *               'description': 'Basic skating class',
     *               'is_default': false
     *             }
     *           ],
     *           'addon_services': [
     *             {
     *               'name': 'Extra Teaching class',
     *               'price': 300
     *             }
     *           ]
     *         }
     *
     *
     *         ==============================
     *         SERVICE CATEGORY ID = 2 (Salon)
     *         ==============================
     *
     *         {
     *           'service_category_id': 2,
     *           'experience': 3,
     *           'languages': 'English, Hindi',
     *           'skills': 'Bridal Makeup,Party Makeup,Ramp/Show Makeup',
     *           'tools': 'MAC,Huda Beauty,Lakme,Maybelline',
     *           'services': [
     *             {
     *               'service_id': 1,
     *               'name': 'Hair Services',
     *               'type': 'service',
     *               'subject_type': 3,
     *               'description': 'Male hair services',
     *               'is_default': true,
     *               'items': [
     *                 { 'id': 1, 'name': 'Haircut', 'price': 500 },
     *                 { 'id': 2, 'name': 'Beard Trim', 'price': 300 }
     *               ]
     *             },
     *             {
     *               'service_id': 5,
     *               'name': 'Basic Grooming Package',
     *               'type': 'package',
     *               'subject_type': 3,
     *               'price': 1500,
     *               'description': 'Haircut + Beard Trim combo',
     *               'is_default': true,
     *               'min_people': null,
     *               'max_people': null
     *             },
     *             {
     *               'service_id': 9,
     *               'name': 'Group Booking Package',
     *               'type': 'package',
     *               'subject_type': 4,
     *               'price': 2000,
     *               'description': 'Group discount package',
     *               'is_default': true,
     *               'min_people': 3,
     *               'max_people': 5
     *             }
     *           ],
     *           'addon_services': [
     *             {
     *               'name': 'Home Visit Charge',
     *               'price': 200
     *             }
     *           ]
     *         }
     *
     *         ",
     *         @OA\JsonContent(
     *             required={"service_category_id","experience","languages","services"},
     *
     *             @OA\Property(property="service_category_id", type="integer", example=5),
     *             @OA\Property(property="experience", type="integer", example=5),
     *             @OA\Property(property="languages", type="string", example="English, Hindi"),
     *             @OA\Property(property="tools", type="string", example="", description="Comma separated brands for salon (e.g. MAC,Huda Beauty). Other categories: free text."),
     *             @OA\Property(property="skills", type="string", example="", description="Comma separated specializations for salon (e.g. Bridal Makeup,Party Makeup). Other categories: free text."),
     *
     *             @OA\Property(
     *                 property="transmission_type",
     *                 type="integer",
     *                 enum={0,1,2},
     *                 description="0=automatic, 1=manual, 2=both (Required when service_category_id = 5)",
     *                 example=1
     *             ),
     *
     *             @OA\Property(
     *                 property="teaching_mode",
     *                 type="integer",
     *                 enum={1,2,3},
     *                 description="0=automatic, 1=manual, 2=both (Required when service_category_id = 5)",
     *                 example=1
     *             ),
     *
     *             @OA\Property(
     *                 property="safety_record",
     *                 type="string",
     *                 description="Required when service_category_id = 5",
     *                 example="Clean driving history"
     *             ),
     *
     *             @OA\Property(
     *                 property="license_type_ids",
     *                 type="array",
     *                 description="Required when service_category_id = 5",
     *                 @OA\Items(type="integer"),
     *                 example={1,2}
     *             ),
     *
     *             @OA\Property(
     *                 property="service_usecase_ids",
     *                 type="array",
     *                 description="Required when service_category_id = 5",
     *                 @OA\Items(type="integer"),
     *                 example={1,2,3}
     *             ),
     *
     *             @OA\Property(
     *                 property="material_type_ids",
     *                 type="array",
     *                 description="Required when service_category_id = 1",
     *                 @OA\Items(type="integer"),
     *                 example={1,2,3}
     *             ),
     *
     *             @OA\Property(
     *                 property="services",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"name","price"},
     *
     *                     @OA\Property(
     *                         property="service_id",
     *                         type="integer",
     *                         description="master_services.id — required when service_category_id = 2",
     *                         example=1
     *                     ),
     *
     *                     @OA\Property(
     *                         property="type",
     *                         type="string",
     *                         enum={"service","package"},
     *                         description="Required when service_category_id = 2. service=individual items, package=bundle with fixed price",
     *                         example="service"
     *                     ),
     *
     *                     @OA\Property(
     *                         property="subject_type",
     *                         type="integer",
     *                         enum={1,2,3,4,5},
     *                         description="Category 3: 1=academic, 2=non_academic | Category 2 (Salon): 3=Male, 4=Female, 5=Both",
     *                         example=1
     *                     ),
     *
     *                     @OA\Property(property="name", type="string", example="Math"),
     *                     @OA\Property(property="price", type="number", format="float", example=500),
     *                     @OA\Property(property="description", type="string", example="Basic math service"),
     *                     @OA\Property(property="is_default", type="boolean", example=true),
     *
     *                     @OA\Property(
     *                         property="min_people",
     *                         type="integer",
     *                         nullable=true,
     *                         description="For group packages only (service_category_id = 2, type = package)",
     *                         example=3
     *                     ),
     *
     *                     @OA\Property(
     *                         property="max_people",
     *                         type="integer",
     *                         nullable=true,
     *                         description="For group packages only (service_category_id = 2, type = package)",
     *                         example=5
     *                     ),
     *
     *                     @OA\Property(
     *                         property="items",
     *                         type="array",
     *                         description="Required when service_category_id = 1 or (service_category_id = 2 and type = service)",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="Floor Cleaning"),
     *                             @OA\Property(property="price", type="number", format="float", example=200)
     *                         )
     *                     ),
     *
     *                     @OA\Property(
     *                         property="classes",
     *                         type="array",
     *                         description="Required for academic subjects (service_category_id = 3, subject_type = 1)",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="class_name", type="string", example="Class 10"),
     *                             @OA\Property(property="price", type="number", format="float", example=500)
     *                         )
     *                     )
     *                 )
     *             ),
     *
     *             @OA\Property(
     *                 property="addon_services",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"name","price"},
     *                     @OA\Property(property="name", type="string", example="Extra Consultation"),
     *                     @OA\Property(property="price", type="number", format="float", example=300)
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Professional details created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Professional details created successfully.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Professional details already exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Professional details already exist.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="This action is unauthorized.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={
     *                     "service_category_id": {"The selected service category id is invalid."},
     *                     "driver_details.0.service_usecase_id": {"The service usecase id field is required."}
     *                 }
     *             )
     *         )
     *     )
     * )
     */
    public function storeProfessionalDetails(StoreProfessionalDetailRequest $request)
    {
        $user = $request->user(); 
        $validated = $request->validated();

        // Create or Update Professional Detail
        $user->professionalDetail()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'service_category_id' => $validated['service_category_id'],
                'price' => $validated['price']??0.00,
                'experience' => $validated['experience']??0,
                'languages' => $validated['languages']??NULL,
                'tools' => $validated['tools']??NULL,
                'skills' => $validated['skills']??NULL,
                'teaching_mode' => $validated['teaching_mode']??NULL,
                'safety_record' => $validated['safety_record']??NULL,
                'transmission_type' => $validated['transmission_type']??0,
            ]
        );

        // Handle Languages (Sync)
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

        // Handle Services (Sync)
        if (!empty($validated['services'])) {

            $serviceIds = [];
            $service_type = 'service';
            foreach ($validated['services'] as $service) {
                if($validated['service_category_id'] == 3){
                    $service_type = 'subject';
                }
                // Check or create master service
                $masterService = MasterService::firstOrCreate(
                    [
                        'name' => $service['name'],
                        'service_category_id' => $validated['service_category_id']
                    ],
                    [
                        'subject_type' => $service['subject_type'] ?? 1,
                        'description' => $service['description'] ?? null,
                        'is_default'  => $service['is_default'] ?? false,
                        'status'      => 0,
                        'type'        => $service_type      
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | SALON CATEGORY (id = 2)
                |--------------------------------------------------------------------------
                */
                if ($validated['service_category_id'] == 2) {

                    $serviceType = $service['type'] ?? 'service';

                    // Package — one row, no service_item_id
                    if ($serviceType === 'package') {

                        $userService = $user->services()->updateOrCreate(
                            [
                                'service_id'      => $masterService->id,
                                'service_item_id' => null,
                                'type'            => 'package',
                            ],
                            [
                                'name'                => $service['name'],
                                'description'         => $service['description'] ?? null,
                                'is_default'          => $service['is_default'] ?? true,
                                'subject_type'        => $service['subject_type'] ?? null,
                                'price'               => $service['price'] ?? 0,
                                'min_people'          => $service['min_people'] ?? null,
                                'max_people'          => $service['max_people'] ?? null,
                                'service_category_id' => $validated['service_category_id'],
                            ]
                        );

                        $serviceIds[] = $userService->id;
                        continue;
                    }

                    // Service — one row per item
                    if ($serviceType === 'service' && !empty($service['items'])) {

                        foreach ($service['items'] as $item) {

                            $userService = $user->services()->updateOrCreate(
                                [
                                    'service_id'      => $masterService->id,
                                    'service_item_id' => $item['id'],
                                ],
                                [
                                    'name'                => $item['name'],
                                    'description'         => $service['description'] ?? null,
                                    'is_default'          => $service['is_default'] ?? true,
                                    'subject_type'        => $service['subject_type'] ?? null,
                                    'type'                => 'service',
                                    'price'               => $item['price'],
                                    'service_category_id' => $validated['service_category_id'],
                                ]
                            );

                            $serviceIds[] = $userService->id;
                        }
                        continue;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Teaching Category + Academic Subject
                |--------------------------------------------------------------------------
                */

                if (
                    $validated['service_category_id'] == 3 &&
                    ($service['subject_type'] ?? 0) == 1
                ) {

                    if (!empty($service['classes'])) {

                        foreach ($service['classes'] as $class) {

                            $userService = $user->services()->updateOrCreate(
                                [
                                    'service_id' => $masterService->id,
                                    'class_name' => $class['class_name']
                                ],
                                [
                                    'name' => $service['name'],
                                    'price' => $class['price'],
                                    'description' => $service['description'] ?? null,
                                    'is_default' => $service['is_default'] ?? false,
                                    'subject_type' => 1,
                                    'class_name' => $class['class_name'],
                                    'service_category_id' => $validated['service_category_id'],
                                ]
                            );

                            $serviceIds[] = $userService->id;
                        }
                    }

                    continue;
                }

                if (
                    $validated['service_category_id'] == 1 &&
                    !empty($service['items'])
                ) {

                    foreach ($service['items'] as $item) {

                        $userService = $user->services()->updateOrCreate(
                            [
                                'service_id' => $masterService->id,
                                'service_item_id' => $item['id']
                            ],
                            [
                                'name' => $item['name'],
                                'price' => $item['price'],
                                'description' => $service['description'] ?? null,
                                'is_default' => $service['is_default'] ?? false,
                                //'subject_type' => 1,
                                //'class_name' => $class['class_name'],
                                'service_category_id' => $validated['service_category_id'],
                            ]
                        );

                        $serviceIds[] = $userService->id;
                    }
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Default Existing Flow
                |--------------------------------------------------------------------------
                */

                $userService = $user->services()->updateOrCreate(
                    [
                        'service_id' => $masterService->id
                    ],
                    [
                        'name' => $service['name'],
                        'price' => $service['price'] ?? 0,
                        'description' => $service['description'] ?? null,
                        'is_default' => $service['is_default'] ?? false,
                        'subject_type' => $service['subject_type'] ?? 1,
                        'service_category_id' => $validated['service_category_id'],
                    ]
                );

                $serviceIds[] = $userService->id;
            }

            // Remove any user services that are no longer selected
            $user->services()
                ->whereNotIn('id', $serviceIds)
                ->delete();
        }

        // Handle Addon Services (Sync)
        if (!empty($validated['addon_services'])) {

            $addonIds = [];

            foreach ($validated['addon_services'] as $addon) {

                $addonService = $user->addonServices()->updateOrCreate(
                    [
                        'name' => $addon['name'], // unique per user
                    ],
                    [
                        'price' => $addon['price'],
                        'status' => 1,
                        'type' => 'service',
                        'description' => null,
                        'is_default' => false,
                        'service_category_id' => $validated['service_category_id'] ?? null,
                    ]
                );

                $addonIds[] = $addonService->id;
            }

            // Delete removed addon services
            $user->addonServices()
                ->whereNotIn('id', $addonIds)
                ->where('type', 'service')
                ->delete();
        }

        /*
        |--------------------------------------------------------------------------
        | Driver License Types
        |--------------------------------------------------------------------------
        */

        if (
            $validated['service_category_id'] == 5 &&
            !empty($validated['license_type_ids'])
        ) {

            $licenseIds = [];

            foreach ($validated['license_type_ids'] as $licenseTypeId) {

                $license = $user->licenseTypes()->updateOrCreate(
                    [
                        'license_type_id' => $licenseTypeId
                    ],
                    []
                );

                $licenseIds[] = $licenseTypeId;
            }

            $user->licenseTypes()
                ->whereNotIn('license_type_id', $licenseIds)
                ->delete();
        }

        if (
            $validated['service_category_id'] == 1 &&
            !empty($validated['material_type_ids'])
        ) {

            $materialIds = [];

            foreach ($validated['material_type_ids'] as $materialTypeId) {

                $material = $user->providerMaterials()->updateOrCreate(
                    [
                        'material_type_id' => $materialTypeId
                    ],
                    [
                        'service_category_id' => $validated['service_category_id']
                    ]
                );

                $materialIds[] = $materialTypeId;
            }
            // Delete old materials not present in latest request
            $user->providerMaterials()
                ->whereNotIn('material_type_id', $materialIds)
                ->delete();
        }

        /*
        |--------------------------------------------------------------------------
        | Driver Service Usecases
        |--------------------------------------------------------------------------
        */

        if (
            $validated['service_category_id'] == 5 &&
            !empty($validated['service_usecase_ids'])
        ) {

            $usecaseIds = [];

            foreach ($validated['service_usecase_ids'] as $usecaseId) {

                $usecase = $user->serviceUsecases()->updateOrCreate(
                    [
                        'service_usecase_id' => $usecaseId
                    ],
                    []
                );

                $usecaseIds[] = $usecaseId;
            }

            $user->serviceUsecases()
                ->whereNotIn('service_usecase_id', $usecaseIds)
                ->delete();
        }

        if($user->profile_step <> 4){
            // Update profile step
            $user->update([
                'profile_step' => 1
            ]);
        }


        return response()->json([
            'success' => true,
            'message' => 'Professional details saved successfully.'
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/user/media-upload",
     *     summary="Upload user media documents",
     *     tags={"Provider"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"identity_type_id", "id_front", "id_back", "profile_photo"},
     *                 
     *                 @OA\Property(property="identity_type_id", type="integer", example=1),
     *                 
     *                 @OA\Property(property="id_front", type="string", format="binary"),
     *                 @OA\Property(property="id_back", type="string", format="binary"),
     *                 
     *                 @OA\Property(
     *                     property="certificates[]",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary"),
     *                     description="Upload multiple certificates"
     *                 ),
     *                 
     *                 @OA\Property(property="profile_photo", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Media uploaded successfully"
     *     )
     * )
     */
    public function storeMediaData(StoreMediaRequest $request){
        $user = $request->user();
        $user = User::find($user->id);
        $folderPath = 'media/userid' . $user->id;
    
        if (Storage::disk('s3')->exists($folderPath)) {
            Storage::disk('s3')->deleteDirectory($folderPath);
        }
    
        $certificatePath = null;
    
        if ($request->hasFile('certificate')) {
            $certificatePath = $request->file('certificate')->store($folderPath, 's3');
        }
    
        $media = Media::updateOrCreate([
            'user_id' => $user->id
        ],
        [
            'identity_type_id' => $request->identity_type_id,
            'id_front'         => $request->file('id_front')->store($folderPath, 's3'),
            'id_back'          => $request->file('id_back')->store($folderPath, 's3'),
            'certificate'      => $certificatePath,
            // 'profile_photo'    => $request->file('profile_photo')->store($folderPath, 's3')
        ]);

        // ==============================
        // (MULTIPLE CERTIFICATES)
        // ==============================
        $allFiles = $request->allFiles();
        $certificateKeys = preg_grep('/^certificate_\d+$/', array_keys($allFiles));
        if (!empty($certificateKeys)) {
    
            // delete old certificate files (only morph ones)
            foreach ($media->certificates as $file) {
                if (Storage::disk('s3')->exists($file->path)) {
                    Storage::disk('s3')->delete($file->path);
                }
            }
    
            $media->certificates()->delete();
            $index = 0;
            foreach ($certificateKeys as $key) {
                $file = $request->file($key);
                $path = $file->store($folderPath, 's3');

                $media->certificates()->create([
                    'original_name' => $file->getClientOriginalName(),
                    'path'          => $path,
                    'mime_type'     => $file->getClientMimeType(),
                    'size'          => $file->getSize(),
                    'is_primary'    => false,
                    'sort_order'    => $index++
                ]);
            }
        }
        $path = $request->file('profile_photo')->store('profile_images', 's3');

        $data = [
            'image' => $path,
        ];
        
        if ($user->profile_step != 4) {
            $data['profile_step'] = 3;
        }
        
        $user->update($data);
    
        return response()->json([
            'success' => true,
            'message' => 'Media uploaded successfully'
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/user/media-upload-v2",
     *     summary="Upload provider media documents",
     *     tags={"Provider"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="documents[0][identity_type_id]", type="integer", example=1),
     *                 @OA\Property(property="documents[0][files][]",    type="array", @OA\Items(type="string", format="binary")),
     *                 @OA\Property(property="documents[0][file_ids][]", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="documents[1][identity_type_id]", type="integer", example=2),
     *                 @OA\Property(property="documents[1][files][]",    type="array", @OA\Items(type="string", format="binary")),
     *                 @OA\Property(property="documents[1][file_ids][]", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="certificate_id",   type="integer", example=3),
     *                 @OA\Property(property="certificates[]",   type="array", @OA\Items(type="string", format="binary")),
     *                 @OA\Property(property="profile_photo",    type="string", format="binary")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=201, description="Media uploaded successfully",
     *         @OA\JsonContent(@OA\Property(property="success", type="boolean", example=true),
     *                         @OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function storeMediaDataV2(StoreMediaRequestV2 $request)
    {
        $user       = $request->user();
        $folderPath = 'media/userid' . $user->id;
        $documents  = $request->input('documents', []);

        // ── 1. Process each document group ───────────────────────────────────
        foreach ($documents as $index => $documentData) {

            $identityType  = IdentityType::findOrFail($documentData['identity_type_id']);
            $uploadedFiles = $request->file("documents.$index.files", []);
            $uploadedFiles = is_array($uploadedFiles) ? $uploadedFiles : [];

            // Skip optional types when nothing was sent at all
            if (!$identityType->is_required && empty($uploadedFiles) && empty($documentData['file_ids'] ?? [])) {
                continue;
            }

            $media = Media::updateOrCreate(
                ['user_id' => $user->id, 'identity_type_id' => $identityType->id],
                []
            );

            // IDs the client wants to KEEP (cast to int so strict comparison works)
            $keepFileIds = collect($documentData['file_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->toArray();

            // ── Delete files NOT in the keep list ────────────────────────────
            // BUG FIX: when keepFileIds is empty we must delete ALL existing files.
            // The previous code had a no-op second when() that left files untouched.
            $deleteQuery = $media->files();

            if (!empty($keepFileIds)) {
                $deleteQuery->whereNotIn('id', $keepFileIds);
            }
            // (if keepFileIds is empty, no whereNotIn → query targets ALL files)

            foreach ($deleteQuery->get() as $oldFile) {
                if ($oldFile->path && Storage::disk('s3')->exists($oldFile->path)) {
                    Storage::disk('s3')->delete($oldFile->path);
                }
                $oldFile->delete();
            }

            // ── Store new files ───────────────────────────────────────────────
            foreach ($uploadedFiles as $fileIndex => $file) {
                $path = $file->store(
                    $folderPath . '/identity_' . $identityType->id,
                    's3'
                );

                $media->files()->create([
                    'original_name' => $file->getClientOriginalName(),
                    'path'          => $path,
                    'mime_type'     => $file->getMimeType(),
                    'size'          => $file->getSize(),
                    'is_primary'    => $fileIndex === 0,
                    'sort_order'    => $fileIndex,
                ]);
            }
        }

        // ── 2. Certificates (legacy flat fields) ─────────────────────────────
        if ($request->filled('certificate_id') && $request->hasFile('certificates')) {

            $certificateId = (int) $request->certificate_id;

            $media = Media::updateOrCreate(
                ['user_id' => $user->id, 'identity_type_id' => $certificateId],
                []
            );

            // Delete ALL old certificate files (no keep-list for this path)
            foreach ($media->files as $file) {
                if ($file->path && Storage::disk('s3')->exists($file->path)) {
                    Storage::disk('s3')->delete($file->path);
                }
                $file->delete();
            }

            foreach ($request->file('certificates') as $fileIndex => $file) {
                $path = $file->store(
                    $folderPath . '/certificate_' . $certificateId,
                    's3'
                );

                $media->files()->create([
                    'original_name' => $file->getClientOriginalName(),
                    'path'          => $path,
                    'mime_type'     => $file->getMimeType(),
                    'size'          => $file->getSize(),
                    'is_primary'    => $fileIndex === 0,
                    'sort_order'    => $fileIndex,
                ]);
            }
        }

        // ── 3. Profile photo ─────────────────────────────────────────────────
        if ($request->hasFile('profile_photo')) {
            if (!empty($user->image) && Storage::disk('s3')->exists($user->image)) {
                Storage::disk('s3')->delete($user->image);
            }

            $user->image = $request->file('profile_photo')
                ->store('profile_images', 's3');
        }

        // ── 4. Advance profile step ───────────────────────────────────────────
        if ($user->profile_step != 4) {
            $user->profile_step = 3;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Media uploaded successfully',
        ], 201);
    }

    public function storeMediaData_old(StoreMediaRequest $request){
        $user = $request->user();

        $folderPath = 'media/userid' . $user->id;

        if (Storage::disk('s3')->exists($folderPath)) {
            Storage::disk('s3')->deleteDirectory($folderPath);
        }

        $certificatePath = null;

        if ($request->hasFile('certificate')) {
            $certificatePath = $request->file('certificate')->store($folderPath, 's3');
        }

        Media::updateOrCreate([
            'user_id' => $user->id
        ],
        [
            'identity_type_id' => $request->identity_type_id,
            'id_front'         => $request->file('id_front')->store($folderPath, 's3'),
            'id_back'          => $request->file('id_back')->store($folderPath, 's3'),
            'certificate'      => $certificatePath,
            'profile_photo'    => $request->file('profile_photo')->store($folderPath, 's3')
        ]);
        if($user->profile_step <> 4){
            $user->update([
                'profile_step' => 3
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Media uploaded successfully'
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/user/bank-details",
     *     summary="Store or update user bank details",
     *     tags={"Provider"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"account_holder_name","bank_name","account_number","ifsc_code"},
     *             @OA\Property(property="account_holder_name", type="string", example="Nitish Kumar"),
     *             @OA\Property(property="bank_name", type="string", example="HDFC Bank"),
     *             @OA\Property(property="account_number", type="string", example="123456789012"),
     *             @OA\Property(property="ifsc_code", type="string", example="HDFC0001234"),
     *             @OA\Property(property="account_type", type="string", example="savings")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bank details saved successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function storeBankDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_holder_name' => 'required|string|max:255',
            'bank_name'           => 'required|string|max:255',
            'account_number'      => 'required|digits_between:9,18',
            'ifsc_code'           => 'required|string|max:20',
            'account_type'        => 'nullable|in:savings,current'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        BankDetail::updateOrCreate(
            ['user_id' => $user->id],
            [
                'account_holder_name' => $request->account_holder_name,
                'bank_name'           => $request->bank_name,
                'account_number'      => $request->account_number,
                'ifsc_code'           => $request->ifsc_code,
                'account_type'        => $request->account_type,
            ]
        );
        if($user->profile_step <> 4){
            $user->update([
                'profile_step' => 4
            ]);
        }

        $response = response()->json([
            'success' => true,
            'message' => 'Bank details saved successfully'
        ], 200);

        return $response->withCookie(cookie()->forget('is_auth_incomplete'));
    }

    /**
     * @OA\Post(
     *     path="/user/availability-slots",
     *     summary="Add or update availability slots",
     *     description="Stores or updates availability time slots for the authenticated user.",
     *     tags={"Provider"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             
     *             @OA\Property(
     *                 property="availability_slots",
     *                 type="array",
     *                 
     *                 @OA\Items(
     *                     type="object",
     *                     
     *                     @OA\Property(property="day", type="string", example="monday"),
     *                     @OA\Property(property="status", type="integer", example=1),
     *                     @OA\Property(property="opening_time", type="string", example="10:00"),
     *                     @OA\Property(property="closing_time", type="string", example="20:00")
     *                 )
     *             ),
     *             @OA\Property(
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
     *         description="Availability slots saved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Availability slots saved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function storeAvailabilitySlots(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'on_call_availability'              => 'boolean',
            'availability_slots'                => 'required|array',
            'availability_slots.*.day'          => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'availability_slots.*.status'       => 'required|integer|in:0,1',
            'availability_slots.*.opening_time' => 'nullable|required_if:availability_slots.*.status,1|date_format:H:i',
            'availability_slots.*.closing_time' => 'nullable|required_if:availability_slots.*.status,1|date_format:H:i|after:availability_slots.*.opening_time',
        ]);

        AvailabilitySlot::where('user_id', $user->id)->delete();

        $data = [];
        foreach ($validated['availability_slots'] as $slot) {
            $data[] = [
                'user_id'       => $user->id,
                'day'           => $slot['day'],
                'status'        => $slot['status'],
                'opening_time'  => $slot['status'] ? $slot['opening_time'] : null,
                'closing_time'  => $slot['status'] ? $slot['closing_time'] : null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        AvailabilitySlot::insert($data);

        $user->update(['on_call_availability' => $request->on_call_availability]);
        
        if($user->profile_step <> 4){
            // Update profile step
            $user->update([
                'profile_step' => 2
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Availability slots replaced successfully.',
            'data'    => AvailabilitySlot::where('user_id', $user->id)->get(),
        ]);
    }
}

