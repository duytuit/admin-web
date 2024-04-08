<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use stdClass;

trait ApiUser
{
    protected static $apiUser = null;

    /**
     * Returns Authenticated User Details
     *
     * @return mixed $user;
     */
    protected function getApiUser()
    {
        if (self::$apiUser === null) {
            $bearer = request()->bearerToken();

            if ($bearer) {
                $jti = (new \Lcobucci\JWT\Parser())->parse($bearer)->getHeader('jti');

                $access = \Laravel\Passport\Token::where('id', $jti)->first();
                $guard = $access->name;

                $provider = Config::get('auth.guards.' . $guard . '.provider');
                $model = Config::get('auth.providers.' . $provider . '.model');

                $class = new $model();

                $user = $class->find($access->user_id);

                $user->type = $this->getApiUserType($user);
                $user->group_id = $user->group_id ?? [];

                self::$apiUser = $user;
            } else {
                $user = (object)[
                    'id' => null,
                    'type' => null,
                    'name' => null,
                    'email' => null,
                    'phone' => null,
                    'avatar' => null,
                    'group_id' => [],
                ];

                self::$apiUser = $user;
            }
        }

        return self::$apiUser;
    }

    protected function getApiUserType($user)
    {
        $class = get_class($user);
        $types = Config::get('auth.types');
        $type = 'user';

        foreach ($types as $key => $value) {
            if ($class == $value) {
                $type = $key;
                break;
            }
        }

        return $type;
    }

    /**
     * Check whether user is owner of model based on user_id column
     * @param $id
     * @return array
     */
    protected function isOwner($id, $model = null)
    {
        $result = [
            'owner' => true,
            'model' => [],
            'error_code' => 0,
            'message' => '',
            'status_code' => 0
        ];

        self::getApiUser();

        try {
            $model = $model ?? $this->model;
            $item = $model->findOrFail($id);
            $result['model'] = $item;

            if ($item->user_id !== self::$apiUser->id) {
                $result['owner'] = false;
                $result['message'] = 'Access denied';
                $result['error_code'] = $result['status_code'] = Config::get('code.unauthorized');
            }
        } catch (ModelNotFoundException $exception) {
            $result['owner'] = false;
            $result['message'] = 'Model Not Found Exception';
            $result['error_code'] = $result['status_code'] = Config::get('code.resource_not_found');
        }

        return $result;
    }
}
