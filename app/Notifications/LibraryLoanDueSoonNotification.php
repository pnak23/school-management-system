<?php

namespace App\Notifications;

use App\Models\LibraryLoan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LibraryLoanDueSoonNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $loan;
    protected $daysLeft;

    /**
     * Create a new notification instance.
     */
    public function __construct(LibraryLoan $loan, int $daysLeft)
    {
        $this->loan = $loan;
        $this->daysLeft = $daysLeft;
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
        
        $subject = $this->daysLeft === 0 
            ? "Library Book Due Today" 
            : "Library Book Due in {$this->daysLeft} Day(s)";
        
        $greeting = $this->daysLeft === 0
            ? "The borrowed book is due today!"
            : "The borrowed book is due in {$this->daysLeft} day(s).";

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($greeting)
            ->line('**Book Details:**')
            ->line("• Title: {$bookTitle}")
            ->line("• Barcode: {$barcode}")
            ->line("• Borrower: {$borrowerName}")
            ->line("• Due Date: " . $this->loan->due_date->format('Y-m-d H:i'))
            ->action('View Loan Details', url('/admin/library/loans'))
            ->line('Please return the book on time to avoid fines.')
            ->line('Thank you!');
    }

    /**
     * Get the array representation of the notification (database).
     */
    public function toArray($notifiable): array
    {
        $bookTitle = $this->loan->copy->item->title ?? 'Unknown Book';
        $barcode = $this->loan->copy->barcode ?? 'N/A';
        $borrowerName = $this->loan->borrower_name ?? 'Unknown';
        
        $message = $this->daysLeft === 0
            ? "Book due TODAY"
            : "Book due in {$this->daysLeft} day(s)";

        return [
            'type' => 'loan_due_soon',
            'loan_id' => $this->loan->id,
            'borrower_type' => $this->loan->borrower_type,
            'borrower_id' => $this->loan->borrower_id,
            'borrower_name' => $borrowerName,
            'barcode' => $barcode,
            'book_title' => $bookTitle,
            'due_date' => $this->loan->due_date->toDateString(),
            'days_left' => $this->daysLeft,
            'message' => $message,
            'link_url' => url('/admin/library/loans'),
            'icon' => 'warning',
            'color' => $this->daysLeft === 0 ? 'danger' : 'warning'
        ];
    }
}
