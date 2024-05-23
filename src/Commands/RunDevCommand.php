<?php

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RunDevCommand extends Command
{
    protected static $defaultName = 'run:dev';
    protected static $defaultDescription = 'Run a development server.';

    public function getName(): ?string
    {
        return self::$defaultName;
    }

    public function getDescription(): string
    {
        return self::$defaultDescription;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        shell_exec('php -S localhost:7777 -t dev-build');
        return 0;
    }
}