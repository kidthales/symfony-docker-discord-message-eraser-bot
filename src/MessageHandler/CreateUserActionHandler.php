<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\User;
use App\Enum\Role;
use App\Exception\UnauthorizedActionException;
use App\Message\CreateUserAction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use TypeError;

#[AsMessageHandler]
final class CreateUserActionHandler extends AbstractActionHandler
{
    public function __construct(
        private readonly Security               $security,
        private readonly ValidatorInterface     $validator,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    /**
     * @param $message
     * @return User
     * @throws SerializerExceptionInterface
     * @throws UnauthorizedActionException
     */
    protected function handle($message): User
    {
        if (!($message instanceof CreateUserAction)) {
            // @codeCoverageIgnoreStart
            throw new TypeError('Expected message instance of type CreateUserAction.');
            // @codeCoverageIgnoreEnd
        }

        return $this->auth($message)
            ->createUser($message);
    }

    /**
     * @param CreateUserAction $message
     * @return self
     * @throws SerializerExceptionInterface
     * @throws UnauthorizedActionException
     */
    private function auth(CreateUserAction $message): self
    {
        $payload = $message->getPayload();

        $requiredRole = Role::Admin->value;

        foreach ($payload->roles as $role) {
            if (in_array($role, [Role::Admin->value, Role::SuperAdmin->value])) {
                $requiredRole = Role::SuperAdmin->value;
                break;
            }
        }

        if (!$this->security->isGranted($requiredRole)) {
            $exception = new UnauthorizedActionException('Unauthorized. Actor requires: ' . $requiredRole);
            $this->actionRecorder->fail($message->getType(), $payload, [$exception->getMessage()]);
            throw $exception;
        }

        return $this;
    }

    /**
     * @param CreateUserAction $message
     * @return User
     * @throws SerializerExceptionInterface
     */
    private function createUser(CreateUserAction $message): User
    {
        $payload = $message->getPayload();

        $user = new User();
        $user->setDiscordId($payload->discordId);
        $user->setRoles($payload->roles);

        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            $this->actionRecorder->fail($message->getType(), $payload, (array)$errors);
            throw new ValidatorException((string)$errors);
        }

        $this->entityManager->wrapInTransaction(function () use ($user, $message) {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->actionRecorder->pass($message->getType(), $message->getPayload(), ['User ID ' . $user->getId()]);
        });

        return $user;
    }
}
