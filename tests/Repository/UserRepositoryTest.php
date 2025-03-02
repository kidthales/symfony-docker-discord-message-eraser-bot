<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\User;
use App\Enum\Role;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class UserRepositoryTest extends KernelTestCase
{
    /**
     * @var EntityManager|null
     */
    private ?EntityManager $entityManager;

    /**
     * The 'system under test'.
     * @return UserRepository
     */
    static private function getSubject(): UserRepository
    {
        return self::getContainer()->get(UserRepository::class);
    }

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->entityManager = self::bootKernel()->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }

    /**
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function test_findOneByUserIdentifier_found(): void
    {
        $expected = new User();
        $expected->setDiscordId(1337);
        $expected->setRoles([Role::Admin->value]);

        $this->entityManager->persist($expected);
        $this->entityManager->flush();

        $subject = self::getSubject();

        $actual = $subject->findOneByUserIdentifier($expected->getUserIdentifier());

        self::assertInstanceOf(User::class, $actual);
        self::assertSame($expected->getId(), $actual->getId());
        self::assertSame($expected->getDiscordId(), $actual->getDiscordId());
        self::assertSame(count($expected->getRoles()), count($actual->getRoles()));
        foreach ($expected->getRoles() as $role) {
            self::assertTrue(in_array($role, $actual->getRoles()));
        }
        self::assertTrue(in_array(Role::User->value, $actual->getRoles()));
        self::assertNotNull($actual->getCreatedAt());
        self::assertNotNull($actual->getUpdatedAt());
        self::assertSame($actual->getUpdatedAt(), $actual->getUpdatedAt());
    }
}
