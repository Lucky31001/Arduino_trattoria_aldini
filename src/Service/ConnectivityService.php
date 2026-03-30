<?php

declare(strict_types=1);

namespace App\Service;

class ConnectivityService
{
    public function isOnline(\DateTimeImmutable $lastSeen, \DateTimeImmutable $referenceTime, int $thresholdSeconds = 20): bool
    {
        $ageInSeconds = $referenceTime->getTimestamp() - $lastSeen->getTimestamp();

        return $ageInSeconds <= $thresholdSeconds;
    }
}

