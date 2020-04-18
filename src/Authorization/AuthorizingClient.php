<?php

namespace CthulhuDen\DockerRegistryV2\Authorization;

use CthulhuDen\DockerRegistryV2\Authorization\Challenge\ChallengeParserInterface;
use CthulhuDen\DockerRegistryV2\Authorization\Exception\InvalidAuthorizationResponseException;
use CthulhuDen\DockerRegistryV2\Authorization\Exception\InvalidChallengeException;
use CthulhuDen\DockerRegistryV2\Authorization\Store\CacheStoreInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AuthorizingClient implements ClientInterface
{
    private $inner;
    private $challengeParser;
    private $requestFactory;
    private $tokenStore;
    private $keepOldScopes;

    private $user;
    private $password;
    /**
     * @var array<int, string>
     * @psalm-var list<string>
     */
    private $oldScopes = [];

    public function __construct(
        ClientInterface $inner,
        ChallengeParserInterface $challengeParser,
        RequestFactoryInterface $requestFactory,
        CacheStoreInterface $tokenStore,
        string $user,
        string $password,
        bool $keepOldScopes = true
    ) {
        $this->inner = $inner;
        $this->challengeParser = $challengeParser;
        $this->requestFactory = $requestFactory;
        $this->tokenStore = $tokenStore;
        $this->keepOldScopes = $keepOldScopes;

        $this->user = $user;
        $this->password = $password;
    }

    final public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $response = $this->sendWithCurrentToken($request);

        if ($response->getStatusCode() === 401) {
            $challenge = $this->extractChallenge($response);
            $authRequest = $this->createAuthRequest($challenge);
            $authResponse = $this->sendAuthRequest($authRequest);

            $this->tokenStore->setToken($this->extractToken($authResponse));

            $response = $this->sendWithCurrentToken($request);
        }

        return $response;
    }

    private function sendWithCurrentToken(RequestInterface $request): ResponseInterface
    {
        if (($token = $this->tokenStore->getToken()) !== null) {
            $request = $request->withHeader('Authorization', "Bearer {$token}");
        }

        return $this->inner->sendRequest($request);
    }

    /**
     * @throws InvalidChallengeException
     */
    private function extractChallenge(ResponseInterface $response): Challenge
    {
        $challengeLine = trim($response->getHeaderLine('WWW-Authenticate'));

        return $this->challengeParser->parse($challengeLine);
    }

    protected function createAuthRequest(Challenge $challenge): RequestInterface
    {
        $authRequest = $this->requestFactory->createRequest('GET', $challenge->getEndpoint());

        $uri = $authRequest->getUri();

        parse_str($uri->getQuery(), $query);

        $query['service'] = $challenge->getService();

        $scopes = $challenge->getScopes();

        if ($this->keepOldScopes) {
            foreach ($scopes as $scope) {
                if (!in_array($scope, $this->oldScopes)) {
                    $this->oldScopes[] = $scope;
                }
            }
            $scopes = $this->oldScopes;
        }

        if (isset($query['scope'])) {
            if (is_array($query['scope'])) {
                $query['scope'] = array_filter($query['scope'], 'is_string');
                $scopes = array_merge($query['scope'], $scopes);
            } else {
                array_unshift($scopes, (string) $query['scope']);
            }

            unset($query['scope']);
        }

        $query = http_build_query($query);

        foreach ($scopes as $scope) {
            $query .= '&scope=' . urlencode($scope);
        }

        return $authRequest
            ->withUri($uri->withQuery($query))
            ->withHeader('Authorization', 'Basic ' . base64_encode("{$this->user}:{$this->password}"));
    }

    /**
     * @throws InvalidAuthorizationResponseException
     */
    private function sendAuthRequest(RequestInterface $request): ResponseInterface
    {
        $response = $this->inner->sendRequest($request);
        if ($response->getStatusCode() !== 200) {
            throw new InvalidAuthorizationResponseException(
                "Non-200 response code: {$response->getStatusCode()}",
                $response,
            );
        }

        return $response;
    }

    protected function extractToken(ResponseInterface $response): string
    {
        $json = $response->getBody()->getContents();
        /** @psalm-var array{token:string} $data */
        $data = json_decode($json, true);

        return $data['token'];
    }
}
