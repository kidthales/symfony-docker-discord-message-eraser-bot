<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Security\DiscordRequestAuthenticator;
use App\Security\DiscordRequestValidator;
use App\Security\RequestValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Throwable;

final class DiscordRequestAuthenticatorTest extends KernelTestCase
{
    /**
     * The 'system under test'.
     * @return DiscordRequestAuthenticator
     */
    static private function getSubject(): DiscordRequestAuthenticator
    {
        return self::getContainer()->get(DiscordRequestAuthenticator::class);
    }

    /**
     * @return void
     */
    public function test_supports(): void
    {
        self::bootKernel();

        $subject = self::getSubject();
        $request = Request::create('/');

        self::assertFalse($subject->supports($request));

        $request->headers->set(DiscordRequestValidator::HEADER_ED25519, 'test-ed25519');

        self::assertFalse($subject->supports($request));

        $request->headers->remove(DiscordRequestValidator::HEADER_ED25519);
        $request->headers->set(DiscordRequestValidator::HEADER_TIMESTAMP, 'test-timestamp');

        self::assertFalse($subject->supports($request));

        $request->headers->set(DiscordRequestValidator::HEADER_ED25519, 'test-ed25519');

        self::assertTrue($subject->supports($request));
    }

    /**
     * @return void
     */
    public function test_authenticate_return_self_validating_passport(): void
    {
        self::bootKernel();

        $validator = self::createMock(RequestValidatorInterface::class);
        $validator->expects(self::once())
            ->method('validate')
            ->willReturn(true);
        self::getContainer()->set(RequestValidatorInterface::class, $validator);

        $subject = self::getSubject();
        $request = Request::create('/');
        $request->headers->set(DiscordRequestValidator::HEADER_ED25519, 'test-ed25519');
        $request->headers->set(DiscordRequestValidator::HEADER_TIMESTAMP, 'test-timestamp');

        $result = $subject->authenticate($request);
        self::assertInstanceOf(SelfValidatingPassport::class, $result);
        self::assertSame(
            DiscordRequestAuthenticator::AGENT_USER_IDENTIFIER,
            $result->getBadge(UserBadge::class)->getUserIdentifier()
        );
    }

    /**
     * @return void
     */
    public function test_authenticate_throw_custom_user_message_authentication_exception(): void
    {
        self::bootKernel();

        $validator = self::createMock(RequestValidatorInterface::class);
        $validator->expects(self::once())
            ->method('validate')
            ->willReturn(false);
        self::getContainer()->set(RequestValidatorInterface::class, $validator);

        $subject = self::getSubject();
        $request = Request::create('/');
        $request->headers->set(DiscordRequestValidator::HEADER_ED25519, 'test-ed25519');
        $request->headers->set(DiscordRequestValidator::HEADER_TIMESTAMP, 'test-timestamp');

        try {
            $subject->authenticate($request);
            self::fail('Custom user message authentication exception not thrown');
        } catch (Throwable $e) {
            self::assertInstanceOf(CustomUserMessageAuthenticationException::class, $e);
            self::assertSame('invalid request signature', $e->getMessage());
            self::assertNull($e->getPrevious());
        }
    }

    /**
     * @return void
     */
    public function test_authenticate_throw_custom_user_message_authentication_exception_with_previous_exception(): void
    {
        self::bootKernel();

        $subject = self::getSubject();
        $request = Request::create('/');
        $request->headers->set(DiscordRequestValidator::HEADER_ED25519, 'test-ed25519');
        $request->headers->set(DiscordRequestValidator::HEADER_TIMESTAMP, 'test-timestamp');

        try {
            $subject->authenticate($request);
            self::fail('Custom user message authentication exception not thrown');
        } catch (Throwable $e) {
            self::assertInstanceOf(CustomUserMessageAuthenticationException::class, $e);
            self::assertSame('invalid request signature', $e->getMessage());
            self::assertInstanceOf(Throwable::class, $e->getPrevious());
        }
    }

    /**
     * @return void
     */
    public function test_onAuthenticationSuccess(): void
    {
        self::bootKernel();

        self::assertNull(
            self::getSubject()->onAuthenticationSuccess(Request::create('/'), new NullToken(), 'test')
        );
    }

    /**
     * @return void
     */
    public function test_onAuthenticationFailure(): void
    {
        self::bootKernel();

        $subject = self::getSubject();
        $actual = $subject->onAuthenticationFailure(Request::create('/'), new AuthenticationException());

        self::assertInstanceOf(Response::class, $actual);
        self::assertSame(Response::HTTP_UNAUTHORIZED, $actual->getStatusCode());
    }
}
