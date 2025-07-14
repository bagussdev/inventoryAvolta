<?php

use App\Http\Controllers\Api\SparepartController as ApiSparepartController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\OutletDataController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserDataController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\SparepartController;
use App\Http\Controllers\UsedSparepartController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationPreferenceController;
use App\Models\Incident;
use App\Models\UsedSparepart;
use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Guest only: jika sudah login, tidak bisa akses login
Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
});

// Authenticated only
Route::middleware(['auth'])->group(function () {
    // Route::get('/dashboard', function () {
    //     return view('master.index');
    // })->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // inventory Management
    Route::get('/items/tbody', [ItemController::class, 'tbody'])->name('items.tbody');
    Route::get('/items/last-updated', [ItemController::class, 'lastUpdated'])->name('items.lastUpdated');
    Route::resource('items', ItemController::class);
    Route::get('/items-deleted', [ItemController::class, 'ryclebin'])->name('items.deleted');
    Route::delete('/items/deleted/permanent-all', [ItemController::class, 'permanentDeleteAll'])->name('items.deleted.permanentAll');
    Route::post('/items/{id}/restore', [ItemController::class, 'restore'])->name('items.restore');
    Route::post('/items/import/save', [ItemController::class, 'importSave'])->name('items.import.save');
    Route::get('/items/template/download', [ItemController::class, 'downloadTemplate'])->name('items.template.download');

    // transactions
    Route::get('/transactions/last-updated', [TransactionController::class, 'lastUpdated'])->name('transactions.lastUpdated');
    Route::get('/transactions/tbody', [TransactionController::class, 'tbody']);
    Route::resource('transactions', TransactionController::class);
    Route::post('/transactions/import/save', [TransactionController::class, 'importSave'])->name('transactions.import.save');
    Route::get('/transactions/template/download', [TransactionController::class, 'downloadTemplate'])->name('transactions.template.download');
    Route::get('/transactions-export', [TransactionController::class, 'export'])->name('transactions.export');
    Route::get('/transactions/create/json', [TransactionController::class, 'json'])->name('transactions.json');

    // Equipments
    Route::get('/equipments/last-updated', [EquipmentController::class, 'lastUpdated']);
    Route::get('/equipments/tbody', [EquipmentController::class, 'tbody'])->name('equipments.tbody');
    Route::resource('equipments', EquipmentController::class)->only(['index', 'show']);
    Route::get('/equipments/{equipment}/migrate', [EquipmentController::class, 'showMigrateForm'])->name('equipments.migrate.form');
    Route::post('/equipments/{equipment}/migrate', [EquipmentController::class, 'storeMigrate'])->name('equipments.migrate');

    // Spareparts
    Route::get('/spareparts/last-updated', [SparepartController::class, 'lastUpdated']);
    Route::get('/spareparts/tbody', [SparepartController::class, 'tbody'])->name('spareparts.tbody');
    Route::resource('spareparts', SparepartController::class);

    // Sparepartused
    Route::get('/sparepartused/last-updated', [UsedSparepartController::class, 'lastUpdated']);
    Route::get('/sparepartused/tbody', [UsedSparepartController::class, 'tbody'])->name('sparepartused.tbody');
    Route::resource('sparepartused', UsedSparepartController::class);
    Route::get('/sparepartused-export', [UsedSparepartController::class, 'export'])->name('sparepartused.export');

    // Maintenance
    Route::get('/maintenances/tbody', [MaintenanceController::class, 'tbody'])->name('maintenances.tbody');
    Route::get('/maintenances/last-updated', [MaintenanceController::class, 'lastUpdated'])->name('maintenances.lastUpdated');
    Route::resource('maintenances', MaintenanceController::class);
    Route::get('/maintenances-completed', [MaintenanceController::class, 'completed'])->name('maintenances.completed');
    Route::get('maintenances/{id}/proses', [MaintenanceController::class, 'proses'])->name('maintenances.proses');
    Route::get('/maintenances/{id}/confirm', [MaintenanceController::class, 'confirm'])->name('maintenances.confirm');
    Route::post('/maintenances/{id}/confirm', [MaintenanceController::class, 'submitConfirm'])->name('maintenances.submitConfirm');
    Route::get('maintenances/{id}/closed', [MaintenanceController::class, 'closed'])->name('maintenances.closed');
    Route::put('/maintenances/{id}/update-spareparts', [MaintenanceController::class, 'updateSpareparts'])->name('maintenances.updateSpareparts');
    Route::get('/maintenances-completed/{maintenance}', [MaintenanceController::class, 'show'])->name('maintenances.showCompletedDetail');
    Route::get('/api/spareparts/{id}/stock', [ApiSparepartController::class, 'getStock']);
    Route::get('/maintenances-export', [MaintenanceController::class, 'export'])->name('maintenances.export');
    Route::get('/maintenances-completedExport', [MaintenanceController::class, 'exportCompleted'])->name('maintenances.completed.export');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // User Management
    Route::get('/users/tbody', [UserDataController::class, 'tbody'])->name('users.tbody');
    Route::get('/users/last-updated', [UserDataController::class, 'lastUpdated'])->name('users.lastUpdated');
    Route::resource('users', UserDataController::class);
    Route::get('/users/{id}/activate', [UserDataController::class, 'activate'])->name('users.active');
    Route::get('/users/{id}/deactivate', [UserDataController::class, 'deactivate'])->name('users.deactive');

    // Outlet Management
    Route::get('/outlets/tbody', [OutletDataController::class, 'tbody'])->name('outlets.tbody');
    Route::get('/outlets/last-updated', [OutletDataController::class, 'lastUpdated'])->name('outlets.lastUpdated');
    Route::resource('outlets', OutletDataController::class);
    Route::get('/outlets/{id}/active', [OutletDataController::class, 'active'])->name('outlets.active');
    Route::get('/outlets/{id}/deactive', [OutletDataController::class, 'deactive'])->name('outlets.deactive');
    Route::post('/outlets/import/save', [OutletDataController::class, 'importSave'])->name('outlets.import.save');
    Route::get('/outlets/template/download', [OutletDataController::class, 'downloadTemplate'])->name('outlets.template.download');

    // Permissions
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.save');
    Route::put('/permissions/{id}', [PermissionController::class, 'update'])->name('permissions.update');

    //Incident
    Route::get('/incidents/tbody', [IncidentController::class, 'tbody'])->name('incidents.tbody');
    Route::get('/incidents/last-updated', [IncidentController::class, 'lastUpdated'])->name('incidents.lastUpdated');
    route::resource('incidents', IncidentController::class);
    Route::get('/incidents-completed', [IncidentController::class, 'completed'])->name('incidents.completed');
    Route::put('/incidents/{id}/update-spareparts', [IncidentController::class, 'updateSpareparts'])->name('incidents.updateSpareparts');
    Route::get('/incidents-completed/{incident}', [IncidentController::class, 'show'])->name('incidents.showCompletedDetail');
    Route::get('/incidents-export', [IncidentController::class, 'export'])->name('incidents.export');
    Route::get('/incidents-exportCompleted', [IncidentController::class, 'exportCompleted'])->name('incidents.exportCompleted');
    Route::get('/ajax/items-by-store/{storeId}/{departmentId}', [IncidentController::class, 'getItemsByStore']);
    Route::prefix('incidents')->name('incidents.')->group(function () {
        Route::post('{id}/start', [IncidentController::class, 'start'])->name('start');
        Route::post('{id}/re-start', [IncidentController::class, 'restart'])->name('restart');
        Route::post('{id}/pending', [IncidentController::class, 'pending'])->name('pending');
        Route::get('{id}/resolve', [IncidentController::class, 'resolve'])->name('resolve');
        Route::post('{id}/complete', [IncidentController::class, 'complete'])->name('complete');
        Route::post('{id}/confirm', [IncidentController::class, 'submitConfirm'])->name('submitConfirm');
    });
    Route::get('/ajax/check-incident-status/{equipmentId}', [IncidentController::class, 'checkIncidentStatus']);

    // request
    Route::get('/requests/tbody', [RequestController::class, 'tbody'])->name('requests.tbody');
    Route::get('/requests/last-updated', [RequestController::class, 'lastUpdated'])->name('requests.last-updated');
    route::resource('requests', RequestController::class);
    Route::get('/requests-completed', [RequestController::class, 'completed'])->name('requests.completed');
    Route::get('/requests-completed/{request}', [RequestController::class, 'show'])->name('requests.showCompletedDetail');
    Route::get('/requests-export', [RequestController::class, 'export'])->name('requests.export');
    Route::get('/requests-exportCompleted', [RequestController::class, 'exportCompleted'])->name('requests.exportCompleted');
    Route::put('/requests/{id}/update-spareparts', [RequestController::class, 'updateSpareparts'])->name('requests.updateSpareparts');
    Route::prefix('requests')->name('requests.')->group(function () {
        Route::post('{id}/start', [RequestController::class, 'start'])->name('start');
        Route::post('{id}/re-start', [RequestController::class, 'restart'])->name('restart');
        Route::post('{id}/pending', [RequestController::class, 'pending'])->name('pending');
        Route::get('{id}/resolve', [RequestController::class, 'resolve'])->name('resolve');
        Route::post('{id}/complete', [RequestController::class, 'complete'])->name('complete');
        Route::post('{id}/confirm', [RequestController::class, 'submitConfirm'])->name('submitConfirm');
    });

    // Notification Permission
    Route::get('/notification-preferences', [NotificationPreferenceController::class, 'index'])->name('notification-preferences.index');
    Route::post('/notification-preferences/save', [NotificationPreferenceController::class, 'save'])->name('notification-preferences.save');

    // Notification
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/html', [NotificationController::class, 'html'])->name('notifications.html');
    Route::get('/notifications/last-updated', [NotificationController::class, 'lastUpdated'])->name('notifications.lastUpdated');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
});

// Route::fallback(function () {
//     abort(404);
// });

require __DIR__ . '/auth.php';
