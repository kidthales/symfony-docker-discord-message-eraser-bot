<?php

namespace App\Command;

use App\Console\AbstractCommand;
use App\Dto\CreateUserPayload;
use App\Enum\Role;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface as MessageExceptionInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface  as SerializerExceptionInterface;

#[AsCommand(name: 'app:user:create', description: 'Create user', aliases: ['app:create-user'])]
final class CreateUserCommand extends AbstractCommand
{
    private const string ARG_DISCORD_ID = 'discord_id';
    private const string OPT_SUPER = 'super';
    private const string OPT_ADMIN = 'admin';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addArgument(self::ARG_DISCORD_ID, InputArgument::REQUIRED, description: 'Discord user ID')
            ->addOption(name: self::OPT_ADMIN, mode: InputOption::VALUE_NONE, description: 'Create admin user')
            ->addOption(name: self::OPT_SUPER, mode: InputOption::VALUE_NONE, description: 'Create super admin user')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws MessageExceptionInterface
     * @throws SerializerExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $roles = [];
        if ($input->getOption(self::OPT_SUPER)) {
            $roles[] = Role::SuperAdmin->value;
        } else if ($input->getOption(self::OPT_ADMIN)) {
            $roles[] = Role::Admin->value;
        }

        $this->io->definitionList(
            $this->definitionListConverter->convert(
                $this->actionDispatcher->createUser(
                    new CreateUserPayload($input->getArgument(self::ARG_DISCORD_ID), $roles),
                    true
                )
            )
        );

        return AbstractCommand::SUCCESS;
    }
}
