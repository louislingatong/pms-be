<?php

namespace App\Exceptions;

use Exception;

class IntervalUnitNotFoundException extends Exception
{
    /** @var string */
    public $errorType = 'interval_unit_not_found';

    /**
     * IntervalUnitNotFoundException constructor.
     * @param string $message
     */
    public function __construct($message = 'Unable to retrieve interval unit.')
    {
        parent::__construct($message);
    }
}
