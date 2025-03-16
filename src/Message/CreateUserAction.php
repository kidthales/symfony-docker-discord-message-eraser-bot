<?php

declare(strict_types=1);

namespace App\Message;

use App\Dto\CreateUserPayload;
use App\Enum\ActionType;

final readonly class CreateUserAction extends AbstractAction
{
    /**
     * @param string $actor
     * @param CreateUserPayload $payload
     */
    public function __construct(string $actor, private CreateUserPayload $payload)
    {
        parent::__construct(ActionType::CreateUser, $actor);
    }

    /**
     * @return CreateUserPayload
     */
    public function getPayload(): CreateUserPayload
    {
        return $this->payload;
    }
}
