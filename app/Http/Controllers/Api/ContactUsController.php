<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContactUsAdminMail;
use App\Mail\ContactUsUserMail;
use App\Models\ContactUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactUsController extends Controller
{
    /**
     * @OA\Post(
     *     path="/contact-us",
     *     tags={"Contact Us"},
     *     summary="Submit Contact Us enquiry",
     *     description="Submit a contact enquiry. The enquiry is saved and an email notification is sent to the admin and a confirmation email is sent to the user.",
     *     operationId="submitContactUs",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","subject","message"},
     *
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 maxLength=255,
     *                 example="Alex Morgan"
     *             ),
     *
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 maxLength=255,
     *                 example="alex.morgan@example.com"
     *             ),
     *
     *             @OA\Property(
     *                 property="subject",
     *                 type="string",
     *                 maxLength=255,
     *                 example="Need to book service"
     *             ),
     *
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="I would like to know more about the services."
     *             ),
     *
     *             @OA\Property(
     *                 property="profile",
     *                 type="integer",
     *                 nullable=true,
     *                 maxLength=255,
     *                 example="0"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Contact enquiry submitted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Your message has been submitted successfully."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer",
     *                     example=15
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
     *                 type="object",
     *                 example={
     *                     "name": {"The name field is required."},
     *                     "email": {"The email field must be a valid email address."}
     *                 }
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
     *                 example="Something went wrong while submitting your message."
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'profile' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $contact = ContactUs::create([
                'name' => $request->name,
                'email' => $request->email,
                'subject' => $request->subject,
                'message' => $request->message,
                'profile' => $request->profile,
            ]);

            // Admin email
            $adminEmail = config('mail.admin_email');

            Mail::to($adminEmail)
                ->send(new ContactUsAdminMail($contact));

            // User confirmation email
            Mail::to($contact->email)
                ->send(new ContactUsUserMail($contact));

            return response()->json([
                'success' => true,
                'message' => 'Your message has been submitted successfully.',
                'data' => [
                    'id' => $contact->id,
                ],
            ], 201);

        } catch (\Throwable $e) {

            \Log::error('Contact Us submission failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' =>  'Something went wrong while submitting your message.',
            ], 500);
        }
    }
}