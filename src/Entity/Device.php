<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DeviceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeviceRepository::class)]
#[ORM\Table(name: 'devices')]
class Device
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_VALIDATED = 'validated';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'device_id', length: 255, unique: true)]
    private string $deviceId;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(name: 'notification_to', length: 32, nullable: true)]
    private ?string $notificationTo = null;

    #[ORM\Column(length: 32)]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(name: 'last_seen', type: 'datetime_immutable')]
    private \DateTimeImmutable $lastSeen;

    #[ORM\Column(type: 'boolean')]
    private bool $online = true;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, MotionEvent> */
    #[ORM\OneToMany(mappedBy: 'device', targetEntity: MotionEvent::class, orphanRemoval: true)]
    private Collection $motionEvents;

    public function __construct(string $deviceId)
    {
        $this->deviceId = $deviceId;
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->lastSeen = $now;
        $this->createdAt = $now;
        $this->updatedAt = $now;
        $this->motionEvents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        $this->updatedAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        return $this;
    }

    public function getNotificationTo(): ?string
    {
        return $this->notificationTo;
    }

    public function setNotificationTo(?string $notificationTo): self
    {
        $this->notificationTo = $notificationTo;
        $this->updatedAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getLastSeen(): \DateTimeImmutable
    {
        return $this->lastSeen;
    }

    public function isOnline(): bool
    {
        return $this->online;
    }

    public function setOnline(bool $online): self
    {
        $this->online = $online;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function touch(?\DateTimeImmutable $seenAt = null): self
    {
        $now = $seenAt ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->lastSeen = $now;
        $this->updatedAt = $now;
        $this->online = true;

        return $this;
    }

    public function validateWithName(string $name): self
    {
        $this->name = $name;
        $this->status = self::STATUS_VALIDATED;
        $this->updatedAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        return $this;
    }
}

