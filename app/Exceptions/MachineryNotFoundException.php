<?php

namespace App\Exceptions;

use Exception;

class MachineryNotFoundException extends Exception
{
    /** @var string */
    public $errorType = 'machinery_not_found';

    /**
     * MachineryNotFoundException constructor.
     * @param string $message
     */
    public function __construct($message = 'Unable to retrieve machinery.')
    {
        parent::__construct($message);
    }
}
