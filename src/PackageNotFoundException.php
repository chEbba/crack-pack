<?php
/**
 * @LICENSE_TEXT
 */

namespace Che\CrackPack;

/**
 * Class PackageNotFoundException
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class PackageNotFoundException extends \RuntimeException
{
    private $name;

    public function __construct($name, \Exception $e = null)
    {
        parent::__construct(sprintf('Package "%s" not found', $name), 0, $e);

        $this->name = $name;
    }
}
