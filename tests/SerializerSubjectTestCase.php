<?php

declare(strict_types=1);

namespace App\Tests;

use LogicException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\SerializerInterface;

class SerializerSubjectTestCase extends KernelTestCase
{
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        throw new LogicException('Must override this method in child test case');
    }

    /**
     * @return SerializerInterface
     */
    protected static function getSerializer(): SerializerInterface
    {
        return self::getContainer()->get(SerializerInterface::class);
    }

    /**
     * @param mixed $subject
     * @param string $expected
     * @param string $format
     * @return void
     */
    protected static function testSerialization(mixed $subject, string $expected, string $format): void
    {
        self::bootKernel();

        $actual = self::getSerializer()->serialize($subject, $format);

        self::assertSame($expected, $actual);
    }

    /**
     * @param string $subject
     * @param mixed $expected
     * @param string $type
     * @param string $format
     * @return void
     */
    protected static function testDeserialization(string $subject, mixed $expected, string $type, string $format): void
    {
        self::bootKernel();

        $actual = self::getSerializer()->deserialize($subject, $type, $format);

        self::assertInstanceOf($type, $actual);
        static::assertDeepSame($expected, $actual);
    }
}
