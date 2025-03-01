<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Discord\AbstractWebhookEventBody;
use App\Dto\Discord\ApplicationAuthorizedWebhookEventBody;
use App\Dto\Discord\WebhookEventPayload;
use App\Enum\Discord\WebhookType;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(methods: ['POST'], schemes: ['https'], stateless: true)]
final class DiscordController extends AbstractController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const string WEBHOOK_ROUTE_PATH = '/webhook/discord';
    public const string WEBHOOK_ROUTE_NAME = 'app_webhook_discord';

    public const string INTERACTION_ROUTE_PATH = '/webhook/discord/interaction';
    public const string INTERACTION_ROUTE_NAME = 'app_webhook_discord_interaction';

    /**
     * @param WebhookEventPayload $payload
     * @return JsonResponse
     */
    #[Route(path: self::WEBHOOK_ROUTE_PATH, name: self::WEBHOOK_ROUTE_NAME)]
    public function webhook(#[MapRequestPayload] WebhookEventPayload $payload): JsonResponse
    {
        switch ($payload->type) {
            case WebhookType::Event:
                $this->handleWebhookEvent($payload->event);
                break;
            case WebhookType::PING:
                break;
        }

        return $this->json(data: null, status: Response::HTTP_NO_CONTENT);
    }

    #[Route(path: self::INTERACTION_ROUTE_PATH, name: self::INTERACTION_ROUTE_NAME)]
    public function interaction(): JsonResponse
    {
        // TODO
        return $this->json(data: ['type' => 4, 'data' => ['content' => 'TODO']]);
    }

    /**
     * @param AbstractWebhookEventBody|null $event
     * @return void
     */
    private function handleWebhookEvent(?AbstractWebhookEventBody $event): void
    {
        if ($event === null) {
            throw new BadRequestException('null event body');
        }

        switch ($event::class) {
            case ApplicationAuthorizedWebhookEventBody::class:
                $this->logger->info('TODO!');
                break;
            // @codeCoverageIgnoreStart
            default:
                throw new BadRequestException('unsupported event body: ' . $event::class);
            // @codeCoverageIgnoreEnd
        }
    }
}
