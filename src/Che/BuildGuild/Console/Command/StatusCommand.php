<?php
/**
 * @LICENSE_TEXT
 */

namespace Che\BuildGuild\Console\Command;

use Composer\Package\PackageInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Class StatusCommand
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class StatusCommand extends BaseGuildCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('status');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $guild = $this->createGuild($input, $output);

        $packages = $guild->getInstalledPackages();
        usort($packages, function (PackageInterface $package1, PackageInterface $package2) {
            return strcmp($package1->getName(), $package2->getName());
        });

        $output->writeln(sprintf('<info>installed</info>: %s', count($packages)));
        $output->writeln('');

        foreach ($packages as $package) {
            $this->outputPackageInfo($package, $output);
            $output->writeln('');
        }
    }

    private function outputPackageInfo(PackageInterface $package, OutputInterface $output)
    {
        $output->writeln('<info>name</info>     : ' . $package->getPrettyName());
        $output->writeln('<info>version</info>  : ' . $package->getPrettyVersion());
    }
}
