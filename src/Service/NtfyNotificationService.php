<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Device;
use App\Entity\MotionEvent;
use Psr\Log\LoggerInterface;

class NtfyNotificationService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly bool $enabled,
        private readonly ?string $baseUrl,
        private readonly ?string $defaultTopic,
    ) {
    }

    public function sendMotionDetected(Device $device, MotionEvent $event): void
    {
        if (!$this->enabled) {
            return;
        }

        $baseUrl = rtrim((string) $this->baseUrl, '/');
        $topic = trim((string) ($device->getNotificationTo() ?: $this->defaultTopic));

        if ($baseUrl === '' || $topic === '') {
            return;
        }

        $deviceLabel = $device->getName() ?: $device->getDeviceId();
        $timeLabel = $event->getDetectedAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('H\\hi');
        $message = sprintf('Mouvement detecte par %s a %s.', $deviceLabel, $timeLabel);

        try {
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => [
                        'Content-Type: text/plain; charset=utf-8',
                        'Title: Mouvement detecte',
                        'Priority: default',
                    ],
                    'content' => $message,
                    'timeout' => 2,
                    'ignore_errors' => true,
                ],
            ]);

            $result = @file_get_contents(sprintf('%s/%s', $baseUrl, rawurlencode($topic)), false, $context);

            if ($result === false) {
                $this->logger->warning('ntfy notification failed', ['topic' => $topic]);
            }
        } catch (\Throwable $exception) {
            // Keep /motion non-blocking if notification delivery fails.
            $this->logger->error('ntfy notification exception', ['exception' => $exception]);
        }
    }
}

