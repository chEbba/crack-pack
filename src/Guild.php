<?php
/**
 * @LICENSE_TEXT
 */

namespace Che\CrackPack;

use Che\CrackPack\Installer\GuildInstaller;
use Che\CrackPack\Installer\ScriptRunner;
use Composer\Package\BasePackage;
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
    private $stability;

    public function __construct(RepositoryInterface $remoteRepository, InstalledRepositoryInterface $localRepository, GuildInstaller $installer, $stability = 'stable')
    {
        if (!isset(BasePackage::$stabilities[$stability])) {
            throw new \InvalidArgumentException(sprintf('Unknown stability "%s"', $stability));
        }

        $this->remoteRepository = $remoteRepository;
        $this->localRepository  = $localRepository;
        $this->installer        = $installer;
        $this->stability        = $stability;
    }

    /**
     * @return array An array of stability names that guild supports
     */
    public function getAcceptableStability()
    {
        $acceptable = [];
        foreach (BasePackage::$stabilities as $name => $value) {
            if ($value <= BasePackage::$stabilities[$this->stability]) {
                $acceptable[] = $name;
            }
        }

        return $acceptable;
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
            if (in_array($version, BasePackage::$stabilities)) {
                $package = $this->findMostRecentPackage($name, $version);
            } else {
                $package = $this->findPackage($name, $version);
                if ($package && !$this->isStabilityAcceptable($package)) {
                    $package = null;
                }
            }
        } else {
            $package = $this->findMostRecentPackage($name);
        }
        if (!$package) {
            throw new PackageNotFoundException($name . ($version ? ':' . $version : ''));
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
        $package = $this->findInstalledPackage($name);
        if (!$package) {
            throw new PackageNotFoundException($name);
        }

        $this->installer->test($this->localRepository, $package, $reportDir);
    }

    public function uninstallPackage($name)
    {
        $package = $this->findInstalledPackage($name);
        if (!$package) {
            throw new PackageNotFoundException($name);
        }

        $this->installer->uninstall($this->localRepository, $package);

        $this->localRepository->write();

        return $package;
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

        $installed = $this->findInstalledPackage($package->getName());
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

    private function findMostRecentPackage($name, $stability = null)
    {
        /** @var PackageInterface $recent */
        $recent = null;
        /** @var PackageInterface $package */
        foreach ($this->remoteRepository->findPackages($name) as $package) {
            if (!$this->isStabilityAcceptable($package, $stability)) {
                continue;
            }

            // Try to find latest version
            if (!$recent || version_compare($recent->getVersion(), $package->getVersion(), '<')) {
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
            throw new RepositoryException(
                sprintf(
                    'Installed package repository is corrupted. Several "%s packages installed": %s',
                    $name, implode(', ', $installed)
                )
            );
        }

        return array_shift($installed);
    }

    /**
     * @param PackageInterface $package
     * @param string|null $stability
     *
     * @return bool
     */
    private function isStabilityAcceptable(PackageInterface $package, $stability = null)
    {
        if ($stability) {
            return $stability === $package->getStability();
        }

        return in_array($package->getStability(), $this->getAcceptableStability());
    }
}
