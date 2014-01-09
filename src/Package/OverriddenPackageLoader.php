<?php
/**
 * @LICENSE_TEXT
 */

namespace Che\CrackPack\Package;

use Composer\Package\Loader\LoaderInterface;


/**
 * Class UnknownPackageLoader
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class OverriddenPackageLoader implements LoaderInterface
{
    private $loader;
    private $overrides;

    public function __construct(LoaderInterface $loader, array $overrides)
    {
        $this->loader = $loader;
        $this->overrides = $overrides;
    }

    public function load(array $config, $class = 'Composer\Package\CompletePackage')
    {
        $config = array_merge($config, $this->overrides);

        return $this->loader->load($config, $class);
    }
}
