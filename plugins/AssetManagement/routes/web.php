<?php

use Illuminate\Support\Facades\Route;
use Plugins\AssetManagement\Controllers\AssetsController;
use Plugins\AssetManagement\Controllers\AssetsCategoryController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::prefix('master-panel')->middleware(['multiguard', 'custom-verified', 'check.subscription', 'subscription.modules', 'customcan:manage_assets'])->group(function () {
        Route::prefix('assets')->group(function () {
            Route::get('/index', [AssetsController::class, 'index'])->name('assets.index');
            Route::get('/show/{id}', [AssetsController::class, 'show'])->name('assets.show');
            Route::post('/store', [AssetsController::class, 'store'])->name('assets.store')->middleware('customcan:create_assets');
            Route::post('/update/{id}', [AssetsController::class, 'update'])->name('assets.update')->middleware('customcan:edit_assets');
            Route::delete('/destroy/{id}', [AssetsController::class, 'destroy'])->name('assets.destroy')->middleware('customcan:delete_assets');
            Route::delete('/destroy_multiple', [AssetsController::class, 'destroy_multiple'])->name('assets.destroy_multiple')->middleware('customcan:delete_assets');
            Route::get('/list', [AssetsController::class, 'list'])->name('assets.list');
            Route::post('/{id}/lend', [AssetsController::class, 'lend'])->name('assets.lend');
            Route::post('/{id}/return', [AssetsController::class, 'returnAsset'])->name('assets.return');
            Route::post('/bulk-assign', [AssetsController::class, 'bulkAssign'])->name('assets.bulk-assign');
            Route::get('/global-analytics', [AssetsController::class, 'globalAnalytics'])->name('assets.global-analytics');
            Route::get('/search-assets', [AssetsController::class, 'search'])->name('assets.search');
            Route::post('/duplicate/{id}', [AssetsController::class, 'duplicate'])->name('assets.duplicate');
            Route::post('/import', [AssetsController::class, 'import'])->name('assets.import');
            Route::get('/export', [AssetsController::class, 'export'])->name('assets.export');

            // category
            Route::get('/category/index', [AssetsCategoryController::class, 'index'])->name('assets.category.index');
            Route::post('/category/store', [AssetsCategoryController::class, 'store'])->name('assets.category.store')->middleware('customcan:create_asset_categories');;
            Route::post('/category/update/{id}', [AssetsCategoryController::class, 'update'])->name('assets.category.update')->middleware('customcan:edit_asset_categories');;
            Route::delete('/category/destroy/{id}', [AssetsCategoryController::class, 'destroy'])->name('assets.category.destroy')->middleware('customcan:delete_asset_categories');;
            Route::delete('/category/destroy_multiple', [AssetsCategoryController::class, 'destroy_multiple'])->name('assets.category.destroy_multiple')->middleware('customcan:delete_asset_categories');;
            Route::get('/category/list', [AssetsCategoryController::class, 'list'])->name('assets.category.list');
        });
    });
});
