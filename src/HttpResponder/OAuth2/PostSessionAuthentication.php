<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder\OAuth2;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\OAuth2AuthorizationCodeFlowControllerInterface;
use Distantmagic\Resonance\OAuth2AuthorizationRequestSessionStore;
use Distantmagic\Resonance\OAuth2AuthorizedUser;
use Distantmagic\Resonance\SessionAuthentication;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

#[GrantsFeature(Feature::OAuth2)]
#[Singleton]
final readonly class PostSessionAuthentication extends HttpResponder
{
    public function __construct(
        private OAuth2AuthorizationCodeFlowControllerInterface $authorizationCodeFlowController,
        private OAuth2AuthorizationRequestSessionStore $authorizationRequestSessionStore,
        private SessionAuthentication $sessionAuthentication,
    ) {}

    public function respond(ServerRequestInterface $request, ResponseInterface $response): HttpInterceptableInterface|HttpResponderInterface|ResponseInterface
    {
        if (!$this->authorizationRequestSessionStore->has($request)) {
            return $this->authorizationCodeFlowController->redirectToAuthenticatedPage($request, $response);
        }

        $authenticatedUser = $this->sessionAuthentication->getAuthenticatedUser($request);

        if (!$authenticatedUser) {
            throw new RuntimeException('Expected authenticated user to be stored in session');
        }

        $authRequest = $this->authorizationRequestSessionStore->get($request);
        $authRequest->setUser(new OAuth2AuthorizedUser($authenticatedUser->user->getIdentifier()));

        return $this
            ->authorizationCodeFlowController
            ->redirectToClientScopeConsentPage($request, $response)
        ;
    }
}
