<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\DeviceRepository;
use App\Service\ConnectivityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:devices:refresh-status', description: 'Refresh online/offline status for all devices.')]
class RefreshDeviceStatusCommand extends Command
{
    public function __construct(
        private readonly DeviceRepository $deviceRepository,
        private readonly ConnectivityService $connectivityService,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('threshold', null, InputOption::VALUE_REQUIRED, 'Threshold in seconds', 20);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $threshold = (int) $input->getOption('threshold');
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        $updatedCount = 0;

        foreach ($this->deviceRepository->findAll() as $device) {
            $shouldBeOnline = $this->connectivityService->isOnline($device->getLastSeen(), $now, $threshold);

            if ($device->isOnline() !== $shouldBeOnline) {
                $device->setOnline($shouldBeOnline);
                ++$updatedCount;
            }
        }

        $this->entityManager->flush();

        $output->writeln(sprintf('[%s] connectivity check complete (updated=%d)', $now->format('Y-m-d H:i:s'), $updatedCount));

        return Command::SUCCESS;
    }
}

