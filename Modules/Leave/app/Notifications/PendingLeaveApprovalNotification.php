<?php

namespace Modules\Leave\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Leave\Models\LeaveRequest;

class PendingLeaveApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public LeaveRequest $leaveRequest) {}

    public function via(object $notifiable): array
    {
        // Deliver via email and save to database
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $employeeName = $this->leaveRequest->employee?->first_name ?? 'An employee';
        $leaveTypeName = $this->leaveRequest->leaveType?->name ?? 'Leave';

        return (new MailMessage)
            ->subject("New Leave Request Pending Approval - #{$this->leaveRequest->id}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$employeeName} has submitted a new {$leaveTypeName} request.")
            ->line("Duration: {$this->leaveRequest->start_date} to {$this->leaveRequest->end_date} ({$this->leaveRequest->days_count} days)")
            ->action('Review Request', url("/leave-requests/{$this->leaveRequest->id}"))
            ->line('Please review and take action on this pending request.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'leave_request_id' => $this->leaveRequest->id,
            'employee_id' => $this->leaveRequest->employee_id,
            'leave_type' => $this->leaveRequest->leaveType?->name,
            'days_count' => $this->leaveRequest->days_count,
            'message' => 'New leave request submitted for your approval.',
        ];
    }
}
