<?php

use App\Http\Controllers\apis\Admin\AdminCertificateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Certificates Routes — /api/v1/admin/certificates
|--------------------------------------------------------------------------
|
| New endpoints serving the 2026 admin Certificates redesign. The legacy
| public endpoints (routes/apis/certificates.php → /api/v1/certificates)
| remain untouched and continue to serve their original consumers.
*/

Route::middleware(['auth.user', 'role:Admin'])->prefix('admin')->group(function () {
    Route::get('certificates/template/overview', [AdminCertificateController::class, 'templateOverview']);
    Route::post('certificates/template',         [AdminCertificateController::class, 'uploadTemplate']);
    Route::get('certificates/template/file',     [AdminCertificateController::class, 'templateFile']);

    Route::get('certificates',                                       [AdminCertificateController::class, 'index']);
    Route::get('certificates/{userId}/{courseId}/download',          [AdminCertificateController::class, 'downloadIssued'])
        ->whereNumber('userId')
        ->whereNumber('courseId');
});
