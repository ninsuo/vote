<?php

namespace App\Security;

use App\Repository\MagicLinkRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class MagicLinkAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var MagicLinkRepository
     */
    private $magicLinkRepository;

    public function __construct(RouterInterface $router, MagicLinkRepository $magicLinkRepository)
    {
        $this->router              = $router;
        $this->magicLinkRepository = $magicLinkRepository;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse(
            $this->router->generate('login')
        );
    }

    public function supports(Request $request) : ?bool
    {
        return strpos($request->getPathInfo(), '/token/') === 0;
    }

    public function authenticate(Request $request) : PassportInterface
    {
        return new SelfValidatingPassport(new UserBadge(substr($request->getPathInfo(), 7), function ($token) {
            $magicLink = $this->magicLinkRepository->findOneByToken($token);

            if (null === $magicLink) {
                throw new CustomUserMessageAuthenticationException('Sorry, authentication failed: provided token is invalid or may have expired, please try again.');
            }

            $user = $magicLink->getUser();

            $this->magicLinkRepository->remove($magicLink);

            return $user;
        }));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName) : ?Response
    {
        return new RedirectResponse(
            $this->router->generate('home')
        );
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) : ?Response
    {
        return new RedirectResponse(
            $this->router->generate('login')
        );
    }
}