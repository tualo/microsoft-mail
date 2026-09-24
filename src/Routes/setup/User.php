<?php

namespace Tualo\Office\MicrosoftMail\Routes\Setup;

use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\MSGraph\API;


class UserRoute extends \Tualo\Office\Basic\RouteWrapper
{
    public static function register()
    {

        BasicRoute::add('/microsoft-mail/setup/user', function ($matches) {
            try {

                $user = API::getMe();
                if (($user['statusCode'] ?? 500) !== 200) {
                    throw new \Exception($user['error']['message'] ?? 'Unable to load Microsoft Graph user.');
                }
                App::result('data', [
                    'mail' => $user['mail'] ?? '',
                    'displayName' => $user['displayName'] ?? '',
                    'principal' => $user['userPrincipalName'] ?? ''
                ]);
                App::result('success',  true);
            } catch (\Exception $e) {
                App::result('error', $e->getMessage());
            }
            App::contenttype('application/json');
        }, ['get'], true);
    }
}
