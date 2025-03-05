<?php

declare(strict_types=1);

namespace App\Security;

use App\Controller\DiscordController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

final readonly class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public const string AFTER_CHECK_ROUTE_SESSION_KEY = '_after_check_route';
    public const string AFTER_CHECK_ROUTE_PARAMS_SESSION_KEY = '_after_check_route_params';

    /**
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * @inheritDoc
     */
    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        $session = $request->getSession();

        $session->set(self::AFTER_CHECK_ROUTE_SESSION_KEY, $request->attributes->get('_route', 'app_dashboard')); // TODO: use class const...
        $session->set(self::AFTER_CHECK_ROUTE_PARAMS_SESSION_KEY, $request->attributes->get('_route_params', []));

        return new RedirectResponse($this->urlGenerator->generate(DiscordController::CONNECT_ROUTE_NAME));
    }
}
