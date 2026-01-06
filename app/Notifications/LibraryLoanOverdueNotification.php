<?php

namespace App\Notifications;

use App\Models\LibraryLoan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LibraryLoanOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $loan;
    protected $daysOverdue;

    /**
     * Create a new notification instance.
     */
    public function __construct(LibraryLoan $loan, int $daysOverdue)
    {
        $this->loan = $loan;
        $this->daysOverdue = $daysOverdue;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        $channels = ['database'];
        
        // Add mail channel if user has email
        if ($notifiable->email) {
            $channels[] = 'mail';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $bookTitle = $this->loan->copy->item->title ?? 'Book';
        $barcode = $this->loan->copy->barcode ?? 'N/A';
        $borrowerName = $this->loan->borrower_name ?? 'Borrower';
        
        $subject = "Library Book OVERDUE - {$this->daysOverdue} Day(s)";

        return (new MailMessage)
            ->subject($subject)
            ->error()
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line("⚠️ A borrowed book is OVERDUE by {$this->daysOverdue} day(s)!")
            ->line('**Book Details:**')
            ->line("• Title: {$bookTitle}")
            ->line("• Barcode: {$barcode}")
            ->line("• Borrower: {$borrowerName}")
            ->line("• Due Date: " . $this->loan->due_date->format('Y-m-d H:i'))
            ->line("• Days Overdue: {$this->daysOverdue}")
            ->action('View Loan Details', url('/admin/library/loans'))
            ->line('Please return the book immediately to avoid additional fines.')
            ->line('Thank you for your cooperation.');
    }

    /**
     * Get the array representation of the notification (database).
     */
    public function toArray($notifiable): array
    {
        $bookTitle = $this->loan->copy->item->title ?? 'Unknown Book';
        $barcode = $this->loan->copy->barcode ?? 'N/A';
        $borrowerName = $this->loan->borrower_name ?? 'Unknown';

        return [
            'type' => 'loan_overdue',
            'loan_id' => $this->loan->id,
            'borrower_type' => $this->loan->borrower_type,
            'borrower_id' => $this->loan->borrower_id,
            'borrower_name' => $borrowerName,
            'barcode' => $barcode,
            'book_title' => $bookTitle,
            'due_date' => $this->loan->due_date->toDateString(),
            'days_overdue' => $this->daysOverdue,
            'message' => "Book OVERDUE by {$this->daysOverdue} day(s)",
            'link_url' => url('/admin/library/loans'),
            'icon' => 'exclamation-triangle',
            'color' => 'danger'
        ];
    }
}
