<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Admin\BookingController as BController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ServiceCategoryController;
use App\Http\Controllers\Api\UserAddressController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\UserDetailsController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SettingsController;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\EnquiryController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\ContactUsController;
use App\Http\Controllers\Api\BookingConcernController;
use App\Http\Controllers\Api\StripeConnectController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\BookingAddonController;
use App\Http\Controllers\Api\ProviderPayoutController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/media-proxy', function (Illuminate\Http\Request $request) {
    $path = $request->query('path');
    if (!Storage::disk('s3')->exists($path)) abort(404);
    return Storage::disk('s3')->response($path);
})->middleware('auth:sanctum');
Route::post('/contact-us', [ContactUsController::class, 'store']);
Route::post('/user/login', [AuthController::class, 'login'])->name('user.login');
Route::post('/user/api-login', [AuthController::class, 'apiLogin'])->name('user.api.login');
Route::post('/user/signup', [AuthController::class, 'signup']);
Route::post('/user/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/user/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/user/verify-otp-forgot-password', [AuthController::class, 'verifyOtpForgotPassword']);
Route::post('/user/api-verify-otp', [AuthController::class, 'apiVerifyOtp']);
Route::post('/user/resend-otp', [AuthController::class, 'resendOtp']);
Route::get('/pages', [AuthController::class, 'pageList']);
Route::get('/pages/{slug}', [AuthController::class, 'pageDetail']); 
Route::get('/faqs', [AuthController::class, 'faqs']);
Route::post('/auth/google/register', [GoogleAuthController::class, 'handle']);
Route::post('/auth/google/login', [GoogleAuthController::class, 'login']);
Route::post('/provider/background-check', [UserController::class, 'backgroundCheck']);
Route::post('/provider/background-webhook', [UserController::class, 'backgroundWebHook']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json([
        'success' => true,
        'data' => $request->user()->load(['roles', 'professionalDetail','bankDetail','availabilitySlots','languages','serviceUseCases','licenseTypes'])
    ]);
});

Route::get('/service-category/all', [ServiceCategoryController::class, 'getAllServiceCategories'])->name('api.service-category.all');
Route::get('/languages', [ServiceCategoryController::class, 'getLanguages'])->name('api.languages');
Route::get('/id-type/all', [UserDetailsController::class, 'getAllIdTypes']);
Route::post('/stripe/webhook', [PaymentController::class, 'webhook'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
Route::middleware(['auth:sanctum', 'role:provider'])->group(function (){
    Route::post('/user/professional-details', [UserDetailsController::class, 'storeProfessionalDetails']);
    Route::post('/user/media-upload', [UserDetailsController::class, 'storeMediaData']);
    Route::post('/user/media-upload-v2', [UserDetailsController::class, 'storeMediaDataV2']);
    Route::post('/user/bank-details', [UserDetailsController::class, 'storeBankDetails']);
    Route::post('/user/availability-slots', [UserDetailsController::class, 'storeAvailabilitySlots']);
});
Route::post('/user/test-notification', [NotificationController::class, 'testNotification']);
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/profile', [UserController::class, 'getProfile'])->name('user.profile');
    Route::post('/change-password', [UserController::class, 'changePassword']);
    Route::post('/logout', [UserController::class, 'logout']);
    Route::post('/user/logout', [AuthController::class, 'logout'])->name('user.logout');
    Route::post('/edit-profile', [UserController::class, 'editProfile']);
    Route::post('/upload-profile', [UserController::class, 'uploadProfileImage']);
    Route::delete('/delete-profile-image', [UserController::class, 'deleteProfileImage']);
    Route::post('/user/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/user/update-availability', [UserController::class, 'updateAvailability']);
    Route::get('/user/get-availability', [UserController::class, 'getAvailability']);
    Route::get('/user/availability-slots', [UserController::class, 'getAvailabilitySlots'])->name('user.availability.slots');
    Route::get('/user/bank-details', [UserController::class, 'getBankDetails'])->name('user.bank.details');
    Route::get('/user/media', [UserController::class, 'getMedia'])->name('user.media');
    Route::get('/user/media-v2', [UserController::class, 'getMediaV2'])->name('user.media.v2');
    Route::get('/user/professional-details', [UserController::class, 'getProfessionalDetail'])->name('user.professional.detail');

    Route::post('/cart/store', [CartController::class, 'storeCart']);
    Route::delete('/cart/clear', [CartController::class, 'clearCart']);

    Route::post('/booking/store', [BookingController::class, 'storeBooking']);
    Route::put('/booking/update/{id}', [BookingController::class, 'updateBooking']);
    Route::get('/booking/{id}/slots',     [BookingController::class, 'listSlots']);

    Route::post('/booking/{id}/start',     [BookingController::class, 'startSlot']);
    Route::post('/booking/{id}/cancel',    [BookingController::class, 'cancelBooking']);
    Route::post('/booking/{id}/complete',  [BookingController::class, 'completeSlot']);
    Route::get('/booking', [BookingController::class, 'getBookings']);
    Route::post('/booking/update-status', [BookingController::class, 'updateBookingStatus']);
    Route::get('/booking/{id}', [BookingController::class, 'getBookingDetail']);
    Route::put('/booking/update', [BookingController::class, 'updateBooking']);
    Route::get('/provider/dashboard', [UserController::class, 'dashboard']);
    Route::get('/provider/earnings', [UserController::class, 'earnings']);

    Route::get('provider/available-slots', [UserController::class, 'getAvailableSlots']);
    Route::get('services/recommended', [ServiceCategoryController::class, 'getRecommendedServices']);
    Route::get('provider/booked-slots', [UserController::class, 'getBookedSlots']);
    // Get cart
    Route::get('/cart', [CartController::class, 'getCart']);
    //User Address
    Route::post('/user/address/store', [UserAddressController::class, 'store']);
    Route::put('/user/address/update/{id}', [UserAddressController::class, 'update']);
    Route::put('/user/address/default/{id}', [UserAddressController::class, 'makeDefault']);
    Route::delete('/user/address/delete/{id}', [UserAddressController::class, 'destroy']);
    Route::get('/user/address/list', [UserAddressController::class, 'addressList']);
    Route::get('//user/address/has-default', [UserAddressController::class, 'hasDefaultAddress']);

    //services
    Route::any('/providers/by-service-category', [ServiceCategoryController::class, 'getUsersByServiceCategory'])->name('api.providers.by.category');
    Route::any('/providers/search', [ServiceCategoryController::class, 'searchProviders'])->name('api.providers.search');
    Route::get('providers/nearby', [ServiceCategoryController::class, 'getNearbyProviders']);
    Route::get('/user-detail', [ServiceCategoryController::class, 'getProviderDetail'])->name('api.get.provider.detail');
    Route::get('/get-service-by-category', [ServiceCategoryController::class, 'minPriceByCategory'])->name('api.get.service.by.category');
    Route::post('/user/update-location', [UserController::class, 'updateLocation']);
    Route::get('license-types', [ServiceCategoryController::class, 'getLicenseTypes']);
    Route::get('service-use-cases', [ServiceCategoryController::class, 'getServiceUseCases']);
    Route::get('booking/{id}/invoice',     [BController::class, 'downloadInvoice']);
    Route::get('/bookings/{id}/invoice', [BookingController::class, 'downloadInvoice']);
    Route::post('/booking/{id}/payment/intent',          [PaymentController::class, 'createIntent']);
    Route::get('/booking/{id}/payment/verify/{intentId}',[PaymentController::class, 'verifyPayment']);
    Route::post('/booking/{id}/payment/confirm',          [PaymentController::class, 'confirmIntent']);
    Route::post('/submit-review', [ReviewController::class, 'submitReview']);
    Route::get('/get-review', [ReviewController::class, 'getReview']);
    Route::get('/get-settings', [SettingsController::class, 'index']);
    // User
    Route::get('/enquiries', [
        EnquiryController::class,
        'index'
    ]);

    Route::get('/enquiries/{enquiry_number}', [
        EnquiryController::class,
        'show'
    ]);

    Route::post('/enquiries', [
        EnquiryController::class,
        'store'
    ]);

    // Provider
    Route::get('/provider/enquiries', [
        EnquiryController::class,
        'providerIndex'
    ]);

    Route::get('/provider/enquiries/{enquiry_number}', [
        EnquiryController::class,
        'providerShow'
    ]);

    Route::put('/provider/enquiries/{enquiry_number}', [
        EnquiryController::class,
        'updateByEnquiryNumber'
    ]);
    Route::get('/booking/concerns/list', [BookingConcernController::class, 'list']);

    Route::post('/booking/concerns', [BookingConcernController::class, 'store']);
    Route::post('/booking/addon/request', [BookingAddonController::class, 'requestAddon']);
    Route::get('/booking/{booking}/addon/requests', [BookingAddonController::class, 'listAddonRequests']);
    Route::post('/booking/addon/respond', [BookingAddonController::class, 'respondAddon']);

    //Chat module
    Route::post('/create-conversations', [ChatController::class, 'createConversation']);
    Route::get('/conversations', [ChatController::class, 'conversations']);
    Route::get('/conversations/{conversation}/messages', [ChatController::class, 'messages']);
    Route::post('/messages', [ChatController::class, 'sendMessage']);
    Route::post('/messages/upload', [ChatController::class, 'uploadMessage']);
    Route::post('/conversations/{conversation}/read', [ChatController::class, 'markRead']);
    Route::get('/chat/unread-count', [ChatController::class, 'unreadCount']);
    Route::post('/account/deactivate', [UserController::class, 'deactivate']);
    Route::get('/provider/background-check-link', [UserController::class, 'backgroundCheckLink']);
    Route::get('/provider/payout/balance', [ProviderPayoutController::class, 'balance']);
    Route::post('/provider/payout/request', [ProviderPayoutController::class, 'store']);
    Route::get('/provider/payout/requests', [ProviderPayoutController::class, 'index']);

});


Route::prefix('v2')->group(function () {

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/stripe/connect/account', [StripeConnectController::class, 'createAccount']);
        Route::post('/stripe/connect/onboarding-link', [StripeConnectController::class, 'onboardingLink']);
        Route::get('/stripe/connect/status', [StripeConnectController::class, 'status']);
        Route::post('/stripe/connect/payment-intent', [StripeConnectController::class, 'createPaymentIntent']);
    });

    // NOT behind auth:sanctum — hit by raw browser redirects from Stripe
    Route::get('/stripe/connect/refresh', [StripeConnectController::class, 'refresh'])
        ->name('stripe.connect.refresh');

    Route::get('/stripe/connect/return', [StripeConnectController::class, 'return'])
        ->name('stripe.connect.return');

    Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);
});

Route::middleware('auth:sanctum')->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'notificationList']);

    Route::patch('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/delete-all', [NotificationController::class, 'deleteAllNotifications']);

    Route::patch('/{notificationId}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('/{notificationId}', [NotificationController::class, 'deleteNotification']);
});

Route::post('/test/email/otp',[NotificationController::class, 'sendOtp']);
Route::post('/test/email/booking-created',[NotificationController::class, 'sendBookingCreated']);
