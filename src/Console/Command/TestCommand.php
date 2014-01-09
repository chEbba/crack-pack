<?php
/**
 * @LICENSE_TEXT
 */

namespace Che\CrackPack\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TestCommand
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class TestCommand extends BaseGuildCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('test')
            ->addArgument('name', InputArgument::REQUIRED, 'Package name')
            ->addOption('report', 'r', InputOption::VALUE_OPTIONAL)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $guild = $this->createGuild($input, $output);

        $guild->testPackage($input->getArgument('name'), $input->getOption('report'));
    }
}
