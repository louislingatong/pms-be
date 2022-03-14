<?php

namespace App\Exceptions;

use Exception;

class IntervalNotFoundException extends Exception
{
    /** @var string */
    public $errorType = 'interval_not_found';

    /**
     * IntervalNotFoundException constructor.
     * @param string $message
     */
    public function __construct($message = 'Unable to retrieve interval.')
    {
        parent::__construct($message);
    }
}
