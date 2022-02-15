<?php

namespace App\Helpers\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AccessHelper
{
    const USER_ACCESS_TMP = 'menu.user.access.{user_id}.{accesses}';

    /**
     * @param $actions
     * @param  Model  $model
     * @return bool
     * @throws \Exception
     */
    public static function canOrFail($actions, Model $model): bool
    {
        if (!is_array($actions)) {
            $actions = [$actions];
        }

        foreach ($actions as $action) {
            if (AuthHelper::getAuthUser()->can($action, $model)) {
                return true;
            }
        }

        abort(403, __('crud.access denied'));
    }

    /**
     * @param  int  $userId
     * @param  array  $actions
     * @return string
     */
    protected static function getAccessCacheName(int $userId, array $actions): string
    {
        return str_replace(
            [
                'user_id',
                'accesses'
            ],
            [
                $userId,
                implode('_', $actions)
            ],
            self::USER_ACCESS_TMP
        );
    }

    /**
     * @param $actions
     * @param  Model  $model
     * @return bool
     * @throws \Exception
     */
    public static function isCan($actions, Model $model): bool
    {
        if(!Auth::check()) {
            return false;
        }

        if (!is_array($actions)) {
            $actions = [$actions];
        }

        $userId = Auth()->user()->id;

        $accessCacheName = self::getAccessCacheName($userId, $actions);

        $isCan = Cache::get($accessCacheName);

        if($isCan === null) {
            $isCan = false;

            foreach ($actions as $action) {
                if (AuthHelper::getAuthUser()->can($action, $model)) {
                    $isCan = true;
                }
            }

            Cache::put($accessCacheName, $isCan);
        }

        return $isCan;
    }
}
