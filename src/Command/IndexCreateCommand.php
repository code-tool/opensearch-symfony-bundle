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
    name: 'opensearch:index:create',
    description: 'Create OpenSearch indexes',
)]
class IndexCreateCommand extends Command
{
    private Manager $indexManager;

    public function __construct(Manager $indexManager)
    {
        parent::__construct();
        $this->indexManager = $indexManager;
    }

    protected function configure(): void
    {
        $this->addArgument('index', InputArgument::OPTIONAL, 'Index from configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        if ('' === ($name = $input->getArgument('index'))) {
            if (!$io->confirm("Are you sure you want to create ALL indexes'?", false)) {
                $io->info('Creation cancelled');

                return Command::SUCCESS;
            }
            foreach ($this->indexManager->getIndexes() as $index) {
                $index->create();
            }
        } else {
            $this->indexManager->getIndex($name)->create();
        }

        return Command::SUCCESS;
    }
}