<?php
/**
 * @LICENSE_TEXT
 */

namespace Che\BuildGuild;

use Composer\Package\PackageInterface;

/**
 * Class InstallationException
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class InstallationException extends \RuntimeException
{
    private $package;

    public function __construct(PackageInterface $package, $reason, \Exception $previous = null)
    {
        parent::__construct(sprintf('Can not install package "%s": %s', $package, $reason), 0, $previous);

        $this->package = $package;
    }
}
