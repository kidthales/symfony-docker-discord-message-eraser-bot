<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enum\Role;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateUserPayload
{
    /**
     * @param int|string $discordId
     * @param Role[] $roles
     */
    public function __construct(#[Assert\NotBlank] public int|string $discordId, public array $roles = [])
    {
    }
}
