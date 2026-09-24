<?php

namespace Tualo\Office\MicrosoftMail\Routes\Setup;

use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\MSGraph\API;


class RefreshToken extends \Tualo\Office\Basic\RouteWrapper
{
    public static function register()
    {


        BasicRoute::add('/microsoft-mail/setup/refresh', function ($matches) {
            try {
                API::refreshAccessToken();




                App::result('success',  true);
            } catch (\Exception $e) {
                App::result('error', $e->getMessage());
            }
            App::contenttype('application/json');
        }, ['get'], true);
    }
}
