<?php
namespace Modules\Leave\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Leave\Models\LeaveRequest;

class LeaveApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public LeaveRequest $leaveRequest) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $leaveTypeName = $this->leaveRequest->leaveType?->name ?? 'Leave';

        return (new MailMessage)
            ->subject("Your Leave Request Has Been Approved - #{$this->leaveRequest->id}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Great news! Your {$leaveTypeName} request for {$this->leaveRequest->days_count} days has been approved.")
            ->line("Dates: {$this->leaveRequest->start_date} to {$this->leaveRequest->end_date}")
            ->action('View Details', url("/my-leave-requests/{$this->leaveRequest->id}"))
            ->line('Thank you for using HRFlow!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'leave_request_id' => $this->leaveRequest->id,
            'leave_type' => $this->leaveRequest->leaveType?->name,
            'days_count' => $this->leaveRequest->days_count,
            'status' => 'approved',
            'message' => 'Your leave request has been approved.',
        ];
    }
}
