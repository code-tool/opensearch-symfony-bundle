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
    name: 'opensearch:list',
    description: 'List OpenSearch indexes',
)]
class ListCommand extends Command
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
        $indexes = $this->indexManager->getIndexes();
        $io->title('Configured OpenSearch Indexes');

        $rows = [];
        foreach ($indexes as $index) {
            $rows[] = [
                $index->getName(),
                \json_encode(array_map(static fn ($p): array => $p->getDefinition(), $index->getProperties())),
                $index->exists() ? 'Yes' : 'No',
            ];
        }

        $io->table(['Name', 'Mappings', 'Exists'], $rows);

        return Command::SUCCESS;

    }
}