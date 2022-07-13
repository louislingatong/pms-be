<?php

namespace App\Exceptions;

use Exception;

class UserInactiveException extends Exception
{
    /** @var string */
    public $errorType = 'user_inactive';

    /**
     * UserLockedException constructor.
     * @param string $message
     */
    public function __construct($message = 'Your account has been deactivated.')
    {
        parent::__construct($message);
    }
}
