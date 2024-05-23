<?php

namespace App\Commands;

use App\Lib\Site\AssetsBuilder;
use App\Lib\Site\PostsBuilder;
use Dibi\Exception;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SiteBuildCommand extends Command
{

    public function getName(): ?string
    {
        return "build";
    }

    public function getDescription(): string
    {
        return "Build the site";
    }

    /**
     * @throws Exception
     * @throws GuzzleException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("Building the site...");

        // posts
        (new PostsBuilder)->build();

        // assets
        (new AssetsBuilder)->build();
        $output->writeln("Asset files copied!");

        $output->writeln("Site built!");

        return 0;
    }

}