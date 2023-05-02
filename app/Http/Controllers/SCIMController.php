<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserCollection;

class SCIMController extends Controller
{
    private function response($data) {
        return response()->json($data)
          ->header('Content-Type', 'application/scim+json');
    }

    public function users(Request $request) {
        $users = User::all();
        return new UserCollection($users);
    }
}
