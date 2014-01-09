<?php

namespace Che\CrackPack\Console\Command;

use Che\CrackPack\ManagerConfig;
use Che\CrackPack\ManagerFactory;
use Composer\IO\ConsoleIO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class GuildCommand
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
abstract class BaseManagerCommand extends Command
{
    const DEFAULT_CONFIG_PATH = '/etc/crack-pack/config.yml';

    protected function configure()
    {
        $this
            ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Configuration path', self::DEFAULT_CONFIG_PATH)
        ;
    }

    protected function createGuild(InputInterface $input, OutputInterface $output)
    {
        $io = new ConsoleIO($input, $output, $this->getHelperSet());
        $config = $this->readConfig($input);

        return (new ManagerFactory($config, $io))->build();
    }

    private function readConfig(InputInterface $input)
    {
        $configPath = $input->getOption('config');
        if (!is_readable($configPath)) {
            throw new \RuntimeException(sprintf('Config file "%s" not exists or is not readable', $configPath));
        }

        return new ManagerConfig(Yaml::parse($configPath));
    }
}
