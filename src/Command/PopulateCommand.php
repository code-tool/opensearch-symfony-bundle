<?php

declare(strict_types=1);

namespace CodeTool\OpenSearch\Command;

use CodeTool\OpenSearch\Index\Manager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'opensearch:populate',
    description: 'Populate OpenSearch indexes',
)]
class PopulateCommand extends Command
{
    private Manager $indexManager;

    public function __construct(Manager $indexManager)
    {
        $this->indexManager = $indexManager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('index', InputArgument::OPTIONAL, 'Index from configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        if (null === ($index = $input->getArgument('index'))) {
            if (!$io->confirm("Are you sure you want to create ALL indexes'?", false)) {
                $io->info('Creation cancelled');

                return Command::SUCCESS;
            }
            $this->indexManager->reindexAll();
        } else {
            $this->indexManager->reindex($index);
        }

        return Command::SUCCESS;
    }
}