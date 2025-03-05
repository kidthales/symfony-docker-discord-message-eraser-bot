<?php

declare(strict_types=1);

namespace App\Security;

use App\Controller\DiscordController;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Throwable;
use Wohali\OAuth2\Client\Provider\DiscordResourceOwner;

final class DiscordOAuth2Authenticator extends OAuth2Authenticator implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const string REGISTRY_CLIENT_KEY = 'discord';

    public function __construct(
        private readonly ClientRegistry         $registry,
        private readonly RouterInterface        $router,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function supports(Request $request): ?bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === DiscordController::CHECK_ROUTE_NAME;
    }

    /**
     * @inheritDoc
     */
    public function authenticate(Request $request): Passport
    {
        $client = $this->registry->getClient(self::REGISTRY_CLIENT_KEY);
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client, $request) {
                try {
                    /** @var DiscordResourceOwner $discordUser */
                    $discordUser = $client->fetchUserFromToken($accessToken);
                } catch (Throwable $e) {
                    $this->logger->error('Discord OAuth2 authenticator encountered an error fetching user resource from access token', [
                        'exception' => FlattenException::createFromThrowable($e)
                    ]);
                    return null;
                }

                $discordId = $discordUser->getId();

                if (!$discordId) {
                    $this->logger->error('Discord OAuth2 authenticator encountered an error getting id from user resource', [
                        'token' => $accessToken->getToken()
                    ]);
                    return null;
                }

                try {
                    /** @var UserRepository $repository */
                    $repository = $this->entityManager->getRepository(User::class);
                    return $repository->findOneByDiscordId($discordId);
                } catch (Throwable $e) {
                    $this->logger->error('Discord OAuth2 authenticator encountered an error finding user', [
                        'exception' => FlattenException::createFromThrowable($e)
                    ]);
                }

                return null;
            })
        );
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $session = $request->getSession();

        $route = $session->get(AuthenticationEntryPoint::AFTER_CHECK_ROUTE_SESSION_KEY, 'app_dashboard'); // TODO: use class const...
        $routeParams = $session->get(AuthenticationEntryPoint::AFTER_CHECK_ROUTE_PARAMS_SESSION_KEY, []);

        $session->remove(AuthenticationEntryPoint::AFTER_CHECK_ROUTE_SESSION_KEY);
        $session->remove(AuthenticationEntryPoint::AFTER_CHECK_ROUTE_PARAMS_SESSION_KEY);

        return new RedirectResponse($this->router->generate($route, $routeParams));
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());
        return new Response($message, Response::HTTP_FORBIDDEN);
    }
}
