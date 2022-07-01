<?php

namespace App\Exceptions;

use Exception;

class MachinerySubCategoryNotFoundException extends Exception
{
    /** @var string */
    public $errorType = 'sub_category_not_found';

    /**
     * MachinerySubCategoryNotFoundException constructor.
     * @param string $message
     */
    public function __construct($message = 'Unable to retrieve Sub Category.')
    {
        parent::__construct($message);
    }
}
