<?php

namespace App\Command;

use App\Service\SyncInstrumentService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncInstrumentsCommand extends Command
{
    protected static $defaultName = 'instruments:sync';

    public function __construct(
        private SyncInstrumentService $instrumentSyncService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->instrumentSyncService->sync();

        return Command::SUCCESS;
    }
}
