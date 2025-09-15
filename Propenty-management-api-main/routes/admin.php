<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\Admin\PropertyController;
use App\Http\Controllers\Admin\PropertyTypeController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\GovernorateController;
use App\Http\Controllers\Admin\AmenityController;
use App\Http\Controllers\Admin\FeatureController;
use App\Http\Controllers\Admin\UtilityController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\PriceTypeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application.
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "admin" middleware group and prefix.
|
*/

// Auth routes (public)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/login', [AuthController::class, 'login'])->name('admin.login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');

// Protected admin routes
Route::middleware(['auth', 'can:view dashboard'])->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard.index');
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('admin.dashboard.stats');
    Route::get('/dashboard/charts', [DashboardController::class, 'charts'])->name('admin.dashboard.charts');

    // Profile Management
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('admin.profile.index');
        Route::put('/', [ProfileController::class, 'update'])->name('admin.profile.update');
        Route::get('/change-password', [ProfileController::class, 'showChangePassword'])->name('admin.profile.change-password');
        Route::put('/change-password', [ProfileController::class, 'updatePassword'])->name('admin.profile.update-password');
        Route::get('/settings', [ProfileController::class, 'settings'])->name('admin.profile.settings');
        Route::put('/settings', [ProfileController::class, 'updateSettings'])->name('admin.profile.update-settings');
        Route::delete('/account', [ProfileController::class, 'destroy'])->name('admin.profile.destroy');
    });

     // Properties Management
    Route::resource('properties', PropertyController::class)->names('admin.properties');
    Route::post('properties/bulk-action', [PropertyController::class, 'bulkAction'])->name('admin.properties.bulk');
    Route::post('properties/{property}/approve', [PropertyController::class, 'approve'])->name('admin.properties.approve');
    Route::post('properties/{property}/reject', [PropertyController::class, 'reject'])->name('admin.properties.reject');
    Route::post('properties/{property}/feature', [PropertyController::class, 'toggleFeature'])->name('admin.properties.feature');
    Route::post('properties/{property}/publish', [PropertyController::class, 'togglePublish'])->name('admin.properties.publish');
    Route::post('properties/{property}/restore', [PropertyController::class, 'restore'])->name('admin.properties.restore');
    Route::get('properties/export/csv', [PropertyController::class, 'exportCsv'])->name('admin.properties.export');
    Route::post('properties/import/csv', [PropertyController::class, 'importCsv'])->name('admin.properties.import');
    
    // AJAX routes for location data
    Route::get('properties/ajax/cities-by-state', [PropertyController::class, 'getCitiesByState'])->name('admin.properties.cities-by-state');
    Route::get('properties/ajax/neighborhoods-by-city', [PropertyController::class, 'getNeighborhoodsByCity'])->name('admin.properties.neighborhoods-by-city');

    // Categories / Property Types
    Route::resource('categories', CategoryController::class)->names('admin.categories');
    Route::resource('property-types', PropertyTypeController::class)->names('admin.property-types');
    Route::post('categories/{category}/restore', [CategoryController::class, 'restore'])->name('admin.categories.restore');

    // Price Types Management
    Route::prefix('price-types')->middleware('can:view price types')->group(function () {
        Route::get('/', [PriceTypeController::class, 'index'])->name('admin.price-types.index');
        Route::get('/create', [PriceTypeController::class, 'create'])->name('admin.price-types.create')->middleware('can:create price types');
        Route::post('/', [PriceTypeController::class, 'store'])->name('admin.price-types.store')->middleware('can:create price types');
        Route::get('/{priceType}', [PriceTypeController::class, 'show'])->name('admin.price-types.show');
        Route::get('/{priceType}/edit', [PriceTypeController::class, 'edit'])->name('admin.price-types.edit')->middleware('can:edit price types');
        Route::put('/{priceType}', [PriceTypeController::class, 'update'])->name('admin.price-types.update')->middleware('can:edit price types');
        Route::patch('/{priceType}', [PriceTypeController::class, 'update'])->middleware('can:edit price types');
        Route::delete('/{priceType}', [PriceTypeController::class, 'destroy'])->name('admin.price-types.destroy')->middleware('can:delete price types');
        Route::post('/{priceType}/toggle-status', [PriceTypeController::class, 'toggleStatus'])->name('admin.price-types.toggle-status')->middleware('can:edit price types');
    });

    // Locations Management
    // Governorates
    Route::resource('governorates', GovernorateController::class)->names('admin.governorates');
    Route::post('governorates/{governorate}/toggle-status', [GovernorateController::class, 'toggleStatus'])->name('admin.governorates.toggle-status');
    Route::get('governorates/ajax/filter', [GovernorateController::class, 'filter'])->name('admin.governorates.filter');
    Route::get('governorates/ajax/active', [GovernorateController::class, 'getActiveGovernorates'])->name('admin.governorates.active');
    Route::get('governorates/{governorate}/cities', [GovernorateController::class, 'getCities'])->name('admin.governorates.cities');
    
    // Cities
    Route::resource('cities', CityController::class)->names('admin.cities');
    Route::post('cities/{city}/toggle-status', [CityController::class, 'toggleStatus'])->name('admin.cities.toggle-status');
    Route::get('cities/{city}/neighborhoods', [CityController::class, 'neighborhoods'])->name('admin.cities.neighborhoods');
    Route::post('cities/{city}/neighborhoods', [CityController::class, 'storeNeighborhood'])->name('admin.cities.neighborhoods.store');
    Route::delete('neighborhoods/{neighborhood}', [CityController::class, 'destroyNeighborhood'])->name('admin.neighborhoods.destroy');

    // Features
    Route::resource('features', FeatureController::class)->names('admin.features');
    Route::post('features/bulk-action', [FeatureController::class, 'bulkAction'])->name('admin.features.bulk');

    // Utilities
    Route::resource('utilities', UtilityController::class)->names('admin.utilities');
    Route::post('utilities/bulk-action', [UtilityController::class, 'bulkAction'])->name('admin.utilities.bulk');

    // Users & Agents Management
    Route::resource('users', UserController::class)->names('admin.users');
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
    Route::post('users/{user}/assign-role', [UserController::class, 'assignRole'])->name('admin.users.assign-role');
    Route::post('users/bulk-action', [UserController::class, 'bulkAction'])->name('admin.users.bulk-action');
    Route::get('users/{user}/properties', [UserController::class, 'properties'])->name('admin.users.properties');
    Route::get('users/{user}/leads', [UserController::class, 'leads'])->name('admin.users.leads');
    Route::post('users/{user}/impersonate', [UserController::class, 'impersonate'])->name('admin.users.impersonate');
    Route::post('users/stop-impersonation', [UserController::class, 'stopImpersonation'])->name('admin.users.stop-impersonation');

    // Leads / Inquiries
    Route::resource('leads', LeadController::class)->names('admin.leads');
    Route::post('leads/{lead}/assign', [LeadController::class, 'assign'])->name('admin.leads.assign');
    Route::post('leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('admin.leads.status');
    Route::post('leads/{lead}/notes', [LeadController::class, 'addNote'])->name('admin.leads.notes');
    Route::get('leads/export/csv', [LeadController::class, 'exportCsv'])->name('admin.leads.export');

    // Contact Messages
    Route::prefix('contact')->middleware('can:view contact messages')->group(function () {
        Route::get('/', [ContactMessageController::class, 'index'])->name('admin.contact.index');
        Route::get('/settings', [ContactMessageController::class, 'settings'])->middleware('can:manage contact messages')->name('admin.contact.settings');
        Route::post('/settings', [ContactMessageController::class, 'updateSettings'])->middleware('can:manage contact messages')->name('admin.contact.settings.update');
        Route::get('/{contactMessage}', [ContactMessageController::class, 'show'])->name('admin.contact.show');
        Route::patch('/{contactMessage}/mark-spam', [ContactMessageController::class, 'markAsSpam'])->middleware('can:manage contact messages')->name('admin.contact.mark-spam');
        Route::patch('/{contactMessage}/mark-read', [ContactMessageController::class, 'markAsRead'])->middleware('can:manage contact messages')->name('admin.contact.mark-read');
        Route::delete('/{contactMessage}', [ContactMessageController::class, 'destroy'])->middleware('can:delete contact messages')->name('admin.contact.destroy');
        Route::post('/bulk-action', [ContactMessageController::class, 'bulkAction'])->middleware('can:manage contact messages')->name('admin.contact.bulk-action');
    });

    // Media Library
    Route::get('media', [MediaController::class, 'index'])->name('admin.media.index');
    Route::get('media/statistics', [MediaController::class, 'statistics'])->name('admin.media.statistics');
    Route::get('media/{media}', [MediaController::class, 'show'])->name('admin.media.show');
    Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('admin.media.destroy');
    Route::post('media/bulk-delete', [MediaController::class, 'bulkDelete'])->name('admin.media.bulk-delete');
    Route::get('media/download/{media}', [MediaController::class, 'download'])->name('admin.media.download');
    Route::post('media/upload', [MediaController::class, 'upload'])->name('admin.media.upload');
    Route::post('media/{media}/regenerate', [MediaController::class, 'regenerateConversions'])->name('admin.media.regenerate');
    Route::get('media/cleanup', [MediaController::class, 'cleanup'])->name('admin.media.cleanup');

    // Moderation Queue
    Route::get('moderation', [ModerationController::class, 'index'])->name('admin.moderation.index');
    Route::get('moderation/{property}', [ModerationController::class, 'show'])->name('admin.moderation.show');
    Route::post('moderation/{property}/approve', [ModerationController::class, 'approve'])->name('admin.moderation.approve');
    Route::post('moderation/{property}/reject', [ModerationController::class, 'reject'])->name('admin.moderation.reject');
    Route::post('moderation/bulk-action', [ModerationController::class, 'bulkAction'])->name('admin.moderation.bulk');

    // Settings
    Route::get('settings', [SettingsController::class, 'index'])->name('admin.settings.index');
    Route::post('settings/general', [SettingsController::class, 'updateGeneral'])->name('admin.settings.general');
    Route::post('settings/listings', [SettingsController::class, 'updateListings'])->name('admin.settings.listings');
    Route::post('settings/seo', [SettingsController::class, 'updateSeo'])->name('admin.settings.seo');
    Route::post('settings/media', [SettingsController::class, 'updateMedia'])->name('admin.settings.media');
    Route::post('settings/maps', [SettingsController::class, 'updateMaps'])->name('admin.settings.maps');
    Route::post('settings/smtp', [SettingsController::class, 'updateSmtp'])->name('admin.settings.smtp');
    Route::post('settings/social', [SettingsController::class, 'updateSocial'])->name('admin.settings.social');
    Route::post('settings/security', [SettingsController::class, 'updateSecurity'])->name('admin.settings.security');
    Route::post('settings/test-email', [SettingsController::class, 'testEmail'])->name('admin.settings.test-email');
    Route::post('settings/cache-clear', [SettingsController::class, 'clearCache'])->name('admin.settings.cache-clear');

    // Reports & Analytics
    Route::get('reports', [ReportController::class, 'index'])->name('admin.reports.index');
    Route::get('reports/properties', [ReportController::class, 'properties'])->name('admin.reports.properties');
    Route::get('reports/users', [ReportController::class, 'users'])->name('admin.reports.users');
    Route::get('reports/revenue', [ReportController::class, 'revenue'])->name('admin.reports.revenue');
    Route::get('reports/export/{type}', [ReportController::class, 'export'])->name('admin.reports.export');

    // AJAX routes for location dropdowns
    Route::get('get-cities', [CityController::class, 'getCitiesByState'])->name('admin.get-cities');
    Route::get('get-cities-by-governorate', [CityController::class, 'getCitiesByGovernorate'])->name('admin.get-cities-by-governorate');
    Route::get('get-neighborhoods', [CityController::class, 'getNeighborhoodsByCity'])->name('admin.get-neighborhoods');

    // Roles & Permissions (SuperAdmin only)
    Route::middleware('role:SuperAdmin')->group(function () {
        Route::resource('roles', RoleController::class)->names('admin.roles');
        Route::post('roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('admin.roles.permissions');
        Route::resource('permissions', PermissionController::class)->names('admin.permissions');
    });
});