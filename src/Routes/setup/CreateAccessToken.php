<?php

namespace Tualo\Office\MicrosoftMail\Routes\Setup;

use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\MicrosoftMail\GraphHelper;



class CreateAccessToken implements IRoute
{
    public static function register()
    {
        BasicRoute::add('/microsoft-mail/setup/accesstoken', function ($matches) {
            echo 1;
            try {
                $db = App::get('session')->getDB();

                GraphHelper::initializeGraphForUserAuth();
                $payload = json_decode(@file_get_contents('php://input'), true);
                if (!isset($payload['device_code'])) {
                    $payload['device_code'] = '';
                }
                $tokenRespone = GraphHelper::getAccessToken($payload['device_code']);


                App::result('token',  $tokenRespone);
                if (isset($tokenRespone['expires_in'])) {
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
                    App::result('success',  true);
                }
            } catch (\Exception $e) {
                App::result('error', $e->getMessage());
            }
            App::contenttype('application/json');
        }, ['post', 'get'], true);
    }
}
