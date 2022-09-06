<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\EmailAddressRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => [
                'required',
                new EmailAddressRule,
                'unique:users,email,' . $this->getId() . ',id',
            ],
            'password' => 'nullable',
        ];
    }

    public function getId()
    {
        /** @var User $user */
        $user = $this->route('user');
        return $user->getAttribute('id');
    }

    public function getFirstName()
    {
        return $this->input('first_name', null);
    }

    public function getLastName()
    {
        return $this->input('last_name', null);
    }

    public function getEmail()
    {
        return $this->input('email', null);
    }

    public function getPassword()
    {
        return $this->input('password', null);
    }
}
