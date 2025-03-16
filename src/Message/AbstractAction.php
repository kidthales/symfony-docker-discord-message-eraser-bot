<?php

declare(strict_types=1);

namespace App\Message;

use App\Enum\ActionType;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(transport: 'async')]
abstract readonly class AbstractAction
{
    /**
     * @param ActionType $type
     * @param string $actor
     */
    public function __construct(protected ActionType $type, protected string $actor)
    {
    }

    /**
     * @return mixed
     */
    abstract public function getPayload(): mixed;

    /**
     * @return ActionType
     */
    public function getType(): ActionType
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getActor(): string
    {
        return $this->actor;
    }
}
