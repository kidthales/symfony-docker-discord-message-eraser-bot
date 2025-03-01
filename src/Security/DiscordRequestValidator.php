<?php

declare(strict_types=1);

namespace App\Security;

use App\DependencyInjection\Parameters;
use App\Exception\DiscordRequestValidationException;
use App\Exception\RequestHeaderMissingException;
use Elliptic\EdDSA;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireInline;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

#[Autoconfigure(public: true)]
final readonly class DiscordRequestValidator implements RequestValidatorInterface
{
    public const string HEADER_ED25519 = 'X-Signature-Ed25519';
    public const string HEADER_TIMESTAMP = 'X-Signature-Timestamp';

    /**
     * @param EdDSA $ec
     * @param string|null $publicKey
     */
    public function __construct(
        #[AutowireInline(class: EdDSA::class, arguments: ['ed25519'])] private EdDSA $ec,
        #[Autowire(param: Parameters::DISCORD_APP_PUBLIC_KEY)] private ?string       $publicKey
    )
    {
    }

    /**
     * @inheritDoc
     * @throws RequestHeaderMissingException
     * @throws DiscordRequestValidationException
     */
    public function validate(Request $request): bool
    {
        $signature = $request->headers->get(self::HEADER_ED25519);
        $timestamp = $request->headers->get(self::HEADER_TIMESTAMP);

        if ($signature === null) {
            throw new RequestHeaderMissingException(self::HEADER_ED25519);
        }

        if ($timestamp === null) {
            throw new RequestHeaderMissingException(self::HEADER_TIMESTAMP);
        }

        try {
            $message = [...unpack('C*', $timestamp), ...unpack('C*', $request->getContent())];
            return $this->ec->keyFromPublic($this->publicKey)->verify($message, $signature);
        } catch (Throwable $e) {
            throw new DiscordRequestValidationException($e);
        }
    }
}
