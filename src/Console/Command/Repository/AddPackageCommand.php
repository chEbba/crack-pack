<?php
/**
 * @LICENSE_TEXT
 */

namespace Che\CrackPack\Console\Command\Repository;

use Che\CrackPack\Package\OverriddenPackageLoader;
use Composer\Json\JsonFile;
use Composer\Package\Dumper\ArrayDumper;
use Composer\Package\Loader\ArrayLoader;
use Composer\Package\Loader\JsonLoader;
use Composer\Package\Loader\ValidatingArrayLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AddPackageCommand
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class AddPackageCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('repository:add')
            ->addArgument('file', InputArgument::REQUIRED)
            ->addArgument('repository', InputArgument::OPTIONAL, '', 'packages.json')
            ->addOption('url', null, InputOption::VALUE_OPTIONAL)
            ->addOption('package-name', null, InputOption::VALUE_OPTIONAL)
            ->addOption('package-version', null, InputOption::VALUE_OPTIONAL)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        if (!is_readable($file)) {
            throw new \RuntimeException(sprintf('Can not open file "%s"', $file));
        }

        $url = $input->getOption('url') ?: realpath($file);

        $overrides = [
            'dist' => [
                'type' => 'tar',
                'url' => $url,
                'shasum' => sha1_file($file)
            ]
        ];
        foreach (['name', 'version'] as $parameter) {
            if ($value = $input->getOption('package-'.$parameter)) {
                $overrides[$parameter] = $value;
            }
        }

        $loader = new JsonLoader(new OverriddenPackageLoader(new ValidatingArrayLoader(new ArrayLoader(), false), $overrides));
        $package = $loader->load(sprintf('phar://%s/composer.json', $file));

        $repositoryFile = new JsonFile($input->getArgument('repository'));
        $repo = $repositoryFile->exists() ? $repositoryFile->read() : [];

        $dumper = new ArrayDumper();
        $repo['packages'][$package->getName()][$package->getVersion()] = $dumper->dump($package);

        $repositoryFile->write($repo);
    }
}
