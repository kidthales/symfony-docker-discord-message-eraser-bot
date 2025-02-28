<?php

declare(strict_types=1);

namespace App\Security;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Throwable;

#[Autoconfigure(public: true)]
final class DiscordRequestAuthenticator extends AbstractAuthenticator implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const string AGENT_USER_IDENTIFIER = 'Discord Agent';

    /**
     * @param DiscordRequestHeaderValidator $validator
     */
    public function __construct(
        #[Autowire(service: DiscordRequestHeaderValidator::class)]
        private readonly DiscordRequestHeaderValidatorInterface $validator
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function supports(Request $request): ?bool
    {
        return $request->headers->has(DiscordRequestHeaderValidator::HEADER_ED25519) &&
            $request->headers->has(DiscordRequestHeaderValidator::HEADER_TIMESTAMP);
    }

    /**
     * @inheritDoc
     */
    public function authenticate(Request $request): Passport
    {
        $exception = null;

        try {
            $isValid = $this->validator->validate($request);
        } catch (Throwable $e) {
            $exception = $e;
            $isValid = false;
            $this->logger->error('Discord request authenticator encountered an error during validation', [
                'exception' => FlattenException::createFromThrowable($exception)
            ]);
        }

        return $isValid
            ? new SelfValidatingPassport(new UserBadge(self::AGENT_USER_IDENTIFIER))
            : throw new CustomUserMessageAuthenticationException(message: 'invalid request signature', previous: $exception);
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new Response(
            strtr($exception->getMessageKey(), $exception->getMessageData()),
            Response::HTTP_UNAUTHORIZED
        );
    }
}
