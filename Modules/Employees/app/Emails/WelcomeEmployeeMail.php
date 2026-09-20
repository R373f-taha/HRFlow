<?php

namespace Modules\Employees\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Employees\Models\Employee;

class WelcomeEmployeeMail extends Mailable
{
    use  SerializesModels;

    public function __construct(
        public Employee $employee,
        public string $temporaryPassword
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to HRFlow - Your Credentials'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'employees::emails.welcome',
            with: [
                'employeeName' => $this->employee->user->name,
                'email' => $this->employee->user->email,
                'password' => $this->temporaryPassword,
            ]
        );
    }
}
