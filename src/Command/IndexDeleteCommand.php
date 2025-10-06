<?php

declare(strict_types=1);

namespace CodeTool\OpenSearch\Command;

use CodeTool\OpenSearch\Index\IndexConfig;
use CodeTool\OpenSearch\Index\Manager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'opensearch:index:delete',
    description: 'Delete OpenSearch indexes',
)]
class IndexDeleteCommand extends Command
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
        if ('' === ($index = $input->getArgument('index'))) {
            if (!$io->confirm("Are you sure you want to delete ALL indexes'?", false)) {
                $io->info('Deletion cancelled');

                return Command::SUCCESS;
            }
            foreach ($this->indexManager->getIndexes() as $config) {
                $this->deleteIndex($config, $io);
            }
        } else {
            if (!$io->confirm("Are you sure you want to delete index '{$index}'?", false)) {
                $io->info('Deletion cancelled');

                return Command::SUCCESS;
            }
            $this->deleteIndex($this->indexManager->getIndex($index), $io);
        }

        return Command::SUCCESS;
    }

    private function deleteIndex(IndexConfig $config, SymfonyStyle $io): void
    {
        if (!$this->indexManager->exists($config->getAlias())) {
            $io->warning("Index '{$config->getAlias()}' does not exist");

            return;
        }

        if (false === $this->indexManager->delete($config->getAlias())) {
            $io->error("Failed to delete index '{$config->getAlias()}'");

            return;
        }
        $io->success("Index '{$config->getAlias()}' deleted successfully");
    }
}