<?php

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PublishCommand extends Command
{
    protected static $defaultName = 'publish';
    protected static $defaultDescription = 'Build the site and Publish it on Github.';

    public function getName(): ?string
    {
        return self::$defaultName;
    }

    public function getDescription(): string
    {
        return self::$defaultDescription;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->writeln("Building...");

        $buildResult = shell_exec('sh app build 2>&1'); // Capture stderr for better error handling
        if (!$buildResult) {
            $io->error("Build failed:\n$buildResult");
            return Command::FAILURE;
        }

        $io->writeln("Publishing...");

        // Stage Changes
        shell_exec('git add .');
        shell_exec('git commit -m "New site built: ' . uniqid() . '"');

        // Confirm Push
        if (!$io->confirm('Do you want to push the changes to GitHub?', true)) {
            $io->warning("Publishing aborted. Changes have been committed but not pushed.");
            return Command::SUCCESS; // User chose not to push, but the command succeeded
        }

        // Push to GitHub
        $pushResult = shell_exec('git push 2>&1'); // Capture stderr
        if (!$pushResult) {
            $io->error("Push failed:\n$pushResult");
            return Command::FAILURE;
        }

        $io->success('Site successfully built and published to GitHub!');
        return Command::SUCCESS;
    }
}
