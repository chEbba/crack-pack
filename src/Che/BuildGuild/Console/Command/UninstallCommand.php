<?php
/**
 * @LICENSE_TEXT
 */

namespace Che\BuildGuild\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UninstallCommand
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class UninstallCommand extends BaseGuildCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('uninstall')
            ->addArgument('name', InputArgument::REQUIRED)
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $guild = $this->createGuild($input, $output);

        $package = $guild->uninstall($input->getArgument('name'));

        $output->writeln(sprintf('Package <info>%s:%s</info> uninstalled', $package->getName(), $package->getPrettyVersion()));
    }
}
