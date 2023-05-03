<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SCIMController;

/*
|--------------------------------------------------------------------------
| SCIM Routes
|--------------------------------------------------------------------------
*/

Route::get('/{tenant}/Users', [SCIMController::class, 'users']);
Route::get('/{tenant}/Users/{user_id}', [SCIMController::class, 'user']);
