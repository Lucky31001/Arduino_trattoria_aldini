<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\MotionEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MotionEventRepository::class)]
#[ORM\Table(name: 'motion_events')]
class MotionEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Device::class, inversedBy: 'motionEvents')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Device $device;

    #[ORM\Column(name: 'detected_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $detectedAt;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(Device $device)
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->device = $device;
        $this->detectedAt = $now;
        $this->createdAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDevice(): Device
    {
        return $this->device;
    }

    public function getDetectedAt(): \DateTimeImmutable
    {
        return $this->detectedAt;
    }
}

