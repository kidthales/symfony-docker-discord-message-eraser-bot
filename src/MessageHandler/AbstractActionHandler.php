<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\AbstractAction;
use App\Security\TokenStack;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractActionHandler
{
    /**
     * @var ActionRecorder
     */
    protected ActionRecorder $actionRecorder;

    /**
     * @var TokenStack
     */
    private TokenStack $tokenStack;

    /**
     * @param AbstractAction $message
     * @return mixed
     */
    final public function __invoke(AbstractAction $message): mixed
    {
        $this->tokenStack->push($message->getActor());

        try {
            $result = $this->handle($message);
        } finally {
            $this->tokenStack->pop();
        }

        return $result;
    }

    /**
     * @param ActionRecorder $actionRecorder
     * @return void
     */
    #[Required]
    final public function setActionRecorder(ActionRecorder $actionRecorder): void
    {
        $this->actionRecorder = $actionRecorder;
    }

    /**
     * @param TokenStack $tokenStack
     * @return void
     */
    #[Required]
    final public function setTokenStack(TokenStack $tokenStack): void
    {
        $this->tokenStack = $tokenStack;
    }

    /**
     * @param $message
     * @return mixed
     */
    abstract protected function handle($message): mixed;
}
