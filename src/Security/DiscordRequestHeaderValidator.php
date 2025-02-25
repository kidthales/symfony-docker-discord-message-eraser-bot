<?php

namespace App\Security;

use Elliptic\EdDSA;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireInline;
use Symfony\Component\HttpFoundation\Request;

final readonly class DiscordRequestHeaderValidator
{
    public const string HEADER_ED25519 = 'X-Signature-Ed25519';
    public const string HEADER_TIMESTAMP = 'X-Signature-Timestamp';

    /**
     * @param EdDSA $ec
     * @param string $publicKey
     */
    public function __construct(
        #[AutowireInline(class: EdDSA::class, arguments: ['ed25519'])] private EdDSA $ec,
        #[Autowire(env: 'string:DISCORD_APP_PUBLIC_KEY')] private string             $publicKey
    )
    {
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function validate(Request $request): bool
    {
        $signature = $request->headers->get(self::HEADER_ED25519);
        $timestamp = $request->headers->get(self::HEADER_TIMESTAMP);

        if ($signature === null || $timestamp === null) {
            return false;
        }

        return $this->ec
            ->keyFromPublic($this->publicKey)
            ->verify([...unpack('C*', $timestamp), ...unpack('C*', $request->getContent())], $signature);
    }
}
