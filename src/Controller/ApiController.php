<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Device;
use App\Entity\MotionEvent;
use App\Repository\DeviceRepository;
use App\Repository\MotionEventRepository;
use App\Service\ApiFormatter;
use App\Service\NtfyNotificationService;
use App\Service\TelegramNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DeviceRepository $deviceRepository,
        private readonly MotionEventRepository $motionEventRepository,
        private readonly ApiFormatter $formatter,
        private readonly NtfyNotificationService $ntfyNotificationService,
        private readonly TelegramNotificationService $telegramNotification,
    ) {
    }

    #[Route('/auth', name: 'api_auth', methods: ['POST'])]
    public function auth(Request $request): JsonResponse
    {
        $payload = $this->decodePayload($request);
        $deviceId = trim((string) ($payload['device_id'] ?? ''));

        if ($deviceId === '') {
            return $this->json(['error' => 'device_id is required'], 422);
        }

        $device = $this->deviceRepository->findOneBy(['deviceId' => $deviceId]);

        if (!$device instanceof Device) {
            $device = new Device($deviceId);
            $this->entityManager->persist($device);
        }

        $device->touch();
        $this->entityManager->flush();

        return $this->json(['device' => $this->formatter->device($device)]);
    }

    #[Route('/motion', name: 'api_motion', methods: ['POST'])]
    public function motion(Request $request): JsonResponse
    {
        $payload = $this->decodePayload($request);
        $deviceId = trim((string) ($payload['device_id'] ?? ''));

        if ($deviceId === '') {
            return $this->json(['error' => 'device_id is required'], 422);
        }

        $device = $this->deviceRepository->findOneBy(['deviceId' => $deviceId]);

        if (!$device instanceof Device) {
            return $this->json(['error' => 'Unknown device'], 404);
        }

        if ($device->getStatus() !== Device::STATUS_VALIDATED) {
            return $this->json(['error' => 'Device is not validated'], 403);
        }

        $device->touch();
        $event = new MotionEvent($device);

        $this->entityManager->persist($event);
        $this->entityManager->flush();
        $this->ntfyNotificationService->sendMotionDetected($device, $event);
        $this->telegramNotification->sendMessage($device);

        return $this->json(['motion' => $this->formatter->motion($event)], 201);
    }

    #[Route('/api/devices', name: 'api_devices_list', methods: ['GET'])]
    public function devices(): JsonResponse
    {
        $items = array_map(fn (Device $device): array => $this->formatter->device($device), $this->deviceRepository->findForDashboard());

        return $this->json(['devices' => $items]);
    }

    #[Route('/api/motions', name: 'api_motions_list', methods: ['GET'])]
    public function motions(): JsonResponse
    {
        $motions = array_map(function (array $motion): array {
            // detected_at is already a DateTimeImmutable from findRecentForApi
            if ($motion['detected_at'] instanceof \DateTimeImmutable) {
                $motion['detected_at'] = $motion['detected_at']->format('Y-m-d H:i:s');
            }

            return $motion;
        }, $this->motionEventRepository->findRecentForApi());

        return $this->json(['motions' => $motions]);
    }

    #[Route('/api/devices/{id}/validate', name: 'api_device_validate', methods: ['POST'])]
    public function validateDevice(int $id, Request $request): JsonResponse
    {
        $payload = $this->decodePayload($request);
        $name = trim((string) ($payload['name'] ?? ''));
        $to = trim((string) ($payload['to'] ?? ''));

        if ($name === '') {
            return $this->json(['error' => 'name is required'], 422);
        }

        $device = $this->deviceRepository->find($id);

        if (!$device instanceof Device) {
            return $this->json(['error' => 'Device not found'], 404);
        }

        $device->validateWithName($name);
        $device->setNotificationTo($to !== '' ? $to : null);
        $this->entityManager->flush();

        return $this->json(['device' => $this->formatter->device($device)]);
    }

    #[Route('/api/devices/{id}', name: 'api_device_update_name', methods: ['PATCH'])]
    public function updateDeviceName(int $id, Request $request): JsonResponse
    {
        $payload = $this->decodePayload($request);
        $name = trim((string) ($payload['name'] ?? ''));
        $to = trim((string) ($payload['to'] ?? ''));

        if ($name === '' && !array_key_exists('to', $payload)) {
            return $this->json(['error' => 'name or to is required'], 422);
        }

        $device = $this->deviceRepository->find($id);

        if (!$device instanceof Device) {
            return $this->json(['error' => 'Device not found'], 404);
        }

        if ($name !== '') {
            $device->setName($name);
        }

        if (array_key_exists('to', $payload)) {
            $device->setNotificationTo($to !== '' ? $to : null);
        }

        $this->entityManager->flush();

        return $this->json(['device' => $this->formatter->device($device)]);
    }

    #[Route('/api/devices/{id}', name: 'api_device_delete', methods: ['DELETE'])]
    public function deleteDevice(int $id): JsonResponse
    {
        $device = $this->deviceRepository->find($id);

        if (!$device instanceof Device) {
            return $this->json(['error' => 'Device not found'], 404);
        }

        $this->entityManager->remove($device);
        $this->entityManager->flush();

        return $this->json(['message' => 'Device deleted']);
    }

    /** @return array<string, mixed> */
    private function decodePayload(Request $request): array
    {
        if ($request->getContent() === '') {
            return [];
        }

        $payload = json_decode($request->getContent(), true);

        return is_array($payload) ? $payload : [];
    }
}


