<?php
/**
 * @LICENSE_TEXT
 */

namespace Che\BuildGuild;

use Che\BuildGuild\Installer\GuildInstaller;
use Che\BuildGuild\Installer\ScriptRunner;
use Composer\Package\Package;
use Composer\Package\PackageInterface;
use Composer\Package\Version\VersionParser;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Repository\RepositoryInterface;

/**
 * Class Guild
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class Guild
{
    private $remoteRepository;
    private $localRepository;
    private $installer;

    public function __construct(RepositoryInterface $remoteRepository, InstalledRepositoryInterface $localRepository, GuildInstaller $installer)
    {
        $this->remoteRepository = $remoteRepository;
        $this->localRepository  = $localRepository;
        $this->installer        = $installer;
    }

    public function findPackage($name, $version)
    {
        try {
            /** @var PackageInterface $package */
            return  $this->remoteRepository->findPackage($name, $version);
        } catch (\Exception $e) {
            throw new RepositoryException('Error during package finding', $e);
        }
    }

    public function installPackage($name, $version = null)
    {
        if ($version) {
            $package = $this->findPackage($name, $version);
        } else {
            $package = $this->findMostRecentPackage($name);
        }
        if (!$package) {
            throw new PackageNotFoundException($name . $version ? ':' . $version : '');
        }

        $this->install($package);

        return $package;
    }

    public function installFile($file, $name, $version = null, $distType = 'tar')
    {
        if (!$version) {
            // TODO: change default version?
            $version = 'dev-master';
        }
        $parser = new VersionParser();
        $package = new Package($name, $parser->normalize($version), $version);
        $package->setType(GuildInstaller::PACKAGE_TYPE);
        $package->setDistType($distType);
        $package->setDistUrl($file);

        $this->install($package);

        return $package;
    }

    public function testPackage($name, $reportDir = null)
    {
        try {
            $package = $this->findInstalledPackage($name);
        } catch (\Exception $e) {
            throw new RepositoryException('Error while reading local repository', $e);
        }
        if (!$package) {
            throw new PackageNotFoundException($name);
        }

        $this->installer->test($this->localRepository, $package, $reportDir);
    }

    /**
     * @return PackageInterface[]
     */
    public function getInstalledPackages()
    {
        return $this->localRepository->getPackages();
    }

    /**
     * @param PackageInterface $package
     *
     * @throws InstallationException
     * @throws RepositoryException
     */
    private function install(PackageInterface $package)
    {
        if (!$this->installer->supports($package->getType())) {
            throw new InstallationException(
                $package,
                sprintf('Package type "%s" is not supported by installer', $package->getType())
            );
        }

        if ($this->installer->isInstalled($this->localRepository, $package)) {
            return;
        }

        try {
            $installed = $this->findInstalledPackage($package->getName());
        } catch (\Exception $e) {
            throw new RepositoryException('Error while reading local repository', $e);
        }

        try {
            if ($installed) {
                $this->installer->update($this->localRepository, $installed, $package);
            } else {
                $this->installer->install($this->localRepository, $package);
            }
        } catch (\Exception $e) {
            throw new InstallationException($package, $e->getMessage(), $e);
        }

        $this->localRepository->write();
    }

    private function findMostRecentPackage($name)
    {
        /** @var PackageInterface $recent */
        $recent = null;
        /** @var PackageInterface $package */
        foreach ($this->remoteRepository->findPackages($name) as $package) {
            if (!$recent || ($recent->getVersion() < $package->getVersion())) {
                $recent = $package;
            }
        }

        return $recent;
    }

    private function findInstalledPackage($name)
    {
        $installed = $this->localRepository->findPackages($name);
        if (count($installed) === 0) {
            return null;
        }

        if (count($installed) > 1) {
            // TODO: some restore functionality
            throw new \RuntimeException(
                sprintf(
                    'Installed package repository is corrupted. Several "%s packages installed": %s',
                    $name, implode(', ', $installed)
                )
            );
        }

        return array_shift($installed);
    }
}
