<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Controller\DiscordController;
use App\Security\AuthenticationEntryPoint;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

final class AuthenticationEntryPointTest extends KernelTestCase
{
    /**
     * The 'system under test'.
     * @return AuthenticationEntryPoint
     */
    static private function getSubject(): AuthenticationEntryPoint
    {
        return self::getContainer()->get(AuthenticationEntryPoint::class);
    }

    /**
     * @return void
     */
    public function test_start(): void
    {
        self::bootKernel();

        $subject = self::getSubject();
        $request = Request::create('/');
        $request->setSession(self::getContainer()->get('session.factory')->createSession());

        $result = $subject->start($request);

        self::assertInstanceOf(RedirectResponse::class, $result);
        self::assertStringEndsWith(DiscordController::CONNECT_ROUTE_PATH, $result->getTargetUrl());

        self::assertSame($request->getSession()->get(AuthenticationEntryPoint::AFTER_CHECK_ROUTE_SESSION_KEY), 'app_dashboard'); // TODO
        self::assertIsArray($request->getSession()->get(AuthenticationEntryPoint::AFTER_CHECK_ROUTE_PARAMS_SESSION_KEY));
        self::assertEmpty($request->getSession()->get(AuthenticationEntryPoint::AFTER_CHECK_ROUTE_PARAMS_SESSION_KEY));
    }
}
