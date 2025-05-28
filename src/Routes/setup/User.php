<?php

namespace Tualo\Office\MicrosoftMail\Routes\Setup;

use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\MicrosoftMail\GraphHelper;
use Microsoft\Graph\Generated\Models\User;
use Tualo\Office\MicrosoftMail\API;
use Tualo\Office\MicrosoftMail\DummyUser;


class UserRoute implements IRoute
{
    public static function register()
    {

        BasicRoute::add('/microsoft-mail/setup/user', function ($matches) {
            try {

                $config = json_decode(API::env('primary'), true);
                App::result('cnf',  $config);
                App::result('step',  __LINE__);
                App::result('cnfy',  $config['access_token']);

                GraphHelper::initializeGraphForUserAuth($config['access_token']);
                App::result('step',  __LINE__);
                if (is_null(API::env('primary'))) {
                    throw new \Exception('config environment not found');
                }
                App::result('step',  __LINE__);
                GraphHelper::setAccessToken($config['access_token']);

                App::result('step',  __LINE__);
                $user = GraphHelper::getUser();

                if ($user instanceof User) {
                    App::result(
                        'data',
                        [
                            'mail' => $user->getMail(),
                            'displayName' => $user->getDisplayName(),
                            'principal' => $user->getUserPrincipalName()
                        ]
                    );
                } else if ($user instanceof DummyUser) {
                    App::result(
                        'data',
                        [
                            'mail' => 'Dummy User',
                            'displayName' => 'Dummy User',
                            'principal' => 'Dummy User',
                        ]
                    );
                }
                App::result('step',  __LINE__);


                App::result('step',  __LINE__);
                App::result('success',  true);
            } catch (\Exception $e) {
                App::result('error', $e->getMessage());
            }
            App::contenttype('application/json');
        }, ['get'], true);
    }
}
