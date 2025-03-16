<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Dto\CreateUserPayload;
use App\Enum\ActionStatus;
use App\Enum\ActionType;
use App\Enum\Role;
use App\Exception\UnauthorizedActionException;
use App\Message\CreateUserAction;
use App\MessageHandler\CreateUserActionHandler;
use App\Repository\ActionRepository;
use App\Security\DiscordRequestAuthenticator;
use App\Security\TokenStack;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Throwable;

final class CreateUserActionHandlerTest extends KernelTestCase
{
    /**
     * The 'system under test'.
     * @return CreateUserActionHandler
     */
    static private function getSubject(): CreateUserActionHandler
    {
        return self::getContainer()->get(CreateUserActionHandler::class);
    }

    /**
     * @return void
     */
    public function test_throw_unauthorized_action_exception(): void
    {
        self::bootKernel();

        $subject = self::getSubject();
        /** @var TokenStack $tokenStack */
        $tokenStack = self::getContainer()->get(TokenStack::class);
        $token = $tokenStack->push(DiscordRequestAuthenticator::AGENT_USER_IDENTIFIER);

        try {
            call_user_func($subject, new CreateUserAction($token->getUserIdentifier(), new CreateUserPayload(1137)));
            self::fail('Unauthorized action exception not thrown');
        } catch (Throwable $e) {
            self::assertInstanceOf(UnauthorizedActionException::class, $e);
            self::assertSame('Unauthorized. Actor requires: ' . Role::Admin->value, $e->getMessage());
        }

        try {
            call_user_func($subject, new CreateUserAction($token->getUserIdentifier(), new CreateUserPayload(1137, [Role::Admin->value])));
            self::fail('Unauthorized action exception not thrown');
        } catch (Throwable $e) {
            self::assertInstanceOf(UnauthorizedActionException::class, $e);
            self::assertSame('Unauthorized. Actor requires: ' . Role::SuperAdmin->value, $e->getMessage());
        }

        /** @var ActionRepository $actionRepository */
        $actionRepository = self::getContainer()->get(ActionRepository::class);

        self::assertSame(2, $actionRepository->count());

        $actions = $actionRepository->findAll();
        foreach ($actions as $action) {
            self::assertSame(ActionType::CreateUser, $action->getType());
            self::assertSame(DiscordRequestAuthenticator::AGENT_USER_IDENTIFIER, $action->getActor());
            self::assertSame(ActionStatus::Fail, $action->getStatus());
            self::assertStringContainsString('Unauthorized. Actor requires: ', $action->getDetails()[0]);
        }
    }
}
