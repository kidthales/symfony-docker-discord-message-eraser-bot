<?php

declare(strict_types=1);

namespace App\Tests\Messenger;

use App\Dto\CreateUserPayload;
use App\Entity\User;
use App\Enum\ActionStatus;
use App\Enum\ActionType;
use App\Messenger\ActionDispatcher;
use App\Repository\ActionRepository;
use App\Security\TokenStack;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface as MessengerExceptionInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Throwable;

final class ActionDispatcherTest extends KernelTestCase
{
    /**
     * The 'system under test'.
     * @return ActionDispatcher
     */
    static private function getSubject(): ActionDispatcher
    {
        return self::getContainer()->get(ActionDispatcher::class);
    }

    /**
     * @return void
     */
    public function test_throw_user_not_found_exception(): void
    {
        self::bootKernel();

        $subject = self::getSubject();

        try {
            $subject->createUser(new CreateUserPayload(1137));
            self::fail('User not found exception not thrown');
        } catch (Throwable $e) {
            self::assertInstanceOf(UserNotFoundException::class, $e);
        }
    }

    /**
     * @return void
     * @throws MessengerExceptionInterface
     */
    public function test_createUser_async(): void
    {
        self::bootKernel();

        $subject = self::getSubject();
        /** @var TokenStack $tokenStack */
        $tokenStack = self::getContainer()->get(TokenStack::class);
        $tokenStack->push('agent:cli');

        $result = $subject->createUser(new CreateUserPayload(1137));

        self::assertInstanceOf(Envelope::class, $result);
    }

    /**
     * @return void
     * @throws MessengerExceptionInterface
     */
    public function test_createUser_sync(): void
    {
        self::bootKernel();

        $subject = self::getSubject();
        /** @var TokenStack $tokenStack */
        $tokenStack = self::getContainer()->get(TokenStack::class);
        $tokenStack->push('agent:cli');

        $result = $subject->createUser(new CreateUserPayload(1137), true);

        self::assertInstanceOf(User::class, $result);
        self::assertSame(1137, $result->getDiscordId());

        /** @var ActionRepository $actionRepository */
        $actionRepository = self::getContainer()->get(ActionRepository::class);

        self::assertSame(1, $actionRepository->count());

        $action = $actionRepository->findOneBy([]);
        self::assertSame(ActionType::CreateUser, $action->getType());
        self::assertSame('agent:cli', $action->getActor());
        self::assertSame(1137, $action->getPayload()['discordId']);
        self::assertSame(ActionStatus::Pass, $action->getStatus());
        self::assertStringContainsString('User ID ', $action->getDetails()[0]);
    }
}
