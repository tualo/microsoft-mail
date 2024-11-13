<?php

namespace Tualo\Office\MicrosoftMail\Routes\Setup;

use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\MicrosoftMail\GraphHelper;
use Microsoft\Graph\Generated\Models\User;
use Tualo\Office\MicrosoftMail\API;
use Microsoft\Graph\Generated\Models\ODataErrors\ODataError ;


class TestSelfMail implements IRoute
{
    public static function register()
    {

        BasicRoute::add('/microsoft-mail/setup/testmail', function ($matches) {
            try {

                GraphHelper::initializeGraphForUserAuth();
                if (is_null(API::env('primary'))){
                    throw new \Exception('config environment not found');
                }
                $config = json_decode(API::env('primary') ,true);
                GraphHelper::setAccessToken( $config['access_token'] );
                $user = GraphHelper::getUser();
                GraphHelper::sendMail(
                    ((new \DateTime('now'))->format('Y-m-d')).': Testing Microsoft Graph ', 

                    'Hello world!', 
                    '<html><body><h1>Hello '.$user->getDisplayName().'</h1></body></html>',

                    $user->getMail(),
                    
                    [[
                        'name' => "muster.datei.txt",
                        'contentType' => 'text/plain',
                        'content' => (' Hello '.$user->getDisplayName().' ')
                    ]]
                    

                );
                App::result('success',  true);
            }catch( ODataError $e ){
                App::result('error', $e->getError()->getMessage());
            } catch (\Exception $e) {
                App::result('msg', $e->getMessage());
            }
            App::contenttype('application/json');
        }, ['get'], true);
    }
}
