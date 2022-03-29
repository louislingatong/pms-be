<?php

namespace App\Mail;

use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ResetPasswordActivateAccount extends Mailable
{
    use Queueable, SerializesModels;

    /** @var string */
    public $view;
    /** @var string */
    public $subject;
    /** @var PasswordReset */
    protected $passwordReset;
    /** @var string */
    protected $url;
    /** @var User */
    protected $user;

    /**
     * Create a new message instance.
     *
     * @param PasswordReset $passwordReset
     * @return void
     */
    public function __construct(PasswordReset $passwordReset)
    {
        $this->view = 'mail.users.activate';
        $this->subject = 'Activate your Account';
        $this->user = $passwordReset->user;
        $this->url = env('APP_URL') . '/password/reset?token=' . $passwordReset->getAttribute('token');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
            ->view($this->view)
            ->with([
                'user' => $this->user,
                'url' => $this->url,
            ]);
    }
}
