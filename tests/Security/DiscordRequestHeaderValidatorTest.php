<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Exception\DiscordRequestHeaderValidationException;
use App\Exception\RequestHeaderMissingException;
use App\Security\DiscordRequestHeaderValidator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

final class DiscordRequestHeaderValidatorTest extends KernelTestCase
{
    /**
     * The 'system under test'.
     * @return DiscordRequestHeaderValidator
     */
    static private function getSubject(): DiscordRequestHeaderValidator
    {
        return self::getContainer()->get(DiscordRequestHeaderValidator::class);
    }

    /**
     * @return void
     */
    public function test_validate_throws_request_header_missing_exception(): void
    {
        self::bootKernel();

        $subject = self::getSubject();
        $request = Request::create('/');

        try {
            $subject->validate($request);
            self::fail('Request header missing exception not thrown (' . DiscordRequestHeaderValidator::HEADER_ED25519 . ')');
        } catch (Throwable $e) {
            self::assertInstanceOf(RequestHeaderMissingException::class, $e);
            self::assertSame('Request header missing: ' . DiscordRequestHeaderValidator::HEADER_ED25519, $e->getMessage());
        }

        $request->headers->set(DiscordRequestHeaderValidator::HEADER_ED25519, 'test-ed25519');

        try {
            $subject->validate($request);
            self::fail('Request header missing exception not thrown (' . DiscordRequestHeaderValidator::HEADER_TIMESTAMP . ')');
        } catch (Throwable $e) {
            self::assertInstanceOf(RequestHeaderMissingException::class, $e);
            self::assertSame('Request header missing: ' . DiscordRequestHeaderValidator::HEADER_TIMESTAMP, $e->getMessage());
        }
    }

    public function test_validate_throws_discord_request_header_validation_exception(): void
    {
        self::bootKernel();

        $subject = self::getSubject();
        $request = Request::create(uri: '/', method: 'POST', content: '"Test"');
        $request->headers->set(DiscordRequestHeaderValidator::HEADER_ED25519, 'test-ed25519');
        $request->headers->set(DiscordRequestHeaderValidator::HEADER_TIMESTAMP, 'test-timestamp');

        try {
            $subject->validate($request);
            self::fail('Discord request header validation exception not thrown');
        } catch (Throwable $e) {
            self::assertInstanceOf(DiscordRequestHeaderValidationException::class, $e);
            self::assertSame('Error occurred during Discord request header validation', $e->getMessage());
            self::assertSame('hex2bin(): Input string must be hexadecimal string', $e->getPrevious()->getMessage());
        }
    }

    /*public function test_validate_fail(): void
    {
        self::bootKernel();

        $subject = self::getSubject();
        $request = Request::create(uri: '/', method: 'POST', content: '"Test content."');
        $request->headers->set(DiscordRequestHeaderValidator::HEADER_ED25519, 'test-ed25519');
        //$request->headers->set(DiscordRequestHeaderValidator::HEADER_ED25519, 'e3d8ccc158dea97fd51e52a62125de91516f678c1e4d9e406b51f6df7aa37a11f7cecb5773964848a28a1c366ef2547c8a5d0ce7ca553758aa54ba15726df70a');
        $request->headers->set(DiscordRequestHeaderValidator::HEADER_TIMESTAMP, 'test-timestamp');

        self::assertFalse($subject->validate($request));
    }*/
}
