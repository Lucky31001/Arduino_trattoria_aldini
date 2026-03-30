<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Device;
use PHPUnit\Framework\TestCase;

final class DeviceTest extends TestCase
{
    public function testNewDeviceStartsAsPendingAndOnline(): void
    {
        $device = new Device('arduino-123');

        self::assertSame('arduino-123', $device->getDeviceId());
        self::assertSame(Device::STATUS_PENDING, $device->getStatus());
        self::assertTrue($device->isOnline());
    }

    public function testValidateWithNameSetsStatusAndName(): void
    {
        $device = new Device('arduino-123');
        $device->validateWithName('Garage Sensor');

        self::assertSame(Device::STATUS_VALIDATED, $device->getStatus());
        self::assertSame('Garage Sensor', $device->getName());
    }
}

