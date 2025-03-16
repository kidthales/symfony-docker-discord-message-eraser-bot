<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Dto\CreateUserPayload;
use App\Enum\ActionStatus;
use App\Enum\ActionType;
use App\MessageHandler\ActionRecorder;
use App\Repository\ActionRepository;
use App\Security\TokenStack;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Throwable;

final class ActionRecorderTest extends KernelTestCase
{
    /**
     * The 'system under test'.
     * @return ActionRecorder
     */
    static private function getSubject(): ActionRecorder
    {
        return self::getContainer()->get(ActionRecorder::class);
    }

    /**
     * @return void
     */
    public function test_throw_user_not_found_exception(): void
    {
        self::bootKernel();

        $subject = self::getSubject();

        try {
            $subject->fail(ActionType::CreateUser, new CreateUserPayload(1137), ['Test details.']);
            self::fail('User not found exception not thrown');
        } catch (Throwable $e) {
            self::assertInstanceOf(UserNotFoundException::class, $e);
        }
    }

    /**
     * @return void
     * @throws SerializerExceptionInterface
     */
    public function test_pass(): void
    {
        self::bootKernel();

        $subject = self::getSubject();
        /** @var TokenStack $tokenStack */
        $tokenStack = self::getContainer()->get(TokenStack::class);
        $tokenStack->push('agent:cli');

        $subject->pass(ActionType::CreateUser, new CreateUserPayload(1137), ['Test details.']);

        /** @var ActionRepository $actionRepository */
        $actionRepository = self::getContainer()->get(ActionRepository::class);

        self::assertSame(1, $actionRepository->count());

        $action = $actionRepository->findOneBy([]);
        self::assertSame(ActionType::CreateUser, $action->getType());
        self::assertSame('agent:cli', $action->getActor());
        self::assertSame(1137, $action->getPayload()['discordId']);
        self::assertSame(ActionStatus::Pass, $action->getStatus());
        self::assertSame('Test details.', $action->getDetails()[0]);
    }

    /**
     * @return void
     * @throws SerializerExceptionInterface
     */
    public function test_warn(): void
    {
        self::bootKernel();

        $subject = self::getSubject();
        /** @var TokenStack $tokenStack */
        $tokenStack = self::getContainer()->get(TokenStack::class);
        $tokenStack->push('agent:cli');

        $subject->warn(ActionType::CreateUser, new CreateUserPayload(1137), ['Test details.']);

        /** @var ActionRepository $actionRepository */
        $actionRepository = self::getContainer()->get(ActionRepository::class);

        self::assertSame(1, $actionRepository->count());

        $action = $actionRepository->findOneBy([]);
        self::assertSame(ActionType::CreateUser, $action->getType());
        self::assertSame('agent:cli', $action->getActor());
        self::assertSame(1137, $action->getPayload()['discordId']);
        self::assertSame(ActionStatus::Warn, $action->getStatus());
        self::assertSame('Test details.', $action->getDetails()[0]);
    }
}
