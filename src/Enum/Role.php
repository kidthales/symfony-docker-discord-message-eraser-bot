<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * See config/packages/security.php for role hierarchy.
 */
enum Role: string
{
    case Agent = 'ROLE_AGENT';
    case User = 'ROLE_USER';
    case Admin = 'ROLE_ADMIN';
    case SuperAdmin = 'ROLE_SUPER_ADMIN';
}
