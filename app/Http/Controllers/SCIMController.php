<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;

class SCIMController extends Controller
{
    private function response($data, $code=200) {
        return response()->json($data, $code)
          ->header('Content-Type', 'application/scim+json');
    }

    public function users(Request $request, string $tenant_id) {
        $users = User::where('tenant_id', $tenant_id)->get();
        return new UserCollection($users);
    }

    public function user(Request $request, string $tenant_id, string $user_id) {
        $user = User::where('tenant_id', $tenant_id)->where('id', $user_id)->first();
        if(!$user)
            return $this->response(['error' => 'not_found'], 404);
        return new UserResource($user);
    }
}
