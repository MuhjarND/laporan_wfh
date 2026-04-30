<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Authentication Routes
Auth::routes(['register' => false]);

// Chatbot Gateway magic login
Route::get('/autologin', 'AutoLoginController')->name('autologin');

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Public eviden preview. Link is token-based and does not require login.
Route::get('/eviden/{token}', 'EvidenController@preview')->name('eviden.preview');
Route::get('/eviden/{token}/file', 'EvidenController@file')->name('eviden.file');

// Dashboard
Route::get('/dashboard', 'DashboardController@index')->name('dashboard');

// Notifications
Route::get('/notifications', 'NotificationController@index')->name('notifications.index');
Route::post('/notifications/{id}/read', 'NotificationController@markAsRead')->name('notifications.read');
Route::post('/notifications/read-all', 'NotificationController@markAllAsRead')->name('notifications.read-all');

// ==========================================
// SUPER ADMIN ROUTES
// ==========================================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:super_admin'])->group(function () {
    // User Management
    Route::post('users/send-credentials', 'Admin\UserController@sendCredentials')->name('users.send-credentials');
    Route::resource('users', 'Admin\UserController');
    Route::post('users/{user}/send-credential', 'Admin\UserController@sendCredential')->name('users.send-credential');
    Route::post('users/{user}/toggle-active', 'Admin\UserController@toggleActive')->name('users.toggle-active');

    // Laporan WFH
    Route::get('laporan/download/all-pdf', 'Admin\LaporanController@downloadAllPdf')->name('laporan.download-all-pdf');
    Route::get('laporan/{laporan}/preview', 'Admin\LaporanController@preview')->name('laporan.preview');
    Route::get('laporan/{laporan}/pdf', 'Admin\LaporanController@downloadPdf')->name('laporan.pdf');
    Route::get('laporan', 'Admin\LaporanController@index')->name('laporan.index');

    // WFH Date Management
    Route::get('wfh-dates/monitoring', 'Admin\WfhDateController@monitoring')->name('wfh-dates.monitoring');
    Route::post('wfh-dates/monitoring/send-all-activity-reminders', 'Admin\WfhDateController@sendAllActivityReminders')->name('wfh-dates.send-all-activity-reminders');
    Route::post('wfh-dates/monitoring/send-all-submit-reminders', 'Admin\WfhDateController@sendAllSubmitReminders')->name('wfh-dates.send-all-submit-reminders');
    Route::post('wfh-dates/{wfhDate}/send-reminder', 'Admin\WfhDateController@sendReminder')->name('wfh-dates.send-reminder');
    Route::post('wfh-dates/{wfhDate}/send-submit-reminder', 'Admin\WfhDateController@sendSubmitReminder')->name('wfh-dates.send-submit-reminder');
    Route::resource('wfh-dates', 'Admin\WfhDateController')->except(['show', 'edit', 'update']);
    Route::post('wfh-dates/{wfhDate}/toggle-active', 'Admin\WfhDateController@toggleActive')->name('wfh-dates.toggle-active');
});

// ==========================================
// PEGAWAI ROUTES
// ==========================================
Route::prefix('pegawai')->name('pegawai.')->middleware(['auth', 'role:pegawai,atasan'])->group(function () {
    Route::get('laporan/download/all-pdf', 'Pegawai\LaporanController@downloadAllPdf')->name('laporan.download-all-pdf');
    Route::resource('laporan', 'Pegawai\LaporanController');
    Route::post('laporan/{laporan}/kegiatan', 'Pegawai\LaporanController@addKegiatan')->name('laporan.add-kegiatan');
    Route::put('kegiatan/{kegiatan}', 'Pegawai\LaporanController@updateKegiatan')->name('kegiatan.update');
    Route::delete('kegiatan/{kegiatan}', 'Pegawai\LaporanController@deleteKegiatan')->name('kegiatan.delete');
    Route::post('laporan/{laporan}/submit', 'Pegawai\LaporanController@submit')->name('laporan.submit');
    Route::get('laporan/{laporan}/preview', 'Pegawai\LaporanController@preview')->name('laporan.preview');
    Route::get('laporan/{laporan}/pdf', 'Pegawai\LaporanController@downloadPdf')->name('laporan.pdf');
});

// ==========================================
// ATASAN ROUTES
// ==========================================
Route::prefix('atasan')->name('atasan.')->middleware(['auth', 'role:atasan'])->group(function () {
    Route::get('monitoring', 'Atasan\MonitoringController@index')->name('monitoring.index');
    Route::get('monitoring/pending', 'Atasan\MonitoringController@laporanPending')->name('monitoring.pending');
    Route::get('monitoring/pending/sign-all', 'Atasan\MonitoringController@signAllForm')->name('monitoring.sign-all');
    Route::post('monitoring/pending/sign-all', 'Atasan\MonitoringController@signAll')->name('monitoring.sign-all.submit');
    Route::get('monitoring/pegawai/{pegawai}', 'Atasan\MonitoringController@showPegawai')->name('monitoring.show-pegawai');
    Route::get('monitoring/laporan/{laporan}', 'Atasan\MonitoringController@showLaporan')->name('monitoring.show-laporan');
    Route::post('monitoring/laporan/{laporan}/approve', 'Atasan\MonitoringController@approve')->name('monitoring.approve');
    Route::post('monitoring/laporan/{laporan}/reject', 'Atasan\MonitoringController@reject')->name('monitoring.reject');
    Route::get('monitoring/laporan/{laporan}/preview', 'Atasan\MonitoringController@preview')->name('monitoring.preview');
    Route::get('monitoring/laporan/{laporan}/pdf', 'Atasan\MonitoringController@downloadPdf')->name('monitoring.pdf');
});
