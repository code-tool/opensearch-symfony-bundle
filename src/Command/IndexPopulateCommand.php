<?php

declare(strict_types=1);

namespace CodeTool\OpenSearch\Command;

use CodeTool\OpenSearch\Index\IndexConfig;
use CodeTool\OpenSearch\Index\IndexManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'opensearch:index:populate',
    description: 'Populate OpenSearch indexes',
)]
class IndexPopulateCommand extends Command
{
    private IndexManager $indexManager;

    public function __construct(IndexManager $indexManager)
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
        if ('' === ($index = $input->getArgument('index'))) {
            if (!$io->confirm("Are you sure you want to create ALL indexes'?", false)) {
                $io->info('Creation cancelled');

                return Command::SUCCESS;
            }
            foreach ($this->indexManager->getIndexes() as $config) {
                $this->reIndex($config, $io);
            }
        } else {
            $this->reIndex($this->indexManager->getConfig($index), $io);
        }

        return Command::SUCCESS;
    }

    private function reIndex(IndexConfig $config, SymfonyStyle $io): void
    {
        if (null === ($generator = $config->getGenerator())) {
            $io->warning("Index '{$config->getAlias()}' has no generator");

            return;
        }

        foreach ($generator->getDocuments() as $document) {
            if (false === $this->indexManager->addDocument($config->getAlias(), $document)) {
                $io->warning("Index '{$config->getAlias()}' already exists");
            }
        }
    }
}