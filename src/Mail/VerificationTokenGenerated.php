<?php
/**
 * This file is part of Jrean\UserVerification package.
 *
 * (c) Jean Ragouin <go@askjong.com> <www.askjong.com>
 */
namespace Jrean\UserVerification\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class VerificationTokenGenerated extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * User instance.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    public $user;

    /**
     * The subject of the message.
     *
     * @var string|null
     */
    public $subject;

    /**
     * The person/company/project e-mail the message is from.
     *
     * @var string|null
     */
    public $from_address;

    /**
     * The person/company/project name the message is from.
     *
     * @var string|null
     */
    public $from_name;

    /**
     * Create a new message instance.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string|null  $subject
     * @param  string|null  $from_address
     * @param  string|null  $from_name
     * @return void
     */
    public function __construct(
        AuthenticatableContract $user,
        $subject = null,
        $from_address = null,
        $from_name = null
    )
    {
        $this->user = $user;
        $this->subject = $subject;
        $this->from_address = $from_address;
        $this->from_name = $from_name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if (! empty($this->from_address)) {
            $this->from($this->from_address, $this->from_name);
        }

        $locale = $this->user->locale??'en';
        $view = "emails.$locale.email_verification";
        if (!view()->exists($view))
            $view = "emails.en.email_verification";

    return $this->markdown($view)->with([
         'name' => $this->user->first_name . " " . $this->user->last_name,
        'url' => route('email-verification.check', $this->user->verification_token) . '?email=' . urlencode($this->user->email)
     ])->with('setSubject', function($subject) {   // Use to be able to set the subject from within the markdown blade
         $this->subject($subject);
     });
    }
}
