<?php

namespace Che\CrackPack;

use Che\CrackPack\Installer\PackageInstaller;
use Che\CrackPack\Util\Filesystem;
use Composer\Config;
use Composer\Downloader\DownloadManager;
use Composer\Downloader;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Repository\ComposerRepository;
use Composer\Repository\InstalledFilesystemRepository;
use Composer\Util\ProcessExecutor;

/**
 * Class BuildFactory
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class ManagerFactory
{
    private $config;
    private $io;
    private $process;
    private $fs;

    public function __construct(ManagerConfig $config, IOInterface $io)
    {
        $this->config = $config;
        $this->io = $io;
        $this->process = new ProcessExecutor($io);
        $this->fs = new Filesystem($this->process);
    }

    public function build()
    {
        $remoteRepository = new ComposerRepository(
            ['url' => $this->config->getRepository()],
            $this->io,
            $this->config->getComposer()
        );

        $localRepository = new InstalledFilesystemRepository(new JsonFile($this->config->getLocalRepository()));

        $installer = new PackageInstaller(
            $this->config->getInstallDir(), $this->createDownloadManager(),
            $this->process, $this->fs
        );

        return new PackageManager($remoteRepository, $localRepository, $installer, $this->config->getStability());
    }

    private function createDownloadManager()
    {
        $dm = new DownloadManager(false, $this->fs);

        $dm->setDownloader('tar', new Downloader\TarDownloader($this->io, $this->config->getComposer()));

        return $dm;
    }
}
