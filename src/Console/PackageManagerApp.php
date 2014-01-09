<?php
/**
 * @LICENSE_TEXT
 */

namespace Che\CrackPack\Console;

use Symfony\Component\Console\Application;

/**
 * Class GuildApp
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class PackageManagerApp extends Application
{
    const NAME = 'CrackPack';

    public function __construct($version = 'UNKNOWN')
    {
        parent::__construct(self::NAME, $version);
    }

    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new Command\InstallCommand();
        $commands[] = new Command\UninstallCommand();
        $commands[] = new Command\StatusCommand();

        $commands[] = new Command\Repository\AddPackageCommand();

        return $commands;
    }
}
