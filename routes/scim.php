<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SCIM Routes
|--------------------------------------------------------------------------
*/

Route::get('/{tenant}/Users', function (Request $request) {
    return 'Hello';
});
