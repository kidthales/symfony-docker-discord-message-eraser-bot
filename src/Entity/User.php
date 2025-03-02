<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\Role;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\HasLifecycleCallbacks]
final class User implements UserInterface
{
    use PrimaryIdTrait, TimestampsTrait;

    /**
     * @var int|string|null
     */
    #[ORM\Column(name: 'discord_id', type: 'bigint', unique: true, updatable: false)]
    #[Assert\NotBlank]
    private int|string|null $discordId = null;

    /**
     * @var array
     */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    /**
     * @return int|string|null
     */
    public function getDiscordId(): int|string|null
    {
        return $this->discordId;
    }

    /**
     * @param int|string $discordId
     * @return void
     */
    public function setDiscordId(int|string $discordId): void
    {
        $this->discordId = $discordId;
    }

    /**
     * @inheritDoc
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = Role::User->value;

        return array_unique($roles);
    }

    /**
     * @param array $roles
     * @return void
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * @inheritDoc
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->discordId;
    }
}
