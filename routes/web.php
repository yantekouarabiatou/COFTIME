<?php

use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\CadeauInvitationController;
use App\Http\Controllers\ClientAuditController;
use App\Http\Controllers\IndependanceController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\LogActivitesController;
use App\Http\Controllers\PlaintesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PosteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\InteretController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Statistics\CadeauInvitationReportController;
use App\Http\Controllers\Statistics\ClientAuditReportController;
use App\Http\Controllers\Statistics\IndependanceReportController;
use App\Http\Controllers\StatisticsController;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Statistics\PlaintesStatsController;
use App\Http\Controllers\Statistics\InteretsStatsController;
use App\Http\Controllers\UserProfileController;


Route::get('/', function () {
    return view('auth.login');
})->middleware('guest');

Route::get('/otp', [AuthenticatedSessionController::class, 'showOtpForm'])
    ->name('otp.form');

Route::post('/otp/resend', [AuthenticatedSessionController::class, 'resendOtp'])
    ->name('otp.resend');

Route::post('/otp/verify', [AuthenticatedSessionController::class, 'verifyOtp'])
    ->name('otp.verify');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');
    Route::get('/dashboard/user-stats/{userId}', [DashboardController::class, 'userStats'])->name('dashboard.user-stats');
    Route::post('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');
    Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications', [NotificationController::class, 'clearAll'])->name('notifications.clear-all');
    Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/mark-multiple-read', [NotificationController::class, 'markMultipleAsRead'])->name('notifications.mark-multiple-read');
    // Activités / logs
    Route::get('/logs', [LogActivitesController::class, 'index'])->name('logs.index');
    Route::get('/logs/{log}', [LogActivitesController::class, 'show'])->name('logs.show'); // ← Nouvelle route
    Route::get('/activities', [LogActivitesController::class, 'index'])->name('activities');
    Route::resource('plaintes', PlaintesController::class);
    Route::resource('users', UserController::class);
    Route::resource('postes', PosteController::class);
    Route::get('/plaintes/data', [PlaintesController::class, 'data'])->name('plaintes.data');
    // Changement: remplacez {plainte} par {id}
    Route::get('/plaintes/{id}/pdf', [PlaintesController::class, 'generatePdf'])
        ->name('plaintes.pdf')
        ->middleware('auth');
    // Client Audits
    Route::resource('clients-audit', ClientAuditController::class, [
        'parameters' => ['clients-audit' => 'clientAudit']
    ]);
    Route::get('clients-audit/{clientAudit}/download', [ClientAuditController::class, 'downloadDocument'])->name('clients-audit.download');

    // Cadeau Invitations
    Route::resource('cadeau-invitations', CadeauInvitationController::class);
    Route::get('cadeau-invitations/{cadeauInvitation}/download', [CadeauInvitationController::class, 'downloadDocument'])->name('cadeau-invitations.download');

    Route::resource('independances', IndependanceController::class);

    Route::get('/interets', [InteretController::class, 'index'])->name('interets.index');
    Route::get('/interets/create', [InteretController::class, 'create'])->name('interets.create');
    Route::post('/interets', [InteretController::class, 'store'])->name('interets.store');
    Route::get('/interets/{interet}', [InteretController::class, 'show'])->name('interets.show');
    Route::get('/interets/{interet}/edit', [InteretController::class, 'edit'])->name('interets.edit');
    Route::put('/interets/{interet}', [InteretController::class, 'update'])->name('interets.update');
    Route::delete('/interets/{interet}', [InteretController::class, 'destroy'])->name('interets.destroy');

    // Routes supplémentaires
    Route::get('/interets/{interet}/download', [InteretController::class, 'download'])->name('interets.download');
    Route::get('/interets/{interet}/pdf', [InteretController::class, 'exportPdf'])->name('interets.pdf');
    Route::get('/interets/statistics', [InteretController::class, 'statistics'])->name('interets.statistics');
    Route::get('/interets/search', [InteretController::class, 'search'])->name('interets.search');
    Route::patch('/interets/{interet}/toggle-status', [InteretController::class, 'toggleStatus'])->name('interets.toggle-status');
    Route::post('/interets/{interet}/duplicate', [InteretController::class, 'duplicate'])->name('interets.duplicate');
});

Route::middleware('auth')->prefix('notifications')->name('notifications.')->group(function () {

    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
    Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    Route::delete('/', [NotificationController::class, 'destroyAll'])->name('destroy-all');

    // Bonus realtime
    Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
    Route::get('/recent', [NotificationController::class, 'recent'])->name('recent');
});

Route::prefix('admin')->middleware(['auth', 'verified'])->group(function () {

    Route::get('/permissions', [PermissionController::class, 'index'])
        ->name('admin.roles.permissions.index');

    Route::get('/roles/{role}/permissions', [PermissionController::class, 'show'])
        ->name('admin.roles.permissions.show');

    // Change POST → PUT (ou PATCH)
    Route::put('/roles/{role}/permissions', [PermissionController::class, 'updateRolePermissions'])
        ->name('admin.roles.permissions.update');
});

Route::get('/dashboard/data', [App\Http\Controllers\DashboardController::class, 'data'])->name('dashboard.data')->middleware('auth');
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {

    Route::get('/roles', [RolePermissionController::class, 'index'])
        ->name('roles.index');

    Route::get('/roles/create', [RolePermissionController::class, 'create'])
        ->name('roles.create');

    Route::post('/roles', [RolePermissionController::class, 'store'])
        ->name('roles.store');

    Route::get('/roles/{role}/edit', [RolePermissionController::class, 'edit'])
        ->name('roles.edit');

    Route::put('/roles/{role}', [RolePermissionController::class, 'update'])
        ->name('roles.update');

    Route::delete('/roles/{role}', [RolePermissionController::class, 'destroy'])
        ->name('roles.destroy');
});
Route::get('roles-permissions/{role}', [PermissionController::class, 'show'])
    ->name('admin.roles-permissions.show');

Route::put('/admin/roles-permissions/{role}', [RolePermissionController::class, 'update'])
    ->name('admin.roles-permissions.update');

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::resource('roles', RoleController::class)->except(['show']);
    Route::resource('roles', RoleController::class);
});

Route::get('/statistics/annual', [StatisticsController::class, 'annual'])->name('statistics.annual');
Route::get('/statistics/export', [StatisticsController::class, 'export'])->name('statistics.export');
Route::post('/stats/annual/update', [StatisticsController::class, 'updateCharts'])->name('stats.annual.update');


Route::get('/statistics/plaintes', [PlaintesStatsController::class, 'index'])->name('stats.plaintes');
Route::post('/statistics/plaintes/update', [PlaintesStatsController::class, 'update'])->name('stats.plaintes.update');

Route::get('/statistics/interets', [InteretsStatsController::class, 'index'])->name('stats.interets');
Route::post('/statistics/interets/update', [InteretsStatsController::class, 'update'])->name('stats.interets.update');
Route::get('/reports/independances/annual', [IndependanceReportController::class, 'annual'])
    ->name('reports.independances.annual');

Route::get('/reports/clients-audit/annual', [ClientAuditReportController::class, 'annual'])
    ->name('reports.clients-audit.annual');
Route::get('/reports/cadeau-invitations/annual', [CadeauInvitationReportController::class, 'annual'])
    ->name('reports.cadeau-invitations.annual');


Route::middleware('auth')
    ->prefix('profile')
    ->name('user-profile.')
    ->group(function () {

        Route::get('/', [UserProfileController::class, 'index'])->name('index');
        Route::get('/{id}', [UserProfileController::class, 'showUser'])->name('show');
        Route::get('/{id}/edit', [UserProfileController::class, 'editUser'])->name('edit');
        Route::put('/{id}', [UserProfileController::class, 'updateUser'])->name('update');
        Route::put('/{id}/deactivate', [UserProfileController::class, 'deactivate'])->name('deactivate');
        Route::put('/{id}/activate', [UserProfileController::class, 'activate'])->name('activate');
        Route::get('/{id}/download-photo', [UserProfileController::class, 'downloadPhoto'])->name('download-photo');
        Route::post('/change-password', [UserProfileController::class, 'changePassword'])->name('change-password');
    });

Route::prefix('settings')->group(function () {
    // Affiche les paramètres
    Route::get('/', [CompanySettingController::class, 'show'])->name('settings.show');

    // Affiche le formulaire d'édition
    Route::get('/edit', [CompanySettingController::class, 'edit'])->name('settings.edit');

    // Traite la mise à jour (nécessite l'ID ou une logique de singleton)
    // Ici, on passe l'ID 1 qui sera géré par la méthode update
    Route::put('/{setting}', [CompanySettingController::class, 'update'])->name('settings.update');
})->middleware('auth'); // Appliquez les middlewares nécessaires

Route::prefix('independances')->name('independances.')->group(function () {

    Route::get('/{independance}/pdf', [IndependanceController::class, 'generatePdf'])
        ->name('pdf');
});

Route::prefix('interets')->name('interets.')->group(function () {

    Route::get('/{interet}/pdf', [InteretController::class, 'generatePdf'])
        ->name('pdf'); // Nom de route complet: interets.pdf
});

Route::prefix('cadeau-invitations')->name('cadeau-invitations.')->group(function () {
    // Route de génération PDF
    Route::get('/{cadeauInvitation}/pdf', [CadeauInvitationController::class, 'generatePdf'])
         ->name('pdf');
});

Route::prefix('clients-audits')->name('clients-audits.')->group(function () {

    Route::get('/{clientAudit}/pdf', [ClientAuditController::class, 'generatePdf'])
         ->name('pdf'); // Nom de route complet: clients-audits.pdf
});
require __DIR__ . '/auth.php';
