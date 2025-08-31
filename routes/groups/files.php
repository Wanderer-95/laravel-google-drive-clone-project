<?php

use App\Http\Controllers\File\FileController;
use Illuminate\Support\Facades\Route;

Route::controller(FileController::class)
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/', 'home');
        Route::get('/my-files/{folder?}', 'myFiles')
            ->where('folder', '(.*)')
            ->name('myFiles');
        Route::get('/trash', 'trash')->name('trash');
        Route::post('/trash', 'restore')->name('restore');
        Route::post('/folder/create', 'createFolder')->name('folder.create');
        Route::post('/file', 'store')->name('file.store');
        Route::post('/file/favorite', 'addFavorite')->name('file.add-favorite');
        Route::post('/file/share', 'share')->name('file.share');
        Route::get('/sharedWithMe', 'sharedWithMe')->name('shared-with-me');
        Route::get('/shared-download/{type}', 'sharedDownload')
            ->whereIn('type', ['with-me', 'by-me'])
            ->name('shared-download');
        Route::get('/sharedByMe', 'sharedByMe')->name('shared-by-me');
        //Route::get('/sharedByMe/download', 'sharedByMeDownload')->name('shared-by-me-download');
        Route::delete('/file/delete', 'destroy')->name('file.delete');
        Route::delete('/file/delete-forever', 'deleteForever')->name('file.delete-forever');
        Route::get('/file/download', 'download')->name('file.download');
    });
