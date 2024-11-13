<?php

namespace Tualo\Office\MicrosoftMail\Routes\Setup;

use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\MicrosoftMail\GraphHelper;
use Microsoft\Graph\Generated\Models\User;
use Tualo\Office\MicrosoftMail\API;
use Microsoft\Graph\Generated\Models\ODataErrors\ODataError;


class RefreshToken implements IRoute
{
    public static function register()
    {


        BasicRoute::add('/microsoft-mail/setup/refresh', function ($matches) {
            try {
                $db = App::get('session')->getDB();
                if (is_null(API::env('primary'))) {
                    throw new \Exception('config environment not found');
                }

                GraphHelper::initializeGraphForUserAuth();
                $list = $db->direct('select * from msgraph_environments where expires + interval - 600 second < now()');
                foreach ($list as $item) {
                    $config = json_decode($item['val'], true);
                    if (isset($config['refresh_token'])) {
                        $tokenRespone = GraphHelper::getAccessTokenByRefreshToken($config['refresh_token']);
                        $sql = '
                                insert into msgraph_environments 
                                    (id,val,login,updated,expires) 
                                values 
                                    ("primary",{object},"*",now(),now() + interval ' . $tokenRespone['expires_in'] . ' second  )
                                on duplicate key update 
                                    val=values(val),
                                    login=values(login),
                                    updated=values(updated),
                                    expires=values(expires)
                                ';
                        $db->direct($sql, [
                            'object' => json_encode($tokenRespone)
                        ]);
                    }
                }




                App::result('success',  true);
            } catch (\Exception $e) {
                App::result('error', $e->getMessage());
            }
            App::contenttype('application/json');
        }, ['get'], true);
    }
}
