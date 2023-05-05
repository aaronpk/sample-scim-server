<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\User;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function users(Request $request, string $tenant_id) {
        $users = User::where('tenant_id', $tenant_id)
            ->where('active', 1)
            ->get();

        return response()->json(['users' => $users]);
    }
}
