<?php

declare(strict_types=1);

namespace CodeTool\OpenSearch\Command;

use CodeTool\OpenSearch\Index\DataStream;
use CodeTool\OpenSearch\Index\Index;
use CodeTool\OpenSearch\Index\IndexTemplate;
use CodeTool\OpenSearch\Index\Manager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'opensearch:delete',
    description: 'Delete OpenSearch indexes',
)]
class DeleteCommand extends Command
{
    public const array ALLOWED_TYPES
        = [
            Index::FIELD_INDEX,
            DataStream::FIELD_DATA_STREAM,
            IndexTemplate::FIELD_TEMPLATE
        ];

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
        $type = $input->getOption('type');
        if (false === \in_array($type, self::ALLOWED_TYPES, true)) {
            throw new \InvalidArgumentException(
                \sprintf('Invalid type "%s, must be one of [%s]"', $type, \implode(', ', self::ALLOWED_TYPES))
            );
        }
        $name = $input->getArgument('name');
        if (!$io->confirm(\sprintf('Are you sure you want to create %s "%s"?', $type, $name), false)) {
            $io->info('Creation cancelled');

            return Command::SUCCESS;
        }
        switch ($type) {
            case Index::FIELD_INDEX:
                $this->indexManager->getIndex($name)->delete();
                break;
            case IndexTemplate::FIELD_TEMPLATE:
                $this->indexManager->getIndexTemplate($name)->delete();
                break;
            case DataStream::FIELD_DATA_STREAM:
                $this->indexManager->getDataStream($name)->delete();
                break;
        }

        return Command::SUCCESS;
    }
}