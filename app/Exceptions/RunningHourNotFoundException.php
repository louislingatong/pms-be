<?php

namespace App\Exceptions;

use Exception;

class RunningHourNotFoundException extends Exception
{
    /** @var string */
    public $errorType = 'running_hour_not_found';

    /**
     * RunningHourNotFoundException constructor.
     * @param string $message
     */
    public function __construct($message = 'Unable to retrieve running hour.')
    {
        parent::__construct($message);
    }
}
