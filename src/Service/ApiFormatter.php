<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Device;
use App\Entity\MotionEvent;

class ApiFormatter
{
    /** @return array<string, mixed> */
    public function device(Device $device): array
    {
        return [
            'id' => $device->getId(),
            'device_id' => $device->getDeviceId(),
            'name' => $device->getName(),
            'status' => $device->getStatus(),
            'last_seen' => $device->getLastSeen()->format('Y-m-d H:i:s'),
            'online' => $device->isOnline() ? 1 : 0,
            'created_at' => $device->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $device->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }

    /** @return array<string, mixed> */
    public function motion(MotionEvent $event): array
    {
        return [
            'id' => $event->getId(),
            'device_id' => $event->getDevice()->getId(),
            'detected_at' => $event->getDetectedAt()->format('Y-m-d H:i:s'),
        ];
    }
}

