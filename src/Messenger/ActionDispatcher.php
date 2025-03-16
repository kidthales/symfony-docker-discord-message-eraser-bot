<?php

declare(strict_types=1);

namespace App\Messenger;

use App\Dto\CreateUserPayload;
use App\Entity\User;
use App\Message\AbstractAction;
use App\Message\CreateUserAction;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface as MessengerExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use UnexpectedValueException;

#[Autoconfigure(public: true)]
final readonly class ActionDispatcher
{
    /**
     * @param ValidatorInterface $validator
     * @param Security $security
     * @param MessageBusInterface $messageBus
     */
    public function __construct(
        private ValidatorInterface     $validator,
        private Security               $security,
        private MessageBusInterface    $messageBus
    )
    {
    }

    /**
     * @param CreateUserPayload $payload
     * @param bool $sync
     * @return Envelope|User
     * @throws MessengerExceptionInterface
     */
    public function createUser(CreateUserPayload $payload, bool $sync = false): Envelope|User
    {
        return $this->validate($payload)
            ->dispatch(new CreateUserAction($this->getActor(), $payload), $sync);
    }

    /**
     * @param mixed $payload
     * @return self
     */
    private function validate(mixed $payload): self
    {
        $errors = $this->validator->validate($payload);

        if (count($errors) > 0) {
            // @codeCoverageIgnoreStart
            throw new ValidatorException((string)$errors);
            // @codeCoverageIgnoreEnd
        }

        return $this;
    }

    /**
     * @return string
     */
    private function getActor(): string
    {
        $user = $this->security->getUser();

        if (!$user) {
            throw new UserNotFoundException('Actor not found');
        }

        return $user->getUserIdentifier();
    }

    /**
     * @param AbstractAction $message
     * @param bool $sync
     * @return mixed
     * @throws MessengerExceptionInterface
     */
    private function dispatch(AbstractAction $message, bool $sync): mixed
    {
        if (!$sync) {
            return $this->messageBus->dispatch($message);
        }

        $envelope = $this->messageBus->dispatch($message, [new TransportNamesStamp('sync')]);

        $handledStamp = $envelope->last(HandledStamp::class);

        if (!$handledStamp) {
            // @codeCoverageIgnoreStart
            throw new UnexpectedValueException('Handled stamp not found');
            // @codeCoverageIgnoreEnd
        }

        return $handledStamp->getResult();
    }
}
