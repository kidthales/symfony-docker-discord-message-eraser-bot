<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Security\DiscordRequestAuthenticator;
use App\Security\TokenStack;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Throwable;

final class TokenStackTest extends KernelTestCase
{
    /**
     * The 'system under test'.
     * @return TokenStack
     */
    static private function getSubject(): TokenStack
    {
        return self::getContainer()->get(TokenStack::class);
    }

    /**
     * @return void
     */
    public function test_pop_return_null_when_empty(): void
    {
        self::bootKernel();

        $subject = self::getSubject();

        self::assertSame(0, $subject->size());
        self::assertNull($subject->pop());
    }

    /**
     * @return void
     */
    public function test_push_throw_user_not_found_exception(): void
    {
        self::bootKernel();

        $subject = self::getSubject();

        try {
            $subject->push('1337');
            $this->fail('User not found exception not thrown');
        } catch (Throwable $e) {
            self::assertInstanceOf(UserNotFoundException::class, $e);
        }
    }

    /**
     * @return void
     */
    public function test_push_and_pop(): void
    {
        self::bootKernel();

        $subject = self::getSubject();
        /** @var Security $security */
        $security = self::getContainer()->get(Security::class);

        $result = $subject->push(DiscordRequestAuthenticator::AGENT_USER_IDENTIFIER);

        self::assertSame(DiscordRequestAuthenticator::AGENT_USER_IDENTIFIER, $result->getUserIdentifier());
        self::assertSame(DiscordRequestAuthenticator::AGENT_USER_IDENTIFIER, $security->getUser()->getUserIdentifier());
        self::assertSame(1, $subject->size());

        $result = $subject->push('agent:cli');

        self::assertSame('agent:cli', $result->getUserIdentifier());
        self::assertSame('agent:cli', $security->getUser()->getUserIdentifier());
        self::assertSame(2, $subject->size());

        $result = $subject->push('agent:cli');

        self::assertSame('agent:cli', $result->getUserIdentifier());
        self::assertSame('agent:cli', $security->getUser()->getUserIdentifier());
        self::assertSame(3, $subject->size());

        $expectedToken = $security->getToken();

        $result = $subject->pop();

        self::assertSame($expectedToken->getUserIdentifier(), $result->getUserIdentifier());
        self::assertSame(2, $subject->size());

        $expectedToken = $security->getToken();

        $result = $subject->pop();

        self::assertSame($expectedToken->getUserIdentifier(), $result->getUserIdentifier());
        self::assertSame(1, $subject->size());

        $expectedToken = $security->getToken();

        $result = $subject->pop();

        self::assertSame($expectedToken->getUserIdentifier(), $result->getUserIdentifier());
        self::assertSame(0, $subject->size());

        self::assertNull($security->getToken());
    }
}
