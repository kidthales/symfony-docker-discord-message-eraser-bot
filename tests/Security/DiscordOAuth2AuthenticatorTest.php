<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Controller\DiscordController;
use App\Security\DiscordOAuth2Authenticator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class DiscordOAuth2AuthenticatorTest extends KernelTestCase
{
    /**
     * The 'system under test'.
     * @return DiscordOAuth2Authenticator
     */
    static private function getSubject(): DiscordOAuth2Authenticator
    {
        return self::getContainer()->get(DiscordOAuth2Authenticator::class);
    }

    /**
     * @return void
     */
    public function test_supports(): void
    {
        self::bootKernel();

        $subject = self::getSubject();
        $request = Request::create('/');
        $request->attributes->set('_route', DiscordController::CHECK_ROUTE_NAME);

        self::assertTrue($subject->supports($request));

        $request->attributes->set('_route', 'not_supported');

        self::assertFalse($subject->supports($request));
    }

    /**
     * @return void
     */
    public function test_onAuthenticationFailure(): void
    {
        self::bootKernel();

        $subject = self::getSubject();
        $request = Request::create('/');
        $exception = new AuthenticationException();

        $result = $subject->onAuthenticationFailure($request, $exception);

        self::assertInstanceOf(Response::class, $result);
        self::assertEquals(Response::HTTP_FORBIDDEN, $result->getStatusCode());
    }
}
