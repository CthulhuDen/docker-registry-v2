<?php

namespace CthulhuDen\DockerRegistryV2\Authorization;

use CthulhuDen\DockerRegistryV2\Authorization\Exception\InvalidAuthorizationResponseException;
use CthulhuDen\DockerRegistryV2\Authorization\Exception\InvalidChallengeException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AuthorizingClient implements ClientInterface
{
    private $inner;
    private $challengeParser;
    private $requestFactory;

    private $user;
    private $password;
    private $token = null;

    public function __construct(
        ClientInterface $inner,
        ChallengeParserInterface $challengeParser,
        RequestFactoryInterface $requestFactory,
        string $user,
        string $password
    ) {
        $this->inner = $inner;
        $this->challengeParser = $challengeParser;
        $this->requestFactory = $requestFactory;

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

            $this->token = $this->extractToken($authResponse);

            $response = $this->sendWithCurrentToken($request);
        }

        return $response;
    }

    private function sendWithCurrentToken(RequestInterface $request): ResponseInterface
    {
        if ($this->token !== null) {
            $request = $request->withHeader('Authorization', "Bearer {$this->token}");
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

    private function createAuthRequest(Challenge $challenge)
    {
        $authRequest = $this->requestFactory->createRequest('GET', $challenge->getEndpoint());

        $uri = $authRequest->getUri();

        parse_str($uri->getQuery(), $query);

        foreach ($challenge->getParameters() as $key => $value) {
            if ($key === 'error') {
                continue;
            }

            $query[$key] = $value;
        }

        return $authRequest
            ->withUri($uri->withQuery(http_build_query($query)))
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

    private function extractToken(ResponseInterface $response): string
    {
        $json = $response->getBody()->getContents();
        $data = json_decode($json, true);

        return $data['token'];
    }
}
