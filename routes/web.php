<?php

use App\Http\Controllers\Admin\MasterServiceController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\IdentityTypeController;
use App\Http\Controllers\Admin\LicenseTypeController;
use App\Http\Controllers\Admin\LanguagesController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\user\HomeController as UserHomeController;
use App\Http\Controllers\provider\HomeController as ProviderHomeController;
use App\Http\Controllers\seeker\HomeController as SeekerHomeController;
use App\Http\Controllers\user\PortfolioController;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\layouts\WithoutMenu;
use App\Http\Controllers\layouts\WithoutNavbar;
use App\Http\Controllers\layouts\Fluid;
use App\Http\Controllers\layouts\Container;
use App\Http\Controllers\layouts\Blank;
use App\Http\Controllers\pages\AccountSettingsAccount;
use App\Http\Controllers\pages\AccountSettingsNotifications;
use App\Http\Controllers\pages\AccountSettingsConnections;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\pages\MiscUnderMaintenance;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\authentications\ForgotPasswordBasic;
use App\Http\Controllers\cards\CardBasic;
use App\Http\Controllers\dashboard\UserList;
use App\Http\Controllers\dashboard\ProviderList;
use App\Http\Controllers\user_interface\Accordion;
use App\Http\Controllers\user_interface\Alerts;
use App\Http\Controllers\user_interface\Badges;
use App\Http\Controllers\user_interface\Buttons;
use App\Http\Controllers\user_interface\Carousel;
use App\Http\Controllers\user_interface\Collapse;
use App\Http\Controllers\user_interface\Dropdowns;
use App\Http\Controllers\user_interface\Footer;
use App\Http\Controllers\user_interface\ListGroups;
use App\Http\Controllers\user_interface\Modals;
use App\Http\Controllers\user_interface\Navbar;
use App\Http\Controllers\user_interface\Offcanvas;
use App\Http\Controllers\user_interface\PaginationBreadcrumbs;
use App\Http\Controllers\user_interface\Progress;
use App\Http\Controllers\user_interface\Spinners;
use App\Http\Controllers\user_interface\TabsPills;
use App\Http\Controllers\user_interface\Toasts;
use App\Http\Controllers\user_interface\TooltipsPopovers;
use App\Http\Controllers\user_interface\Typography;
use App\Http\Controllers\extended_ui\PerfectScrollbar;
use App\Http\Controllers\extended_ui\TextDivider;
use App\Http\Controllers\icons\RiIcons;
use App\Http\Controllers\form_elements\BasicInput;
use App\Http\Controllers\form_elements\InputGroups;
use App\Http\Controllers\form_layouts\VerticalForm;
use App\Http\Controllers\form_layouts\HorizontalForm;
use App\Http\Controllers\tables\Basic as TablesBasic;
use App\Http\Middleware\IsAdmin;

// layout
Route::get('/layouts/without-menu', [WithoutMenu::class, 'index'])->name('layouts-without-menu');
Route::get('/layouts/without-navbar', [WithoutNavbar::class, 'index'])->name('layouts-without-navbar');
Route::get('/layouts/fluid', [Fluid::class, 'index'])->name('layouts-fluid');
Route::get('/layouts/container', [Container::class, 'index'])->name('layouts-container');
Route::get('/layouts/blank', [Blank::class, 'index'])->name('layouts-blank');

// authentication
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/admin/login', [LoginBasic::class, 'index'])->name('auth-login-basic');
Route::get('/login', [LoginBasic::class, 'index'])->name('login');
Route::get('/register', [LoginBasic::class, 'register'])->name('register');
Route::get('/register/account', [LoginBasic::class, 'registerAccount'])->name('register.account');
Route::get('/verification', [LoginBasic::class, 'verification'])->name('verification');

Route::get('/register/step-1', [LoginBasic::class, 'registerStepOne'])->name('register.profile');
Route::get('/register/step-2', [LoginBasic::class, 'registerStepTwo'])->name('register.documents');
Route::get('/register/step-3', [LoginBasic::class, 'registerStepThree'])->name('register.bank-details');

Route::get('/provider/dashboard', [ProviderHomeController::class, 'index'])->name('provider.dashboard');
Route::get('/provider/requests', [ProviderHomeController::class, 'request'])->name('provider.request');
Route::get('/provider/inprogress', [ProviderHomeController::class, 'inprogress'])->name('provider.inprogress');
Route::get('/provider/jobs', [ProviderHomeController::class, 'job'])->name('provider.job');
Route::get('/provider/earnings', [ProviderHomeController::class, 'earnings'])->name('provider.earnings');
Route::get('/provider/edit-profile', [ProviderHomeController::class, 'editProfile'])->name('provider.edit.profile');
Route::get('/provider/job-detail', [ProviderHomeController::class, 'jobDetail'])->name('provider.job.detail');
Route::get('/provider/upcoming-job', [ProviderHomeController::class, 'upcomingJob'])->name('provider.upcoming.job');
Route::get('/provider/inprogress-job', [ProviderHomeController::class, 'inprogressJob'])->name('provider.inprogress.job');
Route::get('/provider/job-start', [ProviderHomeController::class, 'jobStart'])->name('provider.job.start');
Route::get('/provider/job-inprogress', [ProviderHomeController::class, 'jobInprogress'])->name('provider.job.inprogress');
Route::get('/provider/job-new-service', [ProviderHomeController::class, 'jobNewService'])->name('provider.job.new.service');
Route::get('/provider/job-service-done', [ProviderHomeController::class, 'jobServiceDone'])->name('provider.job.service.done');
Route::get('/provider/job-service-completed', [ProviderHomeController::class, 'jobServiceCompleted'])->name('provider.job.service.completed');

Route::get('/seeker/homepage', [SeekerHomeController::class, 'index'])->name('seeker.homepage');

Route::post('/admin/login', [LoginBasic::class, 'login'])->name('admin.login');
Route::any('/logout', [LoginBasic::class, 'logout'])->name('logout');
Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');
// Route::post('/register', [RegisterBasic::class, 'register'])->name('register');
Route::get('/auth/forgot-password-basic', [ForgotPasswordBasic::class, 'index'])->name('auth-reset-password-basic');
Route::post('/forgot-password', [ForgotPasswordBasic::class, 'sendResetLinkEmail'])->name('forgot.password.mail');
Route::get('/payment/return', [PaymentController::class, 'returnPage'])
    ->name('payment.return');
// cards
Route::get('/cards/basic', [CardBasic::class, 'index'])->name('cards-basic');
// Main Page Route
Route::middleware(['auth', IsAdmin::class])->group(function () {
    Route::get('/admin-dashboard', [Analytics::class, 'index'])->name('admin-dashboard');
    // pages
    Route::get('/user-list', [UserList::class, 'userList'])->name('user-list');
    Route::delete('/user-list/delete/{id}', [UserList::class, 'deleteUser'])->name('delete-user');
    Route::get('/user-list/getUser/{id}', [UserList::class, 'getUser'])->name('get-user');
    Route::post('user-list/update/{id}', [UserList::class, 'updateUser'])->name('update-user');
    Route::get('/user-list/view/{id}', [UserList::class, 'viewUser'])->name('view-user');
    
    Route::get('/provider-management', [ProviderList::class, 'userList'])->name('user-list');
    Route::delete('/provider-management/delete/{id}', [ProviderList::class, 'deleteUser'])->name('delete-user');
    Route::get('/provider-management/getUser/{id}', [ProviderList::class, 'getUser'])->name('get-user');
    Route::post('provider-management/update/{id}', [ProviderList::class, 'updateUser'])->name('update-user');
    Route::get('/provider-management/view/{id}', [ProviderList::class, 'viewUser'])->name('view-user');
    
    
    Route::get('/pages/account-settings-account', [AccountSettingsAccount::class, 'index'])->name('pages-account-settings-account');
    Route::get('/pages/account-settings-notifications', [AccountSettingsNotifications::class, 'index'])->name('pages-account-settings-notifications');
    Route::get('/pages/account-settings-connections', [AccountSettingsConnections::class, 'index'])->name('pages-account-settings-connections');
    Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');
    Route::get('/pages/misc-under-maintenance', [MiscUnderMaintenance::class, 'index'])->name('pages-misc-under-maintenance');
    Route::get('/category-list', [ServiceCategoryController::class, 'index'])->name('category-list');
    Route::delete('/category/delete/{id}', [ServiceCategoryController::class, 'delete'])->name('delete-category');
    Route::get('/category/edit/{id}', [ServiceCategoryController::class, 'edit'])->name('edit-category');
    Route::post('/category/update/{id}', [ServiceCategoryController::class, 'update'])->name('update-category');
    Route::get('/category/view/{id}', [ServiceCategoryController::class, 'view'])->name('view-category');
    Route::get('/category/add', [ServiceCategoryController::class, 'create'])->name('add-category');
    Route::post('/category/store', [ServiceCategoryController::class, 'store'])->name('store-category');
    Route::prefix('master-service')->group(function () {
        Route::get('/', [MasterServiceController::class,'index'])->name('master-service');
        Route::get('/create', [MasterServiceController::class,'create'])->name('master-service.create');
        Route::post('/store', [MasterServiceController::class,'store'])->name('master-service.store');
        Route::get('/edit/{id}', [MasterServiceController::class,'edit'])->name('master-service.edit');
        Route::post('/update/{id}', [MasterServiceController::class,'update'])->name('master-service.update');
        Route::delete('/delete/{id}', [MasterServiceController::class,'delete']);
        Route::get('/view/{id}', [MasterServiceController::class,'view'])->name('master-service.view');
    });
    Route::prefix('document-type')->group(function () {
        Route::get('/',                      [IdentityTypeController::class, 'index'])->name('document-type');
        Route::get('/create',                [IdentityTypeController::class, 'create'])->name('document-type.create');
        Route::post('/store',                [IdentityTypeController::class, 'store'])->name('document-type.store');
        Route::get('/edit/{id}',             [IdentityTypeController::class, 'edit'])->name('document-type.edit');
        Route::post('/update/{id}',          [IdentityTypeController::class, 'update'])->name('document-type.update');
        Route::delete('/delete/{id}',        [IdentityTypeController::class, 'delete'])->name('document-type.delete');
        Route::get('/view/{id}',             [IdentityTypeController::class, 'view'])->name('document-type.view');
    });

    Route::prefix('page')->group(function () {
        Route::get('/',                [PageController::class, 'index'])->name('page');
        Route::get('/create',          [PageController::class, 'create'])->name('page.create');
        Route::post('/store',          [PageController::class, 'store'])->name('page.store');
        Route::get('/edit/{id}',       [PageController::class, 'edit'])->name('page.edit');
        Route::post('/update/{id}',    [PageController::class, 'update'])->name('page.update');
        Route::delete('/delete/{id}',  [PageController::class, 'delete'])->name('page.delete');
        Route::get('/view/{id}',       [PageController::class, 'view'])->name('page.view');
    });
    Route::get('license-type', [LicenseTypeController::class, 'index'])->name('license-type');
    Route::get('license-type/create', [LicenseTypeController::class, 'create'])->name('create-license-type');
    Route::post('license-type/store', [LicenseTypeController::class, 'store'])->name('store-license-type');
    Route::get('license-type/edit/{id}', [LicenseTypeController::class, 'edit'])->name('edit-license-type');
    Route::put('license-type/update/{id}', [LicenseTypeController::class, 'update'])->name('update-license-type');
    Route::get('license-type/view/{id}', [LicenseTypeController::class, 'view'])->name('view-license-type');
    Route::delete('license-type/delete/{id}', [LicenseTypeController::class, 'delete'])->name('delete-license-type');
    
    Route::get('languages', [LanguagesController::class, 'index'])->name('language');
    Route::get('languages/create', [LanguagesController::class, 'create'])->name('create-language');
    Route::post('languages/store', [LanguagesController::class, 'store'])->name('store-language');
    Route::get('languages/edit/{id}', [LanguagesController::class, 'edit'])->name('edit-language');
    Route::post('languages/update/{id}', [LanguagesController::class, 'update'])->name('update-language');
    Route::get('languages/view/{id}', [LanguagesController::class, 'view'])->name('view-language');
    Route::delete('languages/delete/{id}', [LanguagesController::class, 'delete'])->name('delete-language');
    
    
    Route::get('/booking-list',           [BookingController::class, 'index'])->name('booking-list');
    Route::get('/booking/{id}/invoice',     [BookingController::class, 'downloadInvoice'])->name('booking-invoice');
    Route::get('/booking/view/{id}',      [BookingController::class, 'view'])->name('view-booking');
    Route::delete('/booking/delete/{id}', [BookingController::class, 'delete'])->name('delete-booking');
    Route::get('/pending-service', [MasterServiceController::class, 'pending'])->name('pending-service');
    Route::post('/master-service/toggle-status', [MasterServiceController::class, 'toggleStatus'])->name('master-service.toggle-status');
});

// User Interface
Route::get('/ui/accordion', [Accordion::class, 'index'])->name('ui-accordion');
Route::get('/ui/alerts', [Alerts::class, 'index'])->name('ui-alerts');
Route::get('/ui/badges', [Badges::class, 'index'])->name('ui-badges');
Route::get('/ui/buttons', [Buttons::class, 'index'])->name('ui-buttons');
Route::get('/ui/carousel', [Carousel::class, 'index'])->name('ui-carousel');
Route::get('/ui/collapse', [Collapse::class, 'index'])->name('ui-collapse');
Route::get('/ui/dropdowns', [Dropdowns::class, 'index'])->name('ui-dropdowns');
Route::get('/ui/footer', [Footer::class, 'index'])->name('ui-footer');
Route::get('/ui/list-groups', [ListGroups::class, 'index'])->name('ui-list-groups');
Route::get('/ui/modals', [Modals::class, 'index'])->name('ui-modals');
Route::get('/ui/navbar', [Navbar::class, 'index'])->name('ui-navbar');
Route::get('/ui/offcanvas', [Offcanvas::class, 'index'])->name('ui-offcanvas');
Route::get('/ui/pagination-breadcrumbs', [PaginationBreadcrumbs::class, 'index'])->name('ui-pagination-breadcrumbs');
Route::get('/ui/progress', [Progress::class, 'index'])->name('ui-progress');
Route::get('/ui/spinners', [Spinners::class, 'index'])->name('ui-spinners');
Route::get('/ui/tabs-pills', [TabsPills::class, 'index'])->name('ui-tabs-pills');
Route::get('/ui/toasts', [Toasts::class, 'index'])->name('ui-toasts');
Route::get('/ui/tooltips-popovers', [TooltipsPopovers::class, 'index'])->name('ui-tooltips-popovers');
Route::get('/ui/typography', [Typography::class, 'index'])->name('ui-typography');

// extended ui
Route::get('/extended/ui-perfect-scrollbar', [PerfectScrollbar::class, 'index'])->name('extended-ui-perfect-scrollbar');
Route::get('/extended/ui-text-divider', [TextDivider::class, 'index'])->name('extended-ui-text-divider');

// icons
Route::get('/icons/icons-ri', [RiIcons::class, 'index'])->name('icons-ri');

// form elements
Route::get('/forms/basic-inputs', [BasicInput::class, 'index'])->name('forms-basic-inputs');
Route::get('/forms/input-groups', [InputGroups::class, 'index'])->name('forms-input-groups');

// form layouts
Route::get('/form/layouts-vertical', [VerticalForm::class, 'index'])->name('form-layouts-vertical');
Route::get('/form/layouts-horizontal', [HorizontalForm::class, 'index'])->name('form-layouts-horizontal');

// tables
Route::get('/tables/basic', [TablesBasic::class, 'index'])->name('tables-basic');
