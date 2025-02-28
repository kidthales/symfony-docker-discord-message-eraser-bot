<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(methods: ['POST'], schemes: ['https'], stateless: true)]
final class DiscordController extends AbstractController
{
    public const string WEBHOOK_ROUTE_PATH = '/webhook/discord';
    public const string WEBHOOK_ROUTE_NAME = 'app_webhook_discord';

    public const string INTERACTION_ROUTE_PATH = '/webhook/discord/interaction';
    public const string INTERACTION_ROUTE_NAME = 'app_webhook_discord_interaction';

    #[Route(path: self::WEBHOOK_ROUTE_PATH, name: self::WEBHOOK_ROUTE_NAME)]
    public function webhook(): JsonResponse
    {
        // TODO
        return $this->json(data: null, status: Response::HTTP_NO_CONTENT);
    }

    #[Route(path: self::INTERACTION_ROUTE_PATH, name: self::INTERACTION_ROUTE_NAME)]
    public function interaction(): JsonResponse
    {
        // TODO
        return $this->json(data: ['type' => 4, 'data' => ['content' => 'TODO']], status: Response::HTTP_NO_CONTENT);
    }
}
