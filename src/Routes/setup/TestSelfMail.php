<?php

namespace Tualo\Office\MicrosoftMail\Routes\Setup;

use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\MSGraph\API;
use Tualo\Office\MicrosoftMail\MSGraphMail;


class TestSelfMail extends \Tualo\Office\Basic\RouteWrapper
{
    public static function register()
    {

        BasicRoute::add('/microsoft-mail/setup/testmail', function ($matches) {
            try {

                $user = API::getMe();
                $recipient = $user['mail'] ?? $user['userPrincipalName'] ?? null;
                if ($recipient === null) {
                    throw new \Exception('No recipient address found.');
                }
                MSGraphMail::get()
                    ->setSubject(((new \DateTime('now'))->format('Y-m-d')) . ': Testing Microsoft Graph ')
                    ->setBody('Hello world!')
                    ->addAddress($recipient)
                    ->addAttachmentData('', ' Hello ' . ($user['displayName'] ?? '') . ' ', 'text/plain', 'muster.datei.txt')
                    ->send();
                App::result('success',  true);
            } catch (ODataError $e) {
                App::result('error', $e->getError()->getMessage());
            } catch (\Exception $e) {
                App::result('msg', $e->getMessage());
            }
            App::contenttype('application/json');
        }, ['get'], true);
    }
}
