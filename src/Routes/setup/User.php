<?php

namespace Tualo\Office\MicrosoftMail\Routes\Setup;

use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\MicrosoftMail\GraphHelper;
use Microsoft\Graph\Generated\Models\User;
use Tualo\Office\MicrosoftMail\API;



class UserRoute implements IRoute
{
    public static function register()
    {

        BasicRoute::add('/microsoft-mail/setup/user', function ($matches) {
            try {

                GraphHelper::initializeGraphForUserAuth();
                if (is_null(API::env('primary'))){
                    throw new \Exception('config environment not found');
                }
                $config = json_decode(API::env('primary') ,true);
                App::result('cnf',  $config );
                GraphHelper::setAccessToken( $config['access_token'] );

                $user = GraphHelper::getUser();
 
                App::result('data', [
                    'mail' => $user->getMail(),
                    'displayName' => $user->getDisplayName(),
                    'principal' => $user->getUserPrincipalName()
                ]
                );

                App::result('success',  true);
                
                
            } catch (\Exception $e) {
                App::result('error', $e->getMessage());
            }
            App::contenttype('application/json');
        }, ['get'], true);
    }
}
