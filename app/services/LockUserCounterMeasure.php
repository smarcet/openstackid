<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 11/13/13
 * Time: 2:15 PM
 */

namespace services;

use auth\OpenIdUser;
use Exception;
use Log;
use openid\services\OpenIdServiceCatalog;
use utils\services\Registry;

class LockUserCounterMeasure implements \utils\services\ISecurityPolicyCounterMeasure
{

    public function trigger(array $params = array())
    {
        try {
            if (!isset($params["user_identifier"])) return;
            $user_identifier = $params["user_identifier"];
            $server_configuration = Registry::getInstance()->get(OpenIdServiceCatalog::ServerConfigurationService);
            $user_service = \utils\services\Registry::getInstance()->get(OpenIdServiceCatalog::UserService);

            $user = OpenIdUser::where('external_id', '=', $user_identifier)->first();
            if(is_null($user))
                return;
            //apply lock policy
            if ($user->login_failed_attempt < $server_configuration->getConfigValue("MaxFailed.Login.Attempts"))
                $user_service->updateFailedLoginAttempts($user->id);
            else {
                $user_service->lockUser($user->id);
            }
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }
}