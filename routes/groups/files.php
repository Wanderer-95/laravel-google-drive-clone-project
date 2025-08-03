<?php

use App\Http\Controllers\File\FileController;
use Illuminate\Support\Facades\Route;

Route::controller(FileController::class)
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/my-files/{folder?}', 'myFiles')
            ->where('folder', '(.*)')
            ->name('myFiles');
        Route::post('/folder/create', 'createFolder')->name('folder.create');
        Route::post('/file', 'store')->name('file.store');
    });
