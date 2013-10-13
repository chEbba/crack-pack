<?php

namespace Che\BuildGuild\Console\Command;

use Che\BuildGuild\Installer\GuildInstaller;
use Composer\Package\Package;
use Composer\Package\Version\VersionParser;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Class InstallCommand
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class InstallCommand extends BaseGuildCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('install')
            ->addArgument('name', InputArgument::REQUIRED)
            ->addOption('file', 'f', InputOption::VALUE_OPTIONAL, 'File name')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $guild = $this->createGuild($input, $output);

        $parts = explode(':', $name = $input->getArgument('name'), 2);
        $name = array_shift($parts);
        $version = array_shift($parts);

        if ($file = $input->getOption('file')) {
            $package = $guild->installFile($file, $name, $version);
        } else {
            $package = $guild->installPackage($name, $version);
        }

        $output->writeln(sprintf('Package <info>%s:%s</info> installed', $package->getName(), $package->getPrettyVersion()));
    }
}
