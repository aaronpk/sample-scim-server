<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SCIMController;

/*
|--------------------------------------------------------------------------
| SCIM Routes
|--------------------------------------------------------------------------
*/

Route::get('/{tenant}/Schema', [SCIMController::class, 'schema']);

Route::get('/{tenant}/Users', [SCIMController::class, 'users']);
Route::get('/{tenant}/Users/{user_id}', [SCIMController::class, 'user']);
Route::post('/{tenant}/Users', [SCIMController::class, 'createUser']);
Route::put('/{tenant}/Users/{user_id}', [SCIMController::class, 'updateUser']);
Route::patch('/{tenant}/Users/{user_id}', [SCIMController::class, 'patchUser']);
Route::delete('/{tenant}/Users/{user_id}', [SCIMController::class, 'deleteUser']);

Route::get('/{tenant}/Groups', [SCIMController::class, 'groups']);
