<?php

namespace Modules\Payroll\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Payroll\Models\Payslip;

class PayrollFinalizedNotification extends Notification 
{

    public function __construct(public Payslip $payslip) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Notice of issuance of a financial voucher' . $this->payslip->payrollRun->period_name)
            ->greeting('Hello ' . $notifiable->name . '،')
            ->line('The payroll has been approved, and your payslip has been issued.')
            ->line('Net Salary.: ' . number_format($this->payslip->net_salary, 2))
            ->action('Display the financial voucher', url('/payslips/' . $this->payslip->id))
            ->line('Thank you');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'payslip_id' => $this->payslip->id,
            'payroll_run_id' => $this->payslip->payroll_run_id,
            'net_salary' => $this->payslip->net_salary,
            'message' => '' . $this->payslip->payrollRun->period_name,
        ];
    }
}
