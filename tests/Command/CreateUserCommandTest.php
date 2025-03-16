<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\CreateUserCommand;
use App\Console\AbstractCommand;
use App\Enum\ActionStatus;
use App\Enum\ActionType;
use App\Enum\Role;
use App\Repository\ActionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class CreateUserCommandTest extends KernelTestCase
{
    /**
     * The 'system under test'.
     * @param string $commandName
     * @return CommandTester
     */
    static private function getSubject(string $commandName): CommandTester
    {
        return new CommandTester((new Application(self::$kernel))->find($commandName));
    }

    /**
     * @return void
     */
    public function test_create_user(): void
    {
        self::bootKernel();

        $subject = self::getSubject('app:user:create');

        $subject->execute([CreateUserCommand::ARG_DISCORD_ID => 1337]);
        $subject->assertCommandIsSuccessful();

        /** @var ActionRepository $actionRepository */
        $actionRepository = self::getContainer()->get(ActionRepository::class);

        self::assertSame(1, $actionRepository->count());

        $action = $actionRepository->findOneBy([]);
        self::assertSame(ActionType::CreateUser, $action->getType());
        self::assertSame(AbstractCommand::AGENT_USER_IDENTIFIER, $action->getActor());
        self::assertSame(ActionStatus::Pass, $action->getStatus());
        self::assertStringContainsString('User ID ', $action->getDetails()[0]);

        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneByDiscordId(1337);

        self::assertCount(1, $user->getRoles());
        self::assertSame(Role::User->value, $user->getRoles()[0]);
    }

    /**
     * @return void
     */
    public function test_create_admin(): void
    {
        self::bootKernel();

        $subject = self::getSubject('app:user:create');

        $subject->execute([
            CreateUserCommand::ARG_DISCORD_ID => 1337,
            '--' . CreateUserCommand::OPT_ADMIN => true
        ]);
        $subject->assertCommandIsSuccessful();

        /** @var ActionRepository $actionRepository */
        $actionRepository = self::getContainer()->get(ActionRepository::class);

        self::assertSame(1, $actionRepository->count());

        $action = $actionRepository->findOneBy([]);
        self::assertSame(ActionType::CreateUser, $action->getType());
        self::assertSame(AbstractCommand::AGENT_USER_IDENTIFIER, $action->getActor());
        self::assertSame(ActionStatus::Pass, $action->getStatus());
        self::assertStringContainsString('User ID ', $action->getDetails()[0]);

        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneByDiscordId(1337);

        self::assertCount(2, $user->getRoles());
        self::assertSame(Role::Admin->value, $user->getRoles()[0]);
        self::assertSame(Role::User->value, $user->getRoles()[1]);
    }

    /**
     * @return void
     */
    public function test_create_super_admin(): void
    {
        self::bootKernel();

        $subject = self::getSubject('app:user:create');

        $subject->execute([
            CreateUserCommand::ARG_DISCORD_ID => 1337,
            '--' . CreateUserCommand::OPT_SUPER => true
        ]);
        $subject->assertCommandIsSuccessful();

        /** @var ActionRepository $actionRepository */
        $actionRepository = self::getContainer()->get(ActionRepository::class);

        self::assertSame(1, $actionRepository->count());

        $action = $actionRepository->findOneBy([]);
        self::assertSame(ActionType::CreateUser, $action->getType());
        self::assertSame(AbstractCommand::AGENT_USER_IDENTIFIER, $action->getActor());
        self::assertSame(ActionStatus::Pass, $action->getStatus());
        self::assertStringContainsString('User ID ', $action->getDetails()[0]);

        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneByDiscordId(1337);

        self::assertCount(2, $user->getRoles());
        self::assertSame(Role::SuperAdmin->value, $user->getRoles()[0]);
        self::assertSame(Role::User->value, $user->getRoles()[1]);
    }
}
