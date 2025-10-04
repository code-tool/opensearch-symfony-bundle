<?php

declare(strict_types=1);

namespace CodeTool\OpenSearch\Command;

use CodeTool\OpenSearch\Index\IndexManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'opensearch:index',
    description: 'Manage OpenSearch indexes',
)]
class IndexActionCommand extends Command
{
    private IndexManager $indexManager;

    public function __construct(IndexManager $indexManager)
    {
        parent::__construct();
        $this->indexManager = $indexManager;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('action', InputArgument::REQUIRED, 'Action to perform (create, delete, exists, list)')
            ->addArgument('index', InputArgument::OPTIONAL, 'Index from configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $action = $input->getArgument('action');
        $index = $input->getArgument('index');

        try {
            switch ($action) {
                case 'list':
                    return $this->listIndexes($io);

                case 'create':
                    if (!$index) {
                        $io->error('Index is required for create action');

                        return Command::FAILURE;
                    }

                    return $this->createIndex($io, $index);

                case 'delete':
                    if (!$index) {
                        $io->error('Index is required for delete action');

                        return Command::FAILURE;
                    }

                    return $this->deleteIndex($io, $index);

                case 'exists':
                    if (!$index) {
                        $io->error('Index is required for exists action');

                        return Command::FAILURE;
                    }

                    return $this->checkExists($io, $index);

                default:
                    $io->error("Unknown action: {$action}");

                    return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }

    private function listIndexes(SymfonyStyle $io): int
    {
        $indexes = $this->indexManager->getIndexes();

        $io->title('Configured OpenSearch Indexes');

        $rows = [];
        foreach ($indexes as $config) {
            $rows[] = [
                $config->getAlias(),
                $config->getType(),
                $config->getPattern()
            ];
        }

        $io->table(['Alias', 'Type', 'Pattern'], $rows);

        return Command::SUCCESS;
    }

    private function createIndex(SymfonyStyle $io, string $index): int
    {
        if ($this->indexManager->exists($index)) {
            $io->warning("Index '{$index}' already exists");

            return Command::SUCCESS;
        }
        $io->text("Creating index: {$index}");
        if (false === $this->indexManager->create($index)) {
            $io->error("Failed to create index '{$index}'");

            return Command::FAILURE;
        }
        $io->success("Index '{$index}' created successfully");

        return Command::SUCCESS;
    }

    private function deleteIndex(SymfonyStyle $io, string $index): int
    {
        if (!$this->indexManager->exists($index)) {
            $io->warning("Index '{$index}' does not exist");

            return Command::SUCCESS;
        }
        if (!$io->confirm("Are you sure you want to delete index '{$index}'?", false)) {
            $io->info('Deletion cancelled');

            return Command::SUCCESS;
        }
        if (false === $this->indexManager->delete($index)) {
            $io->error("Failed to delete index '{$index}'");

            return Command::FAILURE;
        }
        $io->success("Index '{$index}' deleted successfully");

        return Command::SUCCESS;
    }

    private function checkExists(SymfonyStyle $io, string $index): int
    {
        $this->indexManager->exists($index)
            ? $io->success("Index '{$index}' exists")
            : $io->info("Index '{$index}' does not exist");

        return Command::SUCCESS;
    }
}