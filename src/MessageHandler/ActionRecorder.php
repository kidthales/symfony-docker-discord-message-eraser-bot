<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Action;
use App\Enum\ActionStatus;
use App\Enum\ActionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Autoconfigure(public: true)]
final readonly class ActionRecorder
{
    /**
     * @param Security $security
     * @param NormalizerInterface $normalizer
     * @param ValidatorInterface $validator
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        private Security               $security,
        private NormalizerInterface    $normalizer,
        private ValidatorInterface     $validator,
        private EntityManagerInterface $entityManager,
    )
    {
    }

    /**
     * @param ActionType $type
     * @param mixed $payload
     * @param array $details
     * @return void
     * @throws SerializerExceptionInterface
     */
    public function pass(ActionType $type, mixed $payload, array $details): void
    {
        $this->record($type, $payload, ActionStatus::Pass, $details);
    }

    /**
     * @param ActionType $type
     * @param mixed $payload
     * @param array $details
     * @return void
     * @throws SerializerExceptionInterface
     */
    public function warn(ActionType $type, mixed $payload, array $details): void
    {
        $this->record($type, $payload, ActionStatus::Warn, $details);
    }

    /**
     * @param ActionType $type
     * @param mixed $payload
     * @param array $details
     * @return void
     * @throws SerializerExceptionInterface
     */
    public function fail(ActionType $type, mixed $payload, array $details): void
    {
        $this->record($type, $payload, ActionStatus::Fail, $details);
    }

    /**
     * @param ActionType $type
     * @param mixed $payload
     * @param ActionStatus $status
     * @param array $details
     * @return void
     * @throws SerializerExceptionInterface
     */
    private function record(ActionType $type, mixed $payload, ActionStatus $status, array $details): void
    {
        $user = $this->security->getUser();

        if (!$user) {
            throw new UserNotFoundException('User not found in security context');
        }

        $action = new Action();
        $action->setType($type);
        $action->setActor($user->getUserIdentifier());
        $action->setPayload($this->normalizer->normalize($payload));
        $action->setStatus($status);
        $action->setDetails($details);

        $errors = $this->validator->validate($action);
        if (count($errors) > 0) {
            // @codeCoverageIgnoreStart
            throw new ValidatorException((string)$errors);
            // @codeCoverageIgnoreEnd
        }

        $this->entityManager->persist($action);
        $this->entityManager->flush();
    }
}
