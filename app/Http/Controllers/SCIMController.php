<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use Cloudstek\SCIM\FilterParser\FilterParser;
use Cloudstek\SCIM\FilterParser\AST;
use Log;

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

    public function schema(Request $request, string $tenant_id) {

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
                    case 'externalId':
                        $users = $users->where('external_id', $filterValue);
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

        return $this->response(new UserCollection($users));
    }

    public function user(Request $request, string $tenant_id, string $user_id) {
        $user = User::where('tenant_id', $tenant_id)->where('id', $user_id)->first();
        if(!$user)
            return $this->error('notFound', 404, 'User ID not found');
        return $this->response(new UserResource($user));
    }

    public function createUser(Request $request, string $tenant_id) {
        if(!in_array('urn:ietf:params:scim:schemas:core:2.0:User', $request->input('schemas'))) {
            return $this->error('invalidOperation', 400);
        }

        Log::info("CREATING USER: ".json_encode($request->input()));

        # First check if the user already exists and return an error if so
        $username = $request->input('userName');
        $user = User::where('tenant_id', $tenant_id)->where('username', $username)->first();

        if($user) {
            return $this->error('userExists', 409, 'A user with this username already exists');
        }

        $email = false;
        $emails = $request->input('emails');
        if($emails && count($emails) >= 1) {
            $email = $emails[0];
        }

        $user = null;

        if($email) {
            # Check if the user exists by email address, and allow updating that user
            $user = User::where('tenant_id', $tenant_id)->where('email', $email['value'])->first();
        }

        if(!$user) {
            $user = new User();
            $user->tenant_id = $tenant_id;
        }

        $user->username = $username;
        $user->password = 'none';
        $user->external_id = $request->input('externalId', '');
        $user->first_name = $request->input('name.givenName', '');
        $user->last_name = $request->input('name.familyName', '');
        $user->email = ($email ? $email['value'] : null);
        $user->active = $request->input('active', true);
        $user->scim_user_info = json_encode($request->all(), JSON_PRETTY_PRINT+JSON_UNESCAPED_SLASHES);
        $user->save();

        return $this->response(new UserResource($user), 201);
    }

    public function updateUser(Request $request, string $tenant_id, string $user_id) {
        if(!in_array('urn:ietf:params:scim:schemas:core:2.0:User', $request->input('schemas'))) {
            return $this->error('invalidOperation', 400);
        }

        $user = User::where('tenant_id', $tenant_id)->where('id', $user_id)->first();
        if(!$user)
            return $this->error('notFound', 404, 'User ID not found');

        $email = false;
        $emails = $request->input('emails');
        if(count($emails) >= 1) {
            $email = $emails[0];
        }

        $user->username = $request->input('userName');
        $user->password = 'none';
        $user->external_id = $request->input('externalId', '');
        $user->first_name = $request->input('name.givenName');
        $user->last_name = $request->input('name.familyName');
        $user->email = ($email ? $email['value'] : null);
        $user->active = $request->input('active');
        $user->scim_user_info = json_encode($request->all(), JSON_PRETTY_PRINT+JSON_UNESCAPED_SLASHES);
        $user->save();

        return $this->response(new UserResource($user), 200);
    }

    # Okta uses PATCH only to deactivate users
    public function patchUser(Request $request, string $tenant_id, string $user_id) {
        if(!in_array('urn:ietf:params:scim:api:messages:2.0:PatchOp', $request->input('schemas'))) {
            return $this->error('invalidOperation', 400);
        }

        $user = User::where('tenant_id', $tenant_id)->where('id', $user_id)->first();
        if(!$user)
            return $this->error('notFound', 404, 'User ID not found');

        $operations = $request->input('Operations');
        foreach($operations as $op) {
            if($op['op'] == 'replace' && isset($op['value']['active'])) {
                $user->active = (bool)$op['value']['active'];
            }
        }

        $user->save();

        return $this->response(new UserResource($user), 200);
    }

    # Azure requires DELETE support
    public function deleteUser(Request $request, string $tenant_id, string $user_id) {

        $user = User::where('tenant_id', $tenant_id)->where('id', $user_id)->first();
        if(!$user)
            return $this->error('notFound', 404, 'User ID not found');

        $user->delete();

        return $this->response('', 204);
    }

    public function groups(Request $request, string $tenant_id) {
        return $this->response([
            'schemas' => ['urn:ietf:params:scim:api:messages:2.0:ListResponse'],
            'Resources' => [],
            'totalResults' => 0,
            'itemsPerPage' => 1,
            'startIndex' => 1,
        ]);
    }
}
