<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Discord\AbstractWebhookEventBody;
use App\Dto\Discord\ApplicationAuthorizedWebhookEventBody;
use App\Dto\Discord\WebhookEventPayload;
use App\Enum\Discord\WebhookType;
use App\Security\DiscordOAuth2Authenticator;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(schemes: ['https'])]
final class DiscordController extends AbstractController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const string WEBHOOK_ROUTE_PATH = '/webhook/discord';
    public const string WEBHOOK_ROUTE_NAME = 'app_webhook_discord';

    public const string INTERACTION_ROUTE_PATH = '/webhook/discord/interaction';
    public const string INTERACTION_ROUTE_NAME = 'app_webhook_discord_interaction';

    public const string CONNECT_ROUTE_PATH = '/connect/discord';
    public const string CONNECT_ROUTE_NAME = 'app_connect_discord';

    public const string CHECK_ROUTE_PATH = '/connect/discord/check';
    public const string CHECK_ROUTE_NAME = 'app_connect_discord_check';

    /**
     * @param WebhookEventPayload $payload
     * @return JsonResponse
     */
    #[Route(path: self::WEBHOOK_ROUTE_PATH, name: self::WEBHOOK_ROUTE_NAME, methods: ['POST'], stateless: true)]
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

    #[Route(path: self::INTERACTION_ROUTE_PATH, name: self::INTERACTION_ROUTE_NAME, methods: ['POST'], stateless: true)]
    public function interaction(): JsonResponse
    {
        // TODO
        return $this->json(data: ['type' => 4, 'data' => ['content' => 'TODO']]);
    }

    /**
     * @param ClientRegistry $registry
     * @return Response
     */
    #[Route(path: self::CONNECT_ROUTE_PATH, name: self::CONNECT_ROUTE_NAME, methods: ['GET'])]
    public function connect(ClientRegistry $registry): Response
    {
        return $registry->getClient(DiscordOAuth2Authenticator::REGISTRY_CLIENT_KEY)->redirect(['identify']);
    }

    /**
     * @return void
     * @codeCoverageIgnore
     */
    #[Route(path: self::CHECK_ROUTE_PATH, name: self::CHECK_ROUTE_NAME, methods: ['GET'])]
    public function check(): void
    {
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
