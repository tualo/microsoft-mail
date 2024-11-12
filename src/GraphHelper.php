<?php
namespace Tualo\Office\MicrosoftMail;

use Microsoft\Graph\Generated\Models;
use Microsoft\Graph\Generated\Users\Item\MailFolders\Item\Messages\MessagesRequestBuilderGetQueryParameters;
use Microsoft\Graph\Generated\Users\Item\MailFolders\Item\Messages\MessagesRequestBuilderGetRequestConfiguration;
use Microsoft\Graph\Generated\Users\Item\SendMail\SendMailPostRequestBody;
use Microsoft\Graph\Generated\Users\Item\UserItemRequestBuilderGetQueryParameters;
use Microsoft\Graph\Generated\Users\Item\UserItemRequestBuilderGetRequestConfiguration;
use Microsoft\Graph\GraphRequestAdapter;
use Microsoft\Graph\GraphServiceClient;
use Microsoft\Kiota\Abstractions\Authentication\BaseBearerTokenAuthenticationProvider;
use DeviceCodeTokenProvider;

// require_once 'DeviceCodeTokenProvider.php';

class GraphHelper {
    private static string $clientId = '';
    private static string $tenantId = '';
    private static string $graphUserScopes = '';
    private static DeviceCodeTokenProvider $tokenProvider;
    private static GraphServiceClient $userClient;

    public static function initializeGraphForUserAuth(): void {
        GraphHelper::$clientId = App::configuration('microsoft-mail', 'clientId', "");
        GraphHelper::$tenantId = App::configuration('microsoft-mail', 'tenantId', "");
        GraphHelper::$graphUserScopes = 'user.read mail.read mail.send';

        GraphHelper::$tokenProvider = new DeviceCodeTokenProvider(
            GraphHelper::$clientId,
            GraphHelper::$tenantId,
            GraphHelper::$graphUserScopes);
        $authProvider = new BaseBearerTokenAuthenticationProvider(GraphHelper::$tokenProvider);
        $adapter = new GraphRequestAdapter($authProvider);
        GraphHelper::$userClient = GraphServiceClient::createWithRequestAdapter($adapter);
    }


    public static function getUserToken(): string {
        return GraphHelper::$tokenProvider
            ->getAuthorizationTokenAsync('https://graph.microsoft.com')->wait();
    }
    

    public static function getUser(): Models\User {
        $configuration = new UserItemRequestBuilderGetRequestConfiguration();
        $configuration->queryParameters = new UserItemRequestBuilderGetQueryParameters();
        $configuration->queryParameters->select = ['displayName','mail','userPrincipalName'];
        return GraphHelper::$userClient->me()->get($configuration)->wait();
    }

    public static function getInbox(): Models\MessageCollectionResponse {
        $configuration = new MessagesRequestBuilderGetRequestConfiguration();
        $configuration->queryParameters = new MessagesRequestBuilderGetQueryParameters();
        // Only request specific properties
        $configuration->queryParameters->select = ['from','isRead','receivedDateTime','subject'];
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


    public static function sendMail(string $subject, string $body, string $recipient): void {
        $message = new Models\Message();
        $message->setSubject($subject);

        $itemBody = new Models\ItemBody();
        $itemBody->setContent($body);
        $itemBody->setContentType(new Models\BodyType(Models\BodyType::TEXT));
        $message->setBody($itemBody);

        $email = new Models\EmailAddress();
        $email->setAddress($recipient);
        $to = new Models\Recipient();
        $to->setEmailAddress($email);
        $message->setToRecipients([$to]);

        $sendMailBody = new SendMailPostRequestBody();
        $sendMailBody->setMessage($message);

        GraphHelper::$userClient->me()->sendMail()->post($sendMailBody)->wait();
    }
}