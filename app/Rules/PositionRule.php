<?php

namespace App\Rules;

use App\Models\Rank;
use Illuminate\Contracts\Validation\Rule;
use Symfony\Component\HttpFoundation\ParameterBag;

class PositionRule implements Rule
{
    private $request;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(ParameterBag $request)
    {
        $this->request = $request;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($this->request->get('department') === config('employee.departments.crewing')) {
            return Rank::where('name', $value)->exists();
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid position.';
    }
}
