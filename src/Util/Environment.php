<?php
/**
 * @LICENSE_TEXT
 */

namespace Che\CrackPack\Util;

use Composer\IO\IOInterface;
use Composer\Util\ProcessExecutor;

/**
 * Class Environment
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class Environment
{
    private $io;
    private $process;
    private $fs;

    public function __construct(IOInterface $io)
    {
        $this->io = $io;
        $this->process = new ProcessExecutor($io);
        $this->fs = new Filesystem($this->process);
    }

    /**
     * @return IOInterface
     */
    public function getIo()
    {
        return $this->io;
    }

    /**
     * @return ProcessExecutor
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->fs;
    }
}
