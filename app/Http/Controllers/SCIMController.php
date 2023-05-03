<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use Cloudstek\SCIM\FilterParser\FilterParser;
use Cloudstek\SCIM\FilterParser\AST;

class SCIMController extends Controller
{
    private function response($data, $code=200) {
        return response()->json($data, $code)
          ->header('Content-Type', 'application/scim+json');
    }

    private function error($type, $code=400, $detail=null) {
        return response()->json([
            'schemas' => ['urn:ietf:params:scim:api:messages:2.0:Error'],
            'status' => (string)$code,
            'scimType' => $type,
            'detail' => $detail,
        ], $code)
          ->header('Content-Type', 'application/scim+json');
    }

    public function users(Request $request, string $tenant_id) {

        if($request->input('filter')) {
            $filterParser = new FilterParser();
            try {
                $ast = $filterParser->parse($request->input('filter'));
            } catch(\Exception $e) {
                return $this->error('invalidFilter', 400, 'Unable to parse filter syntax');
            }

            if($ast && $ast::class == AST\Comparison::class && $ast->getOperator()->value == 'eq') {

                $attributePath = $ast->getAttributePath();
                if(count($attributePath) > 1) {
                    return $this->error('invalidFilter', 400, 'Unrecognized attribute path');
                }

                $fieldName = $ast->getAttributePath()->getNames()[0];
                $filterValue = $ast->getValue();

                $users = User::where('tenant_id', $tenant_id);

                switch($fieldName) {
                    case 'userName':
                        $users = $users->where('username', $filterValue);
                        break;
                    default:
                        return $this->error('invalidFilter', 400, 'Unrecognized filter attribute');
                }

                $users = $users->get();

            } else {
                return $this->error('invalidFilter', 400, 'Unrecognized filter operator');
            }
        } else {
            $users = User::where('tenant_id', $tenant_id)->get();
        }

        return new UserCollection($users);
    }

    public function user(Request $request, string $tenant_id, string $user_id) {
        $user = User::where('tenant_id', $tenant_id)->where('id', $user_id)->first();
        if(!$user)
            return $this->response(['error' => 'not_found'], 404);
        return new UserResource($user);
    }
}
