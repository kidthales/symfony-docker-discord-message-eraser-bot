<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Style\DefinitionListConverter;
use App\Messenger\ActionDispatcher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractCommand extends Command
{
    public const int SUCCESS = Command::SUCCESS;
    public const int FAILURE = Command::FAILURE;
    public const int INVALID = Command::INVALID;

    /**
     * @var ActionDispatcher
     */
    protected ActionDispatcher $actionDispatcher;

    /**
     * @var SymfonyStyle
     */
    protected SymfonyStyle $io;

    /**
     * @var DefinitionListConverter
     */
    protected DefinitionListConverter $definitionListConverter;

    #[Required]
    public function setActionDispatcher(ActionDispatcher $actionDispatcher): void
    {
        $this->actionDispatcher = $actionDispatcher;
    }

    #[Required]
    public function setDefinitionListConverter(DefinitionListConverter $definitionListConverter): void
    {
        $this->definitionListConverter = $definitionListConverter;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }
}
