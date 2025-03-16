<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserProviderInterface;

#[Autoconfigure(public: true)]
final class TokenStack
{
    /**
     * @var (TokenInterface|null)[]
     */
    private array $stack = [];

    /**
     * @param Security $security
     * @param UserProviderInterface $userProvider
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        private readonly Security              $security,
        #[Autowire(service: 'security.user.provider.concrete.all_provider')]
        private readonly UserProviderInterface $userProvider,
        private readonly TokenStorageInterface $tokenStorage
    )
    {
    }

    /**
     * @param string $userIdentifier
     * @return TokenInterface
     */
    public function push(string $userIdentifier): TokenInterface
    {
        $currentToken = $this->security->getToken();
        $this->stack[] = $currentToken;

        if ($currentToken && $currentToken->getUserIdentifier() === $userIdentifier) {
            return $currentToken;
        }

        $user = $this->userProvider->loadUserByIdentifier($userIdentifier);
        $newToken = new UsernamePasswordToken($user, 'token_stack', $user->getRoles());

        $this->tokenStorage->setToken($newToken);

        return $newToken;
    }

    /**
     * @return TokenInterface|null
     */
    public function pop(): ?TokenInterface
    {
        if (empty($this->stack)) {
            return null;
        }

        $currentToken = $this->security->getToken();
        $this->tokenStorage->setToken(array_pop($this->stack));

        return $currentToken;
    }

    /**
     * @return int
     */
    public function size(): int
    {
        return count($this->stack);
    }
}
