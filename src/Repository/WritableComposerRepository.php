<?php
/**
 * @LICENSE_TEXT
 */

namespace Che\CrackPack\Repository;

use Composer\Package\PackageInterface;
use Composer\Repository\ComposerRepository;
use Composer\Repository\WritableRepositoryInterface;

/**
 * Class WritableComposerRepository
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class WritableComposerRepository extends ComposerRepository implements WritableRepositoryInterface
{
    /**
     * Writes repository (f.e. to the disc).
     */
    public function write()
    {
        // TODO: Implement write() method.
    }

    /**
     * Get unique packages, with aliases resolved and removed
     *
     * @return PackageInterface[]
     */
    public function getCanonicalPackages()
    {
        // TODO: Implement getCanonicalPackages() method.
    }

    /**
     * Forces a reload of all packages
     */
    public function reload()
    {
        // TODO: Implement reload() method.
    }
}
