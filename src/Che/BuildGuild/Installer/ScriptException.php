<?php

namespace Che\BuildGuild\Installer;

/**
 * Class ScriptInstallException
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class ScriptException extends \RuntimeException
{
    private $path;
    private $reason;

    public function __construct($path, $reason, \Exception $previous = null)
    {
        parent::__construct(sprintf('Script "%s" processing error: %s', $path, $reason), 0, $previous);
        $this->path = $path;
        $this->reason = $reason;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return int
     */
    public function getReason()
    {
        return $this->reason;
    }
}
