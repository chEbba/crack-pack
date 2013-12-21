<?php

namespace Che\BuildGuild;

use Che\BuildGuild\Installer\GuildInstaller;
use Che\BuildGuild\Installer\ScriptRunner;
use Che\BuildGuild\Util\Environment;
use Composer\Config;
use Composer\Downloader\DownloadManager;
use Composer\Downloader;
use Composer\Json\JsonFile;
use Composer\Repository\ComposerRepository;
use Composer\Repository\InstalledFilesystemRepository;

/**
 * Class BuildFactory
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class GuildFactory
{
    private $config;
    private $env;

    public function __construct(GuildConfig $config, Environment $env)
    {
        $this->config = $config;
        $this->env = $env;
    }

    public function build()
    {
        $remoteRepository = new ComposerRepository(
            ['url' => $this->config->getRepository()],
            $this->env->getIo(),
            $this->config->getComposer()
        );

        $localRepository = new InstalledFilesystemRepository(new JsonFile($this->config->getLocalRepository()));

        $installer = new GuildInstaller(
            $this->config->getInstallDir(), $this->createDownloadManager(),
            $this->env->getProcess(), $this->env->getFilesystem()
        );

        return new Guild($remoteRepository, $localRepository, $installer, $this->config->getStability());
    }

    private function createDownloadManager()
    {
        $dm = new DownloadManager(false, $this->env->getFilesystem());

        $dm->setDownloader('tar', new Downloader\TarDownloader($this->env->getIo(), $this->config->getComposer()));

        return $dm;
    }
}
