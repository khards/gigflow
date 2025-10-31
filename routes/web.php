<?php

use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;
use UniSharp\LaravelFilemanager\Middlewares\CreateDefaultFolder;
use UniSharp\LaravelFilemanager\Middlewares\MultiUser;

/*
 * Global Routes
 *
 * Routes that are used between both frontend and backend.
 */

// Switch between the included languages
Route::get('lang/{lang}', [LocaleController::class, 'change'])->name('locale.change');

/*
 * Frontend Routes
 */
Route::group(['as' => 'frontend.'], function () {
    includeRouteFiles(__DIR__.'/frontend/');
});

/*
 * Backend Routes
 *
 * These routes can only be accessed by users with type `admin`
 */
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'admin'], function () {
    includeRouteFiles(__DIR__.'/backend/');
});

Route::group(['prefix' => 'filemanager', 'middleware' => ['web', 'auth']], function () {
    {
        $middleware = [ CreateDefaultFolder::class, MultiUser::class ];
        $as = 'unisharp.lfm.';
        $namespace = '\\UniSharp\\LaravelFilemanager\\Controllers\\';

        Route::group(compact('middleware', 'as', 'namespace'), function () {

            // display main layout
            Route::get('/', ['uses' => '\App\Domains\FileManager\Controllers\FileManager@show', 'as' => 'show']);

            // display integration error messages
            Route::get('/errors', ['uses' => '\App\Domains\FileManager\Controllers\FileManager@getErrors','as' => 'getErrors',]);

            // upload
            Route::any('/upload', ['uses' => 'UploadController@upload','as' => 'upload',]);

            // list images & files
            Route::get('/jsonitems', ['uses' => 'ItemsController@getItems', 'as' => 'getItems',]);

            Route::get('/move', ['uses' => 'ItemsController@move', 'as' => 'move', ]);
            Route::get('/domove', ['uses' => 'ItemsController@domove', 'as' => 'domove']);

            // folders
            Route::get('/newfolder', ['uses' => 'FolderController@getAddfolder','as' => 'getAddfolder',]);

            // list folders
            Route::get('/folders', ['uses' => 'FolderController@getFolders', 'as' => 'getFolders', ]);

            // crop
            Route::get('/crop', ['uses' => 'CropController@getCrop', 'as' => 'getCrop', ]);
            Route::get('/cropimage', ['uses' => 'CropController@getCropimage', 'as' => 'getCropimage', ]);
            Route::get('/cropnewimage', [ 'uses' => 'CropController@getNewCropimage', 'as' => 'getCropnewimage',]);

            // rename
            Route::get('/rename', ['uses' => 'RenameController@getRename', 'as' => 'getRename',]);

            // scale/resize
            Route::get('/resize', [ 'uses' => 'ResizeController@getResize', 'as' => 'getResize', ]);
            Route::get('/doresize', [ 'uses' => 'ResizeController@performResize', 'as' => 'performResize', ]);

            // download
            Route::get('/download', [ 'uses' => 'DownloadController@getDownload', 'as' => 'getDownload', ]);

            // delete
            Route::get('/delete', ['uses' => 'DeleteController@getDelete', 'as' => 'getDelete',]);
        });

        Route::group(compact('middleware', 'as', 'namespace'), function () {

            // display embedded layout
            Route::get('/embedded', ['uses' => '\App\Domains\FileManager\Controllers\FileManager@embedded', 'as' => 'embedded',]);

            // display integration error messages
            Route::get('/embedded/errors', ['uses' => '\App\Domains\FileManager\Controllers\FileManager@getErrors','as' => 'getErrors',]);

            // upload
            Route::any('/embedded/upload', ['uses' => 'UploadController@upload','as' => 'upload',]);

            // list images & files
            Route::get('/embedded/jsonitems', ['uses' => 'ItemsController@getItems', 'as' => 'getItems',]);

            Route::get('/embedded/move', ['uses' => 'ItemsController@move', 'as' => 'move', ]);
            Route::get('/embedded/domove', ['uses' => 'ItemsController@domove', 'as' => 'domove']);

            // folders
            Route::get('/embedded/newfolder', ['uses' => 'FolderController@getAddfolder','as' => 'getAddfolder',]);

            // list folders
            Route::get('/embedded/folders', ['uses' => 'FolderController@getFolders', 'as' => 'getFolders', ]);

            // crop
            Route::get('/embedded/crop', ['uses' => 'CropController@getCrop', 'as' => 'getCrop', ]);
            Route::get('/embedded/cropimage', ['uses' => 'CropController@getCropimage', 'as' => 'getCropimage', ]);
            Route::get('/embedded/cropnewimage', [ 'uses' => 'CropController@getNewCropimage', 'as' => 'getCropnewimage',]);

            // rename
            Route::get('/embedded/rename', ['uses' => 'RenameController@getRename', 'as' => 'getRename',]);

            // scale/resize
            Route::get('/embedded/resize', [ 'uses' => 'ResizeController@getResize', 'as' => 'getResize', ]);
            Route::get('/embedded/doresize', [ 'uses' => 'ResizeController@performResize', 'as' => 'performResize', ]);

            // download
            Route::get('/embedded/download', [ 'uses' => 'DownloadController@getDownload', 'as' => 'getDownload', ]);

            // delete
            Route::get('/embedded/delete', ['uses' => 'DeleteController@getDelete', 'as' => 'getDelete',]);
        });

        //end indent
    }
});
