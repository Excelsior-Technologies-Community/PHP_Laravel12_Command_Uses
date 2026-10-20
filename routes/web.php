<?php

use App\Http\Controllers\ArtisanCommandController;
use Illuminate\Support\Facades\Route;

// Redirect home directly to the Artisan Control Center Dashboard
Route::get('/', function () {
    return redirect()->route('commands.dashboard');
});

// Artisan Command Runner Dashboard & Actions
Route::prefix('commands')->name('commands.')->group(function () {
    Route::get('/', [ArtisanCommandController::class, 'index'])->name('dashboard');
    Route::post('/run', [ArtisanCommandController::class, 'run'])->name('run');
    Route::get('/backups/{filename}/download', [ArtisanCommandController::class, 'downloadBackup'])->name('backup.download');
    Route::delete('/backups/{filename}', [ArtisanCommandController::class, 'deleteBackup'])->name('backup.delete');
    Route::post('/logs/clear', [ArtisanCommandController::class, 'clearLogs'])->name('logs.clear');
});
