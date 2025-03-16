<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateUserPayload
{
    /**
     * @param int|string $discordId
     * @param array $roles
     */
    public function __construct(#[Assert\NotBlank] public int|string $discordId, public array $roles = [])
    {
    }
}
