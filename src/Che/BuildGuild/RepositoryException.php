<?php
/**
 * @LICENSE_TEXT
 */

namespace Che\BuildGuild;

/**
 * Class RepositoryException
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class RepositoryException extends \RuntimeException
{
    public function __construct($message, \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
