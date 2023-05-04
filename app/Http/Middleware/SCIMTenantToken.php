<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\TenantAccessToken;

class SCIMTenantToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $valid = false;

        $header = $request->header('Authorization');
        if($header && preg_match('/Bearer (.+)/', $header, $match)) {
            $token = TenantAccessToken::findFromToken($match[1]);
            if($token) {
                // Make sure this token matches the tenant ID in the request
                if((int)$request->route('tenant') == $token->tenant->id) {
                    $valid = true;
                }
            }
        }


        if(!$valid) {
            return response()->json([
                    'schemas' => ['urn:ietf:params:scim:api:messages:2.0:Error'],
                    'status' => 403,
                ], 403)
                ->header('Content-Type', 'application/scim+json');

        }

        return $next($request);
    }
}
