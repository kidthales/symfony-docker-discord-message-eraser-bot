<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\DiscordController;
use App\Dto\Discord\ApplicationAuthorizedWebhookEventBody;
use App\Dto\Discord\ApplicationAuthorizedWebhookEventData;
use App\Dto\Discord\User;
use App\Dto\Discord\WebhookEventPayload;
use App\Enum\Discord\WebhookEventBodyType;
use App\Enum\Discord\WebhookType;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class DiscordControllerTest extends KernelTestCase
{
    /**
     * The 'system under test'.
     * @return DiscordController
     */
    static private function getSubject(): DiscordController
    {
        return self::getContainer()->get(DiscordController::class);
    }

    /**
     * @return void
     */
    public function test_interaction(): void
    {
        self::bootKernel();
        self::assertInstanceOf(JsonResponse::class, self::getSubject()->interaction());
    }

    /**
     * @return void
     */
    public function test_webhook_ping(): void
    {
        self::bootKernel();

        $subject = self::getSubject();

        $result = $subject->webhook(new WebhookEventPayload(
            application_id: 'test-application-id',
            type: WebhookType::PING,
            event: null
        ));

        self::assertInstanceOf(JsonResponse::class, $result);
        self::assertSame($result->getContent(), 'null');
        self::assertSame($result->getStatusCode(), Response::HTTP_NO_CONTENT);
    }

    /**
     * @return void
     */
    public function test_webhook_event_application_authorized(): void
    {
        self::bootKernel();

        $subject = self::getSubject();

        $result = $subject->webhook(new WebhookEventPayload(
            application_id: 'test-application-id',
            type: WebhookType::Event,
            event: new ApplicationAuthorizedWebhookEventBody(
                type: WebhookEventBodyType::ApplicationAuthorized,
                timestamp: 'test-timestamp',
                data: new ApplicationAuthorizedWebhookEventData(
                    user: new User(
                        id: 'test-user-id',
                        username: 'test-user-username',
                        discriminator: 'test-user-discriminator',
                        global_name: null,
                        avatar: null
                    ),
                    scopes: []
                )
            )
        ));

        self::assertInstanceOf(JsonResponse::class, $result);
        self::assertSame($result->getContent(), 'null');
        self::assertSame($result->getStatusCode(), Response::HTTP_NO_CONTENT);
    }

    /**
     * @return void
     */
    public function test_webhook_null_event_body(): void
    {
        self::bootKernel();

        $subject = self::getSubject();

        try {
            $subject->webhook(new WebhookEventPayload(
                application_id: 'test-application-id',
                type: WebhookType::Event,
                event: null
            ));
            self::fail('Bad request exception not thrown');
        } catch (Throwable $e) {
            self::assertInstanceOf(BadRequestException::class, $e);
            self::assertSame('null event body', $e->getMessage());
        }
    }

    /**
     * @return void
     */
    public function test_connect_throw_logic_exception_no_current_request(): void
    {
        self::bootKernel();

        $subject = self::getSubject();

        try {
            $subject->connect(self::getContainer()->get(ClientRegistry::class));
            self::fail('Logic exception not thrown');
        } catch (Throwable $e) {
            self::assertInstanceOf(LogicException::class, $e);
            self::assertStringContainsString('no "current request"', $e->getMessage());
        }
    }
}
