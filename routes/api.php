<?php

use Fazzinipierluigi\LaraccoonLayouts\Http\Controllers\LayoutController;
use Illuminate\Support\Facades\Route;

Route::get('/page/{page_key}', [LayoutController::class, 'getByPage']);
Route::post('/store', [LayoutController::class, 'store']);
Route::put('/{id}', [LayoutController::class, 'update']);
Route::delete('/{id}', [LayoutController::class, 'destroy']);
Route::post('/{id}/default', [LayoutController::class, 'setDefault']);
Route::post('/{id}/copy', [LayoutController::class, 'copy']);
