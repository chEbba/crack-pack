<?php

namespace Che\CrackPack\Util;

use Composer\Util\Filesystem as BaseFilesystem;
use Composer\Util\ProcessExecutor;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

/**
 * Class FileSystem
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class Filesystem extends BaseFilesystem
{
    private $sfs;

    public function __construct(ProcessExecutor $executor = null)
    {
        $this->sfs = new SymfonyFilesystem();
        parent::__construct($executor);
    }


    public function symlink($source, $target)
    {
        $this->sfs->symlink($source, $target);
    }
}
