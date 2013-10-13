<?php

namespace Che\BuildGuild\Installer;

use Che\BuildGuild\Util\Environment;
use Che\BuildGuild\Util\Filesystem;
use Che\LogStock\LoggerManager;
use Composer\Downloader\DownloadManager;
use Composer\Installer\InstallerInterface;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Util\ProcessExecutor;

/**
 * Class BuildInstaller
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class GuildInstaller implements InstallerInterface
{
    const PACKAGE_TYPE = 'guild';
    const DIR_BUILDS = 'builds';
    const DIR_CURRENT = 'current';
    const BUILD_SCRIPT = '.build/setup';

    private $buildPath;
    private $dm;
    private $process;
    private $fs;
    private $logger;

    public function __construct($basePath, DownloadManager $dm, ProcessExecutor $process, Filesystem $fs)
    {
        $this->buildPath = rtrim($basePath, '/');
        $this->dm = $dm;
        $this->process = $process;
        $this->fs = $fs;
        $this->logger = LoggerManager::getLogger(__CLASS__);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return $packageType === self::PACKAGE_TYPE;
    }

    /**
     * {@inheritDoc}
     */
    public function isInstalled(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        return $repo->hasPackage($package) && is_readable($this->getInstallPath($package));
    }

    /**
     * {@inheritDoc}
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $this->packageInstall($repo, $package, 'install');
    }

    /**
     * {@inheritDoc}
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        $this->checkInstalled($repo, $initial);

        $this->packageInstall($repo, $target, 'update');

        $repo->removePackage($initial);
        $this->cleanBuild($initial);
    }

    /**
     * {@inheritDoc}
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $this->checkInstalled($repo, $package);

        $this->runCommand($this->getInstallPath($package), 'uninstall');
        $this->fs->remove($this->getPackageCurrentPath($package));
        $repo->removePackage($package);
        $this->cleanBuild($package);
        $this->fs->remove($this->getPackageCurrentPath($package));
    }

    public function test(InstalledRepositoryInterface $repo, PackageInterface $package, $reportPath = null)
    {
        $this->checkInstalled($repo, $package);

        $path = $this->getInstallPath($package);
        $this->runCommand($path, 'test', [], $reportPath ? ['report' => $reportPath] : []);
    }

    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        return $this->getPackageCurrentPath($package);
    }

    private function checkInstalled(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        if (!$this->isInstalled($repo, $package)) {
            throw new \InvalidArgumentException(sprintf('Package "%s" is not installed', $package));
        }
    }

    private function initBuildPath()
    {
        $this->fs->ensureDirectoryExists($this->buildPath);
        $this->buildPath = realpath($this->buildPath);
    }

    private function getPackagePath(PackageInterface $package)
    {
        return sprintf('%s/%s', $this->buildPath, $package->getPrettyName());
    }

    private function getPackageBuildPath(PackageInterface $package)
    {
        return sprintf('%s/%s/%s', $this->getPackagePath($package), self::DIR_BUILDS, $package->getVersion());
    }

    private function getPackageCurrentPath(PackageInterface $package)
    {
        return sprintf('%s/%s', $this->getPackagePath($package), self::DIR_CURRENT);
    }

    private function cleanBuild(PackageInterface $package)
    {
        $this->logger->info(sprintf('Cleaning package "%s"', $package));
        $installPath = $this->getPackageBuildPath($package);
        $this->fs->removeDirectory($installPath);

        // Clear package vendor dir if no other vendor packages exists
        if (strpos($package->getName(), '/')) {
            $packageVendorDir = dirname($installPath);
            if (is_dir($packageVendorDir) && !glob($packageVendorDir.'/*')) {
                $this->fs->removeDirectory($packageVendorDir);
            }
        }
    }

    private function packageInstall(InstalledRepositoryInterface $repo, PackageInterface $package, $command)
    {
        $this->initBuildPath();
        $buildPath = $this->getPackageBuildPath($package);

        $this->dm->download($package, $buildPath);

        try {
            $this->runCommand($buildPath, $command);
        } catch (ScriptException $e) {
            $this->cleanBuild($package);
            throw $e;
        }

        if (!$repo->hasPackage($package)) {
            $repo->addPackage(clone $package);
        }

        $this->fs->symlink($buildPath, $this->getPackageCurrentPath($package));

        return $buildPath;
    }

    public function runCommand($dir, $command, array $arguments = [], $options = [])
    {
        $buildScript = $dir . '/' . self::BUILD_SCRIPT;
        if (!is_executable($buildScript)) {
            throw new ScriptException($buildScript, 'Setup script was not found or is not executable');
        }

        $argumentLine = implode(' ', array_map(function($arg) {return escapeshellarg($arg);}, $arguments));

        array_walk($options, function (&$value, $option) {$value = sprintf('--%s=%s', escapeshellarg($option), escapeshellarg($value));});
        $optionLine = implode(' ', $options);

        $script = sprintf("%s %s %s %s", $buildScript, escapeshellarg($command), $argumentLine, $optionLine);
        try {
            $status = $this->process->execute($script);
        } catch (\Exception $e) {
            throw new ScriptException($buildScript, sprintf('Script "%s" can not be executed', $script), $e);
        }

        if ($status !== 0) {
            throw new ScriptException($buildScript, sprintf('Script finished with non-zero status code "%s"', $status));
        }
    }
}
