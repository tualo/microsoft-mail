<?php

namespace Tualo\Office\MicrosoftMail;

use Exception;
use Microsoft\Graph\Generated\Models;
use Microsoft\Graph\Generated\Models\User;
use Microsoft\Graph\Generated\Users\Item\MailFolders\Item\Messages\MessagesRequestBuilderGetQueryParameters;
use Microsoft\Graph\Generated\Users\Item\MailFolders\Item\Messages\MessagesRequestBuilderGetRequestConfiguration;
use Microsoft\Graph\Generated\Users\Item\SendMail\SendMailPostRequestBody;
use Microsoft\Graph\Generated\Users\Item\UserItemRequestBuilderGetQueryParameters;
use Microsoft\Graph\Generated\Users\Item\UserItemRequestBuilderGetRequestConfiguration;
use Microsoft\Graph\GraphRequestAdapter;
use Microsoft\Graph\GraphServiceClient;
use Microsoft\Kiota\Abstractions\Authentication\BaseBearerTokenAuthenticationProvider;
use Tualo\Office\MicrosoftMail\DeviceCodeTokenProvider;
use Tualo\Office\Basic\TualoApplication as App;

use Microsoft\Graph\Generated\Models\Message;
use Microsoft\Graph\Generated\Models\ItemBody;
use Microsoft\Graph\Generated\Models\BodyType;
use Microsoft\Graph\Generated\Models\Recipient;
use Microsoft\Graph\Generated\Models\EmailAddress;
use Microsoft\Graph\Generated\Models\FileAttachment;
use Microsoft\Kiota\Abstractions\ApiException;
use GuzzleHttp\Psr7\Stream;
use Microsoft\Graph\Generated\Models\ODataErrors\ODataError;

// require_once 'DeviceCodeTokenProvider.php';


class GraphHelper
{
    private static string $clientId = '';
    private static string $tenantId = '';
    private static string $accessToken = '';
    private static string $graphUserScopes = '';
    private static DeviceCodeTokenProvider $tokenProvider;
    private static GraphServiceClient $userClient;

    // SingleValueExtendedProperties

    public static function initializeGraphForUserAuth(string $accessToken = ""): void
    {
        $db = App::get('session')->getDB();
        $clientId = $db->singleValue('select val from  msgraph_setup where id = "clientId"', [], 'val');
        $tenantId = $db->singleValue('select val from  msgraph_setup where id = "tenantId"', [], 'val');
        // print_r([$clientId, $tenantId]);
        GraphHelper::$clientId = App::configuration('microsoft-mail', 'clientId', $clientId);
        GraphHelper::$tenantId = App::configuration('microsoft-mail', 'tenantId', $tenantId);
        GraphHelper::$graphUserScopes = 'offline_access user.read mail.read mail.send';
        GraphHelper::$tokenProvider = new DeviceCodeTokenProvider(
            GraphHelper::$clientId,
            GraphHelper::$tenantId,
            GraphHelper::$graphUserScopes,
            $accessToken
        );

        self::$accessToken = $accessToken;
        $authProvider = new BaseBearerTokenAuthenticationProvider(GraphHelper::$tokenProvider);
        $adapter = new GraphRequestAdapter($authProvider);
        GraphHelper::$userClient = GraphServiceClient::createWithRequestAdapter($adapter);
    }

    public static function getDeviceLogin(): mixed
    {
        return GraphHelper::$tokenProvider
            ->deviceLogin();
    }

    public static function setAccessToken($token): void
    {
        GraphHelper::$tokenProvider
            ->setAccessToken($token);
    }

    public static function getAccessToken($device_code): mixed
    {
        return GraphHelper::$tokenProvider
            ->getAccessToken($device_code);
    }

    public static function getAccessTokenByRefreshToken($refresh_token): mixed
    {
        return GraphHelper::$tokenProvider
            ->getAccessTokenByRefreshToken($refresh_token);
    }

    public static function getUserToken(): string
    {
        return GraphHelper::$tokenProvider
            ->getAuthorizationTokenAsync('https://graph.microsoft.com')->wait();
    }


    public static function getUser(): User|null
    {
        GraphHelper::initializeGraphForUserAuth(self::$accessToken);
        GraphHelper::refreshToken();
        try {
            $configuration = new UserItemRequestBuilderGetRequestConfiguration();
            $configuration->queryParameters = new UserItemRequestBuilderGetQueryParameters();
            $configuration->queryParameters->select = ['displayName', 'mail', 'userPrincipalName'];
            $result = GraphHelper::$userClient->me()->get($configuration)->wait();
            return $result;
        } catch (ApiException $ex) {
            echo $ex->getMessage();
        } catch (ODataError $e) {
            throw  new Exception($e->getError()->getMessage());
        }

        return null;
    }

    public static function getInbox(): Models\MessageCollectionResponse
    {
        $configuration = new MessagesRequestBuilderGetRequestConfiguration();
        $configuration->queryParameters = new MessagesRequestBuilderGetQueryParameters();
        // Only request specific properties
        $configuration->queryParameters->select = ['from', 'isRead', 'receivedDateTime', 'subject'];
        // Sort by received time, newest first
        $configuration->queryParameters->orderby = ['receivedDateTime DESC'];
        // Get at most 25 results
        $configuration->queryParameters->top = 25;
        return GraphHelper::$userClient->me()
            ->mailFolders()
            ->byMailFolderId('inbox')
            ->messages()
            ->get($configuration)->wait();
    }


    public static function sendMail(
        string $subject,
        string $bodyText,
        string $bodyHtml,
        string $recipient,
        array $attachments = [],
        string $listUnsubscribePost = ""
    ): void {

        try {
            self::refreshToken();
            $requestBody = new SendMailPostRequestBody();
            $message = new Message();
            $message->setSubject($subject);

            if ($bodyText != '') {
                $messageBody = new ItemBody();
                $messageBody->setContentType(new BodyType('text'));
                $messageBody->setContent($bodyText);
                $message->setBody($messageBody);
            }

            if ($bodyHtml != '') {
                $messageBody = new ItemBody();
                $messageBody->setContentType(new BodyType('html'));
                $messageBody->setContent($bodyHtml);
                $message->setBody($messageBody);
            }



            $toRecipientsRecipient1 = new Recipient();
            $toRecipientsRecipient1EmailAddress = new EmailAddress();
            $toRecipientsRecipient1EmailAddress->setAddress($recipient);
            $toRecipientsRecipient1->setEmailAddress($toRecipientsRecipient1EmailAddress);
            $toRecipientsArray[] = $toRecipientsRecipient1;


            $attachmentsArray = [];
            foreach ($attachments as $attachment) {


                $attachmentsAttachment1 = new FileAttachment();
                if (isset($attachment['isInline'])) {
                    $attachmentsAttachment1->setIsInline($attachment['isInline']);
                }
                $attachmentsAttachment1->setName($attachment['name']);
                if (isset($attachment['isInline'])) {
                    $attachmentsAttachment1->setContentType($attachment['contentType']);
                }
                if (isset($attachment['content'])) {
                    $attachmentsAttachment1->setContentBytes(\GuzzleHttp\Psr7\Utils::streamFor(base64_encode($attachment['content'])));
                }
                $attachmentsArray[] = $attachmentsAttachment1;
            }
            if (count($attachmentsArray) > 0)
                $message->setAttachments($attachmentsArray);

            $message->setToRecipients($toRecipientsArray);


            // Füge den List-Unsubscribe-Post-Header hinzu, falls angegeben
            if ($listUnsubscribePost !== null) {
                $extendedProperty = new Models\SingleValueLegacyExtendedProperty();
                $extendedProperty->setId("String 0x1045"); // Standard-ID für benutzerdefinierte Header
                $extendedProperty->setValue($listUnsubscribePost);
                $message->setSingleValueExtendedProperties([$extendedProperty]);
            }

            $requestBody->setMessage($message);
            GraphHelper::$userClient->me()->sendMail()->post($requestBody)->wait();
        } catch (ApiException $ex) {
            echo $ex->getMessage();
        } catch (ODataError $e) {
            echo $e->getError()->getMessage();
            throw  new Exception($e->getError()->getMessage());
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    public static function refreshToken(): void
    {
        try {
            $db = App::get('session')->getDB();
            if (is_null(API::env('primary'))) {
                throw new \Exception('config environment not found');
            }

            // GraphHelper::initializeGraphForUserAuth();
            $list = $db->direct('select * from msgraph_environments where expires + interval - 10 second < now()');
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
                    GraphHelper::setAccessToken($tokenRespone['access_token']);
                }
            }
        } catch (ODataError $e) {
            throw  new Exception($e->getError()->getMessage());
        }
    }
}
