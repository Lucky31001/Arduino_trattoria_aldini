<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\ConnectivityService;
use PHPUnit\Framework\TestCase;

final class ConnectivityServiceTest extends TestCase
{
    public function testDeviceIsOnlineWhenLastSeenIsWithinThreshold(): void
    {
        $service = new ConnectivityService();
        $reference = new \DateTimeImmutable('2026-03-30 13:00:00', new \DateTimeZone('UTC'));
        $lastSeen = $reference->sub(new \DateInterval('PT10S'));

        self::assertTrue($service->isOnline($lastSeen, $reference, 20));
    }

    public function testDeviceIsOfflineWhenLastSeenExceedsThreshold(): void
    {
        $service = new ConnectivityService();
        $reference = new \DateTimeImmutable('2026-03-30 13:00:00', new \DateTimeZone('UTC'));
        $lastSeen = $reference->sub(new \DateInterval('PT45S'));

        self::assertFalse($service->isOnline($lastSeen, $reference, 20));
    }
}

