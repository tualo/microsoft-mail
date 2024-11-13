<?php
namespace Tualo\Office\MicrosoftMail;

use GuzzleHttp\Client;

use Http\Promise\FulfilledPromise;
use Http\Promise\Promise;
use Http\Promise\RejectedPromise;
use Microsoft\Kiota\Abstractions\Authentication\AccessTokenProvider;
use Microsoft\Kiota\Abstractions\Authentication\AllowedHostsValidator;

class DeviceCodeTokenProvider implements AccessTokenProvider {

    private string $clientId;
    private string $tenantId;
    private string $scopes;
    private AllowedHostsValidator $allowedHostsValidator;
    public string $accessToken;
    private Client $tokenClient;



    public function __construct(string $clientId, string $tenantId, string $scopes) {
        $this->clientId = $clientId;
        $this->tenantId = $tenantId;
        $this->scopes = $scopes;
        
        $this->allowedHostsValidator = new AllowedHostsValidator();
        $this->allowedHostsValidator->setAllowedHosts([
            "graph.microsoft.com",
            "graph.microsoft.us",
            "dod-graph.microsoft.us",
            "graph.microsoft.de",
            "microsoftgraph.chinacloudapi.cn"
        ]);
        
        $this->tokenClient = new Client();
    }

    public function setAccessToken($token){
        $this->accessToken = $token;
    }

    public function deviceLogin(){
        $deviceCodeRequestUrl = 'https://login.microsoftonline.com/'.$this->tenantId.'/oauth2/v2.0/devicecode';
        $deviceCodeResponse = json_decode($this->tokenClient->post($deviceCodeRequestUrl, [
            'form_params' => [
                'client_id' => $this->clientId,
                'scope' => $this->scopes
            ]
        ])->getBody()->getContents(),true);
        return $deviceCodeResponse;
    }

    public function getAccessToken($device_code){
        $tokenRequestUrl = 'https://login.microsoftonline.com/'.$this->tenantId.'/oauth2/v2.0/token';

        $tokenResponse = $this->tokenClient->post($tokenRequestUrl, [
            'form_params' => [
                'client_id' => $this->clientId,
                'grant_type' => 'urn:ietf:params:oauth:grant-type:device_code',
                'device_code' => $device_code
            ],
            'http_errors' => false,
            'curl' => [
                CURLOPT_FAILONERROR => false
            ]
        ]);

        if ($tokenResponse->getStatusCode() == 200) {
            $responseBody = json_decode($tokenResponse->getBody()->getContents(),true);
            return $responseBody;
        } else if ($tokenResponse->getStatusCode() == 400) {
            $responseBody = json_decode($tokenResponse->getBody()->getContents());
            if (isset($responseBody->error)) {
                $error = $responseBody->error;
                if (strcmp($error, 'authorization_pending') != 0) {
                    return new RejectedPromise(
                        new \Exception('Token endpoint returned '.$error, 100));
                }
            }
        }
    }


    public function getAccessTokenByRefreshToken($refresh_token){
        $tokenRequestUrl = 'https://login.microsoftonline.com/'.$this->tenantId.'/oauth2/v2.0/token';

        $tokenResponse = $this->tokenClient->post($tokenRequestUrl, [
            'form_params' => [
                'client_id' => $this->clientId,
                'grant_type' => 'refresh_token',
                'refresh_token' => $refresh_token
            ],
            'http_errors' => false,
            'curl' => [
                CURLOPT_FAILONERROR => false
            ]
        ]);

        if ($tokenResponse->getStatusCode() == 200) {
            $responseBody = json_decode($tokenResponse->getBody()->getContents(),true);
            return $responseBody;
        } else if ($tokenResponse->getStatusCode() == 400) {
            $responseBody = json_decode($tokenResponse->getBody()->getContents());
            if (isset($responseBody->error)) {
                $error = $responseBody->error;
                if (strcmp($error, 'authorization_pending') != 0) {
                    return new RejectedPromise(
                        new \Exception('Token endpoint returned '.$error, 100));
                }
            }
        }
    }

    public function getAuthorizationTokenAsync(string $url, array $additionalAuthenticationContext = []): Promise {
        $parsedUrl = parse_url($url);
        $scheme = $parsedUrl["scheme"] ?? null;


        if ($scheme !== 'https' || !$this->getAllowedHostsValidator()->isUrlHostValid($url)) {
            return new FulfilledPromise(null);
        }

        // If we already have a user token, just return it
        // Tokens are valid for one hour, after that it needs to be refreshed
        if (isset($this->accessToken)) {

            return new FulfilledPromise($this->accessToken);
        }else{
            return new RejectedPromise(
                new \Exception('No access token', 100));
            // throw new \Exception('No access token');
        }
    }

    public function getAllowedHostsValidator(): AllowedHostsValidator {
        return $this->allowedHostsValidator;
    }
}
?>