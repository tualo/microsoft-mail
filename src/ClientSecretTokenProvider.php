<?php

namespace Tualo\Office\MicrosoftMail;

use GuzzleHttp\Client;
use Tualo\Office\Basic\TualoApplication as App;

use Http\Promise\FulfilledPromise;
use Http\Promise\Promise;
use Http\Promise\RejectedPromise;
use Microsoft\Kiota\Abstractions\Authentication\AccessTokenProvider;
use Microsoft\Kiota\Abstractions\Authentication\AllowedHostsValidator;

class ClientSecretTokenProvider implements TokenProvider
{

    private string $clientId;
    private string $tenantId;
    private string $scopes;
    private AllowedHostsValidator $allowedHostsValidator;
    public string $accessToken;
    public string $clientSecret;

    private Client $tokenClient;



    public function __construct(string $clientId, string $clientSecret, string $tenantId, string $scopes, string $accessToken = "")
    {
        $this->clientId = $clientId;
        $this->tenantId = $tenantId;
        $this->clientSecret = $clientSecret;
        $this->scopes = $scopes;

        $this->allowedHostsValidator = new AllowedHostsValidator();
        $this->allowedHostsValidator->setAllowedHosts([
            "graph.microsoft.com",
            "graph.microsoft.us",
            "dod-graph.microsoft.us",
            "graph.microsoft.de",
            "microsoftgraph.chinacloudapi.cn"
        ]);

        if ($accessToken !== '') {
            $this->accessToken = $accessToken;
        } else {
            $this->accessToken = '';
        }

        $this->tokenClient = new Client();

        App::result('YYYYYX',  $this->accessToken);
    }

    public function setAccessToken($token): void
    {
        $this->accessToken = $token;
    }

    public function getAccessToken(string $device_code = "")
    {
        $tokenRequestUrl = 'https://login.microsoftonline.com/' . $this->tenantId . '/oauth2/v2.0/token';

        $tokenResponse = $this->tokenClient->post($tokenRequestUrl, [
            'form_params' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => 'https://graph.microsoft.com/.default',
                'grant_type' => 'client_credentials',

            ],
            'http_errors' => false,
            'curl' => [
                CURLOPT_FAILONERROR => false
            ]
        ]);

        if ($tokenResponse->getStatusCode() == 200) {
            $responseBody = json_decode($tokenResponse->getBody()->getContents(), true);
            return $responseBody;
        } else if ($tokenResponse->getStatusCode() == 400) {
            $responseBody = json_decode($tokenResponse->getBody()->getContents());
            if (isset($responseBody->error)) {
                $error = $responseBody->error;
                if (strcmp($error, 'authorization_pending') != 0) {
                    return new RejectedPromise(
                        new \Exception('Token endpoint returned ' . $error, 100)
                    );
                }
            }
        }
    }

    public function deviceLogin(): array
    {
        return [];
    }


    public function getAllowedHostsValidator(): AllowedHostsValidator
    {
        return $this->allowedHostsValidator;
    }

    /**
     * Returns a promise that resolves to the access token string.
     *
     * @param string $url
     * @param array $additionalAuthenticationContext
     * @return Promise
     */
    public function getAuthorizationTokenAsync(string $url, array $additionalAuthenticationContext = []): Promise
    {
        try {
            $tokenResponse = $this->getAccessToken();
            if (is_array($tokenResponse) && isset($tokenResponse['access_token'])) {
                $this->accessToken = $tokenResponse['access_token'];
                return new FulfilledPromise($this->accessToken);
            } elseif ($tokenResponse instanceof Promise) {
                return $tokenResponse;
            } else {
                return new RejectedPromise(
                    new \Exception('Failed to retrieve access token', 101)
                );
            }
        } catch (\Throwable $e) {
            return new RejectedPromise($e);
        }
    }

    public function getAccessTokenByRefreshToken($refresh_token)
    {
        $tokenRequestUrl = 'https://login.microsoftonline.com/' . $this->tenantId . '/oauth2/v2.0/token';

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
            $responseBody = json_decode($tokenResponse->getBody()->getContents(), true);
            return $responseBody;
        } else if ($tokenResponse->getStatusCode() == 400) {
            $responseBody = json_decode($tokenResponse->getBody()->getContents());
            if (isset($responseBody->error)) {
                $error = $responseBody->error;
                if (strcmp($error, 'authorization_pending') != 0) {
                    return new RejectedPromise(
                        new \Exception('Token endpoint returned ' . $error, 100)
                    );
                }
            }
        }
    }
}
