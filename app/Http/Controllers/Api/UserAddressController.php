<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAddressController extends Controller
{
    /**
     * @OA\Post(
     *     path="/user/address/store",
     *     summary="Store a new user address",
     *     description="Creates a new address for the authenticated user. `type_name` is required only when address_type = 3.",
     *     tags={"User Address"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={
     *                 "address_type","address","city","state",
     *                 "country","pincode"
     *             },
     *             @OA\Property(
     *                 property="address_type",
     *                 type="integer",
     *                 example=1,
     *                 description="1 = Home, 2 = Office, 3 = Other"
     *             ),
     *             
     *             @OA\Property(
     *                 property="type_name",
     *                 type="string",
     *                 example="Warehouse",
     *                 description="Required only when address_type = 3"
     *             ),
     *
     *             @OA\Property(property="address", type="string", example="221B Baker Street"),
     *             @OA\Property(property="city", type="string", example="London"),
     *             @OA\Property(property="state", type="string", example="London State"),
     *             @OA\Property(property="country", type="string", example="United Kingdom"),
     *             @OA\Property(property="pincode", type="string", example="NW16XE"),
     *
     *             @OA\Property(property="country_code", type="string", example="+44"),
     *             @OA\Property(property="phone_number", type="string", example="9876543210"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="name", type="string", example="John Watson"),
     *
     *             @OA\Property(property="latitude", type="string", example="51.523767"),
     *             @OA\Property(property="longitude", type="string", example="-0.1585557"),
     *
     *             @OA\Property(property="is_default",type="boolean",example=true,
     *               description="true = default, false = non-default")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Address saved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Address added successfully."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'address_type'  => 'required|in:1,2,3',
            'type_name'     => 'nullable|string|max:255',
            'address'       => 'required|string',
            'city'          => 'required|string',
            'state'         => 'nullable|string',
            'country'       => 'required|string',
            'pincode'       => 'required|string|max:20',
            'latitude'       => 'nullable|string|max:20',
            'longitude'       => 'nullable|string|max:20',
            'country_code'  => 'nullable|string|max:10',
            'phone_number'  => 'nullable|string|max:20',
            'email'         => 'nullable|email',
            'name'          => 'nullable|string',
            'is_default'    => 'boolean',
        ]);
        //Make address_type unique is case of 1 or 2 
        if (in_array($validated['address_type'], [1, 2])) {
            $existingAddress = UserAddress::where('user_id', $user->id)
                ->where('address_type', $validated['address_type'])
                ->first();

            if ($existingAddress) {
                return response()->json([
                    'status' => false,
                    'message' => 'You already have an address of this type. Please update the existing address or choose a different type.'
                ], 400);
            }
        }
        
        // Make the first address default automatically
        $hasAddress = UserAddress::where('user_id', $user->id)->exists();

        if (! $hasAddress) {
            $validated['is_default'] = true;
        } elseif (!empty($validated['is_default'])) {
            // If user marks this address as default, unset previous defaults
            UserAddress::where('user_id', $user->id)
                ->update(['is_default' => false]);
        } else {
            $validated['is_default'] = false;
        }

        $validated['user_id'] = $user->id;
        $address = UserAddress::create($validated);

        return response()->json([
            'status'  => true,
            'message' => 'Address added successfully',
            'data'    => $address,
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/user/address/update/{id}",
     *     summary="Update an existing user address",
     *     description="Updates a user's saved address. `type_name` is required only when address_type = 3. User must be authenticated.",
     *     tags={"User Address"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the address to update",
     *         @OA\Schema(type="integer", example=12)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="address_type",
     *                 type="integer",
     *                 example=1,
     *                 description="1 = Home, 2 = Office, 3 = Other"
     *             ),
     *
     *             @OA\Property(
     *                 property="type_name",
     *                 type="string",
     *                 example="Warehouse",
     *                 nullable=true,
     *                 description="Required only when address_type = 3"
     *             ),
     *
     *             @OA\Property(property="address", type="string", example="221B Baker Street"),
     *             @OA\Property(property="city", type="string", example="London"),
     *             @OA\Property(property="state", type="string", example="London State"),
     *             @OA\Property(property="country", type="string", example="United Kingdom"),
     *             @OA\Property(property="pincode", type="string", example="NW16XE"),
     *
     *             @OA\Property(property="country_code", type="string", example="+44"),
     *             @OA\Property(property="phone_number", type="string", example="9876543210"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="name", type="string", example="John Watson"),
     *
     *             @OA\Property(property="latitude", type="string", example="51.523767"),
     *             @OA\Property(property="longitude", type="string", example="-0.1585557"),
     *
     *             @OA\Property(
     *                 property="is_default",
     *                 type="boolean",
     *                 example=true,
     *                 description="true = set as default, false = not default"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Address updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Address updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Address not found or does not belong to user",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Address not found or does not belong to user")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        // Find address
        $address = UserAddress::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$address) {
            return response()->json([
                'status' => false,
                'message' => 'Address not found or does not belong to user'
            ], 404);
        }

        // Validation
        $validated = $request->validate([
            'address_type' => 'required|in:1,2,3',
            'type_name' => 'required_if:address_type,3|nullable|string|max:255',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
            'pincode' => 'required|string',
            'country_code' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'email' => 'nullable|email',
            'name' => 'nullable|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'is_default' => 'boolean',
        ]);

        // Handle default address
        if (!empty($validated['is_default'])) {
            // User wants to make this address default
            UserAddress::where('user_id', $user->id)
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);

            $validated['is_default'] = true;
        } else {
            // Prevent removing the only default address
            if ($address->is_default) {
                $hasAnotherDefault = UserAddress::where('user_id', $user->id)
                    ->where('id', '!=', $address->id)
                    ->where('is_default', true)
                    ->exists();

                if (! $hasAnotherDefault) {
                    $validated['is_default'] = true;
                }
            } else {
                $validated['is_default'] = false;
            }
        }

        // Update address
        $address->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Address updated successfully',
            'data' => $address
        ]);
    }

    /**
     * @OA\Put(
     *     path="/user/address/default/{id}",
     *     summary="Set an address as default",
     *     description="Marks the specified address as the default address for the authenticated user. All other user addresses are automatically set to non-default.",
     *     tags={"User Address"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the address to set as default",
     *         @OA\Schema(type="integer", example=12)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Default address updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Default address updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Address not found or does not belong to user",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Address not found or does not belong to user")
     *         )
     *     )
     * )
     */
    public function makeDefault($id)
    {
        $user = Auth::user();

        // Check if address belongs to user
        $address = UserAddress::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$address) {
            return response()->json([
                'status' => false,
                'message' => 'Address not found or does not belong to user'
            ], 404);
        }

        // Reset all other addresses
        UserAddress::where('user_id', $user->id)
            ->update(['is_default' => false]);

        // Set this address as default
        $address->update(['is_default' => true]);

        return response()->json([
            'status' => true,
            'message' => 'Default address updated successfully',
            'data' => $address
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/user/address/delete/{id}",
     *     summary="Delete a user address",
     *     description="Deletes a user's saved address. If the deleted address was default, the first remaining address becomes default automatically.",
     *     tags={"User Address"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the address to delete",
     *         @OA\Schema(type="integer", example=12)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Address deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Address deleted successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Address not found or does not belong to user",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Address not found or does not belong to user")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        $user = Auth::user();

        // Fetch address
        $address = UserAddress::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$address) {
            return response()->json([
                'status' => false,
                'message' => 'Address not found or does not belong to user'
            ], 404);
        }

        $wasDefault = $address->is_default;

        // Delete address
        $address->delete();

        // If deleted address was default, set the first address as default
        if ($wasDefault) {
            $newDefault = UserAddress::where('user_id', $user->id)->first();

            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Address deleted successfully',
        ]);
    }

    /**
     * @OA\Get(
     *     path="/user/address/list",
     *     summary="Get paginated user address list",
     *     description="Fetch paginated addresses for authenticated user. Default address appears first.",
     *     tags={"User Address"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of results per page (default 20)",
     *         @OA\Schema(type="integer", example=20)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Address list fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=20),
     *                 @OA\Property(property="total", type="integer", example=100),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=12),
     *                         @OA\Property(property="address_type", type="integer", example=1),
     *                         @OA\Property(property="type_name", type="string", example="Warehouse"),
     *                         @OA\Property(property="address", type="string", example="221B Baker Street"),
     *                         @OA\Property(property="city", type="string", example="London"),
     *                         @OA\Property(property="state", type="string", example="London State"),
     *                         @OA\Property(property="country", type="string", example="United Kingdom"),
     *                         @OA\Property(property="pincode", type="string", example="NW16XE"),
     *                         @OA\Property(property="country_code", type="string", example="+44"),
     *                         @OA\Property(property="phone_number", type="string", example="9876543210"),
     *                         @OA\Property(property="email", type="string", example="john@example.com"),
     *                         @OA\Property(property="name", type="string", example="John Watson"),
     *                         @OA\Property(property="latitude", type="string", example="51.523767"),
     *                         @OA\Property(property="longitude", type="string", example="-0.1585557"),
     *                         @OA\Property(property="is_default", type="boolean", example=true)
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function addressList(Request $request)
    {
        try {
            $userId = Auth::id();

            $addresses = UserAddress::where('user_id', $userId)
                ->orderBy('is_default', 'DESC') // default first
                ->orderBy('id', 'DESC')
                ->paginate($request->get('per_page', 20)); // default 20

            return response()->json([
                'success' => true,
                'data'    => $addresses,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/user/address/has-default",
     *     summary="Check if user has a default address",
     *     description="Returns whether the authenticated user has a default address set or not.",
     *     tags={"User Address"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Status fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Default address status fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="has_default_address", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function hasDefaultAddress()
    {
        try {
            $user = Auth::user();
    
            $defaultAddress = UserAddress::where('user_id', $user->id)
                ->where('is_default', true)
                ->first();
    
            return response()->json([
                'status'  => true,
                'message' => 'Default address status fetched successfully',
                'data'    => [
                    'has_default_address' => $defaultAddress ? true : false,
                    'default_address'     => $defaultAddress
                ]
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

}
