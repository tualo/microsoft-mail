<?php

namespace Tualo\Office\Mail\Routes;

use Tualo\Office\Mail\OutgoingMail;
use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\DS\DSModel;
use Tualo\Office\Mail\GraphHelper;






function displayAccessToken(): void {
    try {
        $token = GraphHelper::getUserToken();
        print('User token: '.$token.PHP_EOL.PHP_EOL);
    } catch (Exception $e) {
        print('Error getting access token: '.$e->getMessage().PHP_EOL.PHP_EOL);
    }
}

function listInbox(): void {
    try {
        $messages = GraphHelper::getInbox();

        // Output each message's details
        foreach ($messages->getValue() as $message) {
            print('Message: '.$message->getSubject().PHP_EOL);
            print('  From: '.$message->getFrom()->getEmailAddress()->getName().PHP_EOL);
            $status = $message->getIsRead() ? "Read" : "Unread";
            print('  Status: '.$status.PHP_EOL);
            print('  Received: '.$message->getReceivedDateTime()->format(\DateTimeInterface::RFC2822).PHP_EOL);
        }

        $nextLink = $messages->getOdataNextLink();
        $moreAvailable = isset($nextLink) && $nextLink != '' ? 'True' : 'False';
        print(PHP_EOL.'More messages available? '.$moreAvailable.PHP_EOL.PHP_EOL);
    } catch (Exception $e) {
        print('Error getting user\'s inbox: '.$e->getMessage().PHP_EOL.PHP_EOL);
    }
}

function greetUser(): void {
    try {
        $user = GraphHelper::getUser();
        print('Hello, '.$user->getDisplayName().'!'.PHP_EOL);

        // For Work/school accounts, email is in Mail property
        // Personal accounts, email is in UserPrincipalName
        $email = $user->getMail();
        if (empty($email)) {
            $email = $user->getUserPrincipalName();
        }
        print('Email: '.$email.PHP_EOL.PHP_EOL);
    } catch (Exception $e) {
        print('Error getting user: '.$e->getMessage().PHP_EOL.PHP_EOL);
    }
}

function sendMail(): void {
    try {
        // Send mail to the signed-in user
        // Get the user for their email address
        $user = GraphHelper::getUser();

        // For Work/school accounts, email is in Mail property
        // Personal accounts, email is in UserPrincipalName
        $email = $user->getMail();
        if (empty($email)) {
            $email = $user->getUserPrincipalName();
        }

        GraphHelper::sendMail('Testing Microsoft Graph', 'Hello world!', $email);

        print(PHP_EOL.'Mail sent.'.PHP_EOL.PHP_EOL);
    } catch (Exception $e) {
        print('Error sending mail: '.$e->getMessage().PHP_EOL.PHP_EOL);
    }
}
function initializeGraph(): void {
    GraphHelper::initializeGraphForUserAuth();
}


class Test implements IRoute
{

    public static function register()
    {
        
        BasicRoute::add('/microsoft-mail/test', function ($matches) {
            try{
                

                initializeGraph();
                listInbox();




            } catch (\Exception $e) {
                echo $e->getMessage();
            }

        }, ['get', 'post'], true);
    }
}
