<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Device;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ApiControllerTest extends WebTestCase
{
    private ?EntityManagerInterface $entityManager = null;
    private ?EntityManagerInterface $transaction = null;

    private function getEntityManager(): EntityManagerInterface
    {
        if (!$this->entityManager) {
            $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        }
        return $this->entityManager;
    }

    private function beginTransaction(): void
    {
        $em = $this->getEntityManager();
        if (!$em->getConnection()->isTransactionActive()) {
            $em->beginTransaction();
            $this->transaction = $em;
        }
    }

    private function rollbackTransaction(): void
    {
        if ($this->transaction && $this->transaction->getConnection()->isTransactionActive()) {
            $this->transaction->rollback();
            $this->transaction = null;
        }
    }

    public function testAuthCreatesNewPendingDevice(): void
    {
        $client = self::createClient();
        $this->beginTransaction();

        $client->request('POST', '/auth', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['device_id' => 'test-device-1']));

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);

        self::assertSame('test-device-1', $data['device']['device_id']);
        self::assertSame(Device::STATUS_PENDING, $data['device']['status']);

        $this->rollbackTransaction();
    }

    public function testAuthUpdatesTouchesExistingDevice(): void
    {
        $client = self::createClient();
        $this->beginTransaction();

        $device = new Device('test-device-2');
        $this->getEntityManager()->persist($device);
        $this->getEntityManager()->flush();
        $firstSeen = $device->getLastSeen();

        sleep(1);

        $client->request('POST', '/auth', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['device_id' => 'test-device-2']));

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);

        // Last seen should be updated
        self::assertGreaterThan($firstSeen->getTimestamp(), (new \DateTimeImmutable($data['device']['last_seen']))->getTimestamp());

        $this->rollbackTransaction();
    }

    public function testMotionRequiresValidatedDevice(): void
    {
        $client = self::createClient();
        $this->beginTransaction();

        $device = new Device('test-device-3');
        $this->getEntityManager()->persist($device);
        $this->getEntityManager()->flush();

        $client->request('POST', '/motion', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['device_id' => 'test-device-3']));

        self::assertResponseStatusCodeSame(403);
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertSame('Device is not validated', $data['error']);

        $this->rollbackTransaction();
    }

    public function testMotionSucceedsForValidatedDevice(): void
    {
        $client = self::createClient();
        $this->beginTransaction();

        $device = new Device('test-device-4');
        $device->validateWithName('Validated Device');
        $this->getEntityManager()->persist($device);
        $this->getEntityManager()->flush();

        $client->request('POST', '/motion', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['device_id' => 'test-device-4']));

        self::assertResponseStatusCodeSame(201);
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('motion', $data);
        self::assertSame($device->getId(), $data['motion']['device_id']);

        $this->rollbackTransaction();
    }

    public function testListDevices(): void
    {
        $client = self::createClient();
        $this->beginTransaction();

        $device = new Device('test-device-5');
        $device->validateWithName('Listed Device');
        $this->getEntityManager()->persist($device);
        $this->getEntityManager()->flush();

        $client->request('GET', '/api/devices');

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);

        self::assertNotEmpty($data['devices']);
        self::assertSame('test-device-5', $data['devices'][0]['device_id']);

        $this->rollbackTransaction();
    }

    public function testValidateDevice(): void
    {
        $client = self::createClient();
        $this->beginTransaction();

        $device = new Device('test-device-6');
        $this->getEntityManager()->persist($device);
        $this->getEntityManager()->flush();

        $client->request('POST', "/api/devices/{$device->getId()}/validate", [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['name' => 'My Device', 'to' => 'topic-device-6']));

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);

        self::assertSame(Device::STATUS_VALIDATED, $data['device']['status']);
        self::assertSame('My Device', $data['device']['name']);
        self::assertSame('topic-device-6', $data['device']['notification_to']);

        $this->rollbackTransaction();
    }

    public function testUpdateDeviceName(): void
    {
        $client = self::createClient();
        $this->beginTransaction();

        $device = new Device('test-device-7');
        $device->validateWithName('Old Name');
        $this->getEntityManager()->persist($device);
        $this->getEntityManager()->flush();

        $client->request('PATCH', "/api/devices/{$device->getId()}", [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['name' => 'Updated Name', 'to' => 'topic-updated']));

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);

        self::assertSame('Updated Name', $data['device']['name']);
        self::assertSame('topic-updated', $data['device']['notification_to']);

        $this->rollbackTransaction();
    }

    public function testDeleteDevice(): void
    {
        $client = self::createClient();
        $this->beginTransaction();

        $device = new Device('test-device-8');
        $this->getEntityManager()->persist($device);
        $this->getEntityManager()->flush();
        $deviceId = $device->getId();

        $client->request('DELETE', "/api/devices/{$deviceId}");

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertSame('Device deleted', $data['message']);

        self::assertNull($this->getEntityManager()->find(Device::class, $deviceId));

        $this->rollbackTransaction();
    }

}

