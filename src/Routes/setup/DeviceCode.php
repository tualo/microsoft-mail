<?php

namespace Tualo\Office\MicrosoftMail\Routes\Setup;

use Tualo\Office\Mail\OutgoingMail;
use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\DS\DSModel;
use Tualo\Office\MicrosoftMail\GraphHelper;


class DeviceCode extends \Tualo\Office\Basic\RouteWrapper
{

    public static function register()
    {
        BasicRoute::add('/microsoft-mail/setup/devicelogin', function ($matches) {
            try {
                GraphHelper::initializeGraphForUserAuth();

                $deviceCodeResponse = GraphHelper::getDeviceLogin();
                App::result('verification_uri',  $deviceCodeResponse['verification_uri']);
                App::result('device_code',  $deviceCodeResponse['device_code']);
                App::result('user_code', $deviceCodeResponse['user_code']);

                App::result('expires_in', $deviceCodeResponse['expires_in']);
                App::result('interval', $deviceCodeResponse['interval']);

                App::result('message', $deviceCodeResponse['message']);
                App::result('success', true);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
            App::contenttype('application/json');
        }, ['get', 'post'], true);
    }
}
