<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @note Ensure consuming class has `#[ORM\HasLifecycleCallbacks]` attribute.
 */
trait TimestampsTrait
{
    /**
     * @var int|string|null
     */
    #[ORM\Column(name: 'created_at', type: 'bigint', updatable: false)]
    private int|string|null $createdAt = null;

    /**
     * @var int|string|null
     */
    #[ORM\Column(name: 'updated_at', type: 'bigint')]
    private int|string|null $updatedAt = null;

    /**
     * @return int|string|null
     */
    public function getCreatedAt(): int|string|null
    {
        return $this->createdAt;
    }

    /**
     * @param int|string $createdAt
     * @return void
     */
    public function setCreatedAt(int|string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return void
     */
    #[ORM\PrePersist]
    public function prePersistCreatedAt(): void
    {
        $this->createdAt = $this->updatedAt = time();
    }

    /**
     * @return int|string|null
     */
    public function getUpdatedAt(): int|string|null
    {
        return $this->updatedAt;
    }

    /**
     * @param int|string $updatedAt
     * @return void
     */
    public function setUpdatedAt(int|string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return void
     */
    #[ORM\PreUpdate]
    public function preUpdateUpdatedAt(): void
    {
        $this->updatedAt = time();
    }
}
