<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\ActionStatus;
use App\Enum\ActionType;
use App\Repository\ActionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ActionRepository::class)]
#[ORM\Table(name: '`action`')]
#[ORM\HasLifecycleCallbacks]
final class Action
{
    use PrimaryIdTrait, TimestampsTrait;

    /**
     * @var ActionType|null
     */
    #[ORM\Column(type: 'string', length: 128, updatable: false, enumType: ActionType::class)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 128)]
    private ?ActionType $type = null;

    /**
     * User identifier.
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 128, updatable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 128)]
    private ?string $actor = null;

    /**
     * @var array|null
     */
    #[ORM\Column(type: 'json', nullable: true, updatable: false)]
    private ?array $payload = null;

    /**
     * @var ActionStatus|null
     */
    #[ORM\Column(type: 'string', length: 16, updatable: false, enumType: ActionStatus::class)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 16)]
    private ?ActionStatus $status = null;

    /**
     * @var string[]|null
     */
    #[ORM\Column(type: 'json', nullable: true, updatable: false)]
    private ?array $details = null;

    /**
     * @return ActionType|null
     */
    public function getType(): ?ActionType
    {
        return $this->type;
    }

    /**
     * @param ActionType $type
     * @return void
     */
    public function setType(ActionType $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string|null
     */
    public function getActor(): ?string
    {
        return $this->actor;
    }

    /**
     * @param string $actor
     * @return void
     */
    public function setActor(string $actor): void
    {
        $this->actor = $actor;
    }

    /**
     * @return array|null
     */
    public function getPayload(): ?array
    {
        return $this->payload;
    }

    /**
     * @param array|null $payload
     * @return void
     */
    public function setPayload(?array $payload): void
    {
        $this->payload = $payload;
    }

    /**
     * @return ActionStatus|null
     */
    public function getStatus(): ?ActionStatus
    {
        return $this->status;
    }

    /**
     * @param ActionStatus $status
     * @return void
     */
    public function setStatus(ActionStatus $status): void
    {
        $this->status = $status;
    }

    /**
     * @return array|null
     */
    public function getDetails(): ?array
    {
        return $this->details;
    }

    /**
     * @param array|null $details
     * @return void
     */
    public function setDetails(?array $details): void
    {
        $this->details = $details;
    }
}
