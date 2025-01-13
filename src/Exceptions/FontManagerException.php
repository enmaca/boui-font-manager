<?php

namespace Enmaca\Backoffice\FontManager\Exceptions;

use Exception;
use Throwable;

class FontManagerException extends Exception
{
    public function __construct(string $message = 'Product Designer Exception', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
