<?php

namespace App\Service;

use App\Entity\Device;
use App\Entity\MotionEvent;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Exception\TransportExceptionInterface;
use Symfony\Component\Notifier\Message\ChatMessage;

readonly class TelegramNotificationService
{

    public function __construct(private ChatterInterface $chatter)
    {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendMessage(Device $device): void
    {
        $message = new ChatMessage(sprintf('Motion detected on device %s: %s', $device->getName() ?: $device->getDeviceId(), (new \DateTime())->setTimezone(new \DateTimeZone('Europe/Paris'))->format('Y-m-d H:i:s')));
        $message->transport('telegram');
        $this->chatter->send($message);
    }
}