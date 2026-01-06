<?php

namespace App\Console\Commands;

use App\Models\LibraryLoan;
use App\Models\User;
use App\Notifications\LibraryLoanDueSoonNotification;
use App\Notifications\LibraryLoanOverdueNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotifyLibraryLoanDueDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'library:notify-due-dates 
                            {--due-soon-days=3 : Number of days before due date to send "due soon" notifications}
                            {--dry-run : Run without actually sending notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications for library loans that are due soon or overdue';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting library loan due date notifications...');
        $dryRun = $this->option('dry-run');
        $dueSoonDays = (int) $this->option('due-soon-days');

        $dueSoonCount = 0;
        $overdueCount = 0;

        // Get librarians (users with admin, manager, or staff roles)
        $librarians = $this->getLibrarians();
        $this->info("Found {$librarians->count()} librarian(s) to notify.");

        // Find active borrowed loans (not returned)
        $activeLoans = LibraryLoan::with(['copy.item'])
            ->where('status', 'borrowed')
            ->whereNull('returned_at')
            ->whereNotNull('due_date')
            ->get();

        $this->info("Found {$activeLoans->count()} active loan(s) to check.");

        foreach ($activeLoans as $loan) {
            $dueDate = Carbon::parse($loan->due_date);
            $today = Carbon::today();
            $daysUntilDue = $today->diffInDays($dueDate, false);

            // Check if overdue
            if ($daysUntilDue < 0) {
                $daysOverdue = abs($daysUntilDue);
                
                if ($this->shouldNotifyOverdue($loan, $daysOverdue)) {
                    $this->info("  📛 Loan #{$loan->id}: OVERDUE by {$daysOverdue} day(s)");
                    
                    if (!$dryRun) {
                        $this->sendOverdueNotification($loan, $daysOverdue, $librarians);
                        $this->markOverdueNotified($loan, $daysOverdue);
                    }
                    
                    $overdueCount++;
                }
            }
            // Check if due soon (within configured days)
            elseif ($daysUntilDue >= 0 && $daysUntilDue <= $dueSoonDays) {
                if ($this->shouldNotifyDueSoon($loan, $daysUntilDue)) {
                    $message = $daysUntilDue === 0 
                        ? "DUE TODAY" 
                        : "due in {$daysUntilDue} day(s)";
                    
                    $this->info("  ⚠️  Loan #{$loan->id}: {$message}");
                    
                    if (!$dryRun) {
                        $this->sendDueSoonNotification($loan, $daysUntilDue, $librarians);
                        $this->markDueSoonNotified($loan, $daysUntilDue);
                    }
                    
                    $dueSoonCount++;
                }
            }
        }

        if ($dryRun) {
            $this->warn('DRY RUN: No notifications were actually sent.');
        }

        $this->info("✅ Done! Sent {$dueSoonCount} due-soon and {$overdueCount} overdue notifications.");
        
        return Command::SUCCESS;
    }

    /**
     * Get all librarians (admin, manager, staff)
     */
    protected function getLibrarians()
    {
        return User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'manager', 'staff']);
        })->where('is_active', true)->get();
    }

    /**
     * Check if we should notify for due soon (prevent duplicates)
     */
    protected function shouldNotifyDueSoon(LibraryLoan $loan, int $daysLeft): bool
    {
        $cacheKey = "loan_due_soon_notified_{$loan->id}_{$loan->due_date->toDateString()}_{$daysLeft}";
        
        // Check if already notified for this specific day count
        return !Cache::has($cacheKey);
    }

    /**
     * Mark loan as notified for due soon
     */
    protected function markDueSoonNotified(LibraryLoan $loan, int $daysLeft): void
    {
        $cacheKey = "loan_due_soon_notified_{$loan->id}_{$loan->due_date->toDateString()}_{$daysLeft}";
        Cache::put($cacheKey, true, now()->addDays(7)); // 7 days TTL
    }

    /**
     * Check if we should notify for overdue (prevent daily spam)
     */
    protected function shouldNotifyOverdue(LibraryLoan $loan, int $daysOverdue): bool
    {
        // Notify on day 1, 3, 7, then weekly (7, 14, 21, 28...)
        $notifyOnDays = [1, 3, 7];
        
        // Add weekly milestones
        for ($day = 7; $day <= 90; $day += 7) {
            $notifyOnDays[] = $day;
        }

        if (!in_array($daysOverdue, $notifyOnDays)) {
            return false;
        }

        $cacheKey = "loan_overdue_notified_{$loan->id}_{$daysOverdue}";
        
        // Check if already notified for this overdue day count
        return !Cache::has($cacheKey);
    }

    /**
     * Mark loan as notified for overdue
     */
    protected function markOverdueNotified(LibraryLoan $loan, int $daysOverdue): void
    {
        $cacheKey = "loan_overdue_notified_{$loan->id}_{$daysOverdue}";
        Cache::put($cacheKey, true, now()->addDays(7)); // 7 days TTL
    }

    /**
     * Send due soon notification
     */
    protected function sendDueSoonNotification(LibraryLoan $loan, int $daysLeft, $librarians): void
    {
        try {
            $notification = new LibraryLoanDueSoonNotification($loan, $daysLeft);

            // Notify all librarians
            Notification::send($librarians, $notification);
            
            $this->line("    ✉️  Notified {$librarians->count()} librarian(s)");

            // Try to notify the borrower
            $borrowerUser = $loan->borrowerUser();
            if ($borrowerUser) {
                $borrowerUser->notify($notification);
                $this->line("    ✉️  Notified borrower: {$borrowerUser->name}");
            } else {
                $this->line("    ⚠️  No user account found for borrower");
            }

            Log::info("Due soon notification sent for loan #{$loan->id}");
        } catch (\Exception $e) {
            Log::error("Failed to send due soon notification for loan #{$loan->id}: " . $e->getMessage());
            $this->error("    ❌ Error: " . $e->getMessage());
        }
    }

    /**
     * Send overdue notification
     */
    protected function sendOverdueNotification(LibraryLoan $loan, int $daysOverdue, $librarians): void
    {
        try {
            $notification = new LibraryLoanOverdueNotification($loan, $daysOverdue);

            // Notify all librarians
            Notification::send($librarians, $notification);
            
            $this->line("    ✉️  Notified {$librarians->count()} librarian(s)");

            // Try to notify the borrower
            $borrowerUser = $loan->borrowerUser();
            if ($borrowerUser) {
                $borrowerUser->notify($notification);
                $this->line("    ✉️  Notified borrower: {$borrowerUser->name}");
            } else {
                $this->line("    ⚠️  No user account found for borrower");
            }

            Log::info("Overdue notification sent for loan #{$loan->id}, {$daysOverdue} days overdue");
        } catch (\Exception $e) {
            Log::error("Failed to send overdue notification for loan #{$loan->id}: " . $e->getMessage());
            $this->error("    ❌ Error: " . $e->getMessage());
        }
    }
}
