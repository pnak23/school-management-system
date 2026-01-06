<?php

namespace App\Http\Controllers\Admin\QRLibrary;

use App\Http\Controllers\Controller;
use App\Models\LibraryVisit;
use App\Models\LibraryReadingLog;
use App\Models\LibraryItem;
use App\Models\LibraryCopy;
use App\Models\LibraryLoan;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Staff;
use App\Models\LibraryGuest;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * QR Library Visit Controller
 * 
 * Handles QR-based self-service check-in/check-out for library visitors.
 * This controller works ALONGSIDE the existing LibraryVisitController
 * without modifying any existing CRUD functionality.
 * 
 * Key Features:
 * - Self-service check-in/check-out via QR code
 * - Auto-detect if user has open session
 * - Auto-determine session (morning/afternoon/evening) by time
 * - Mobile-first UI
 * - No staff_id required (self-service)
 */
class QRLibraryVisitController extends Controller
{
    use LogsActivity;
    /**
     * Show QR page (main entry point after scanning QR code)
     * 
     * This page auto-detects:
     * 1. If user has account and is logged in → Show check-in/out form
     * 2. If user not logged in → Redirect to login (will return here after)
     * 3. If user has open visit → Show check-out form
     * 4. If user has no open visit → Show check-in form
     */
    public function showQRPage()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            // User not logged in → Redirect to login page
            // After login, they will be redirected back here
            return redirect()->route('login')->with('intended', route('qr.library.visits.index'));
        }

        $user = Auth::user();
        
        // Check if user has an open visit today
        $openVisit = $this->detectOpenVisit($user->id);
        
        // Determine current session by time
        $currentSession = $this->determineSessionByTime();
        
        // Prepare data for view
        $data = [
            'user' => $user,
            'openVisit' => $openVisit,
            'hasOpenVisit' => !is_null($openVisit),
            'currentSession' => $currentSession,
            'currentTime' => Carbon::now()->format('H:i'),
        ];
        
        return view('admin.QRlibrary.QRvisits.index', $data);
    }

    /**
     * Handle self-service check-in
     * 
     * Business Rules:
     * - User must be logged in
     * - Cannot check-in if already has open visit today
     * - Auto-fill: visit_date=today, check_in_time=now()
     * - Auto-detect session by current time
     * - checked_in_by_staff_id = NULL (self-service)
     * - Optional: Auto-create reading log if start_reading_now=1
     */
    public function handleCheckIn(Request $request)
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to check-in.'
            ], 401);
        }

        $user = Auth::user();

        // Base validation rules
        $rules = [
            'purpose' => 'required|in:read,study,borrow,return,other',
            'note' => 'nullable|string|max:500',
            'start_reading_now' => 'nullable|boolean', // NEW: Optional quick-start
        ];

        // NEW: If starting reading, require book selection
        if ($request->input('start_reading_now')) {
            $rules['library_item_id'] = 'required|exists:library_items,id';
            $rules['copy_id'] = 'nullable|exists:library_copies,id';
        }

        // Validate request
        $validated = $request->validate($rules, [
            'purpose.required' => 'Please select a purpose for your visit.',
            'library_item_id.required' => 'Please select a book to start reading.',
        ]);

        try {
            DB::beginTransaction();

            // Check for duplicate open session today
            $existingOpen = $this->detectOpenVisit($user->id);
            
            if ($existingOpen) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an open visit today. Please check-out first!',
                    'data' => [
                        'existing_session' => [
                            'check_in_time' => $existingOpen->check_in_time->format('H:i'),
                            'session' => ucfirst($existingOpen->session),
                            'purpose' => ucfirst($existingOpen->purpose),
                            'duration' => $existingOpen->duration
                        ]
                    ]
                ], 422);
            }

            // Auto-determine session by current time
            $session = $this->determineSessionByTime();

            // Create new visit record
            $visit = LibraryVisit::create([
                'user_id' => $user->id,
                'guest_id' => null, // QR is for registered users only
                'visit_date' => Carbon::today(),
                'check_in_time' => Carbon::now(),
                'check_out_time' => null,
                'session' => $session,
                'purpose' => $validated['purpose'],
                'note' => $validated['note'] ?? null,
                'checked_in_by_staff_id' => null, // Self-service (no staff)
                'checked_out_by_staff_id' => null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'is_active' => true,
            ]);

            // NEW: Auto-create reading log if requested
            $readingLog = null;
            $bookTitle = null;
            
            if ($request->input('start_reading_now') && isset($validated['library_item_id'])) {
                // Get book title
                $item = LibraryItem::find($validated['library_item_id']);
                $bookTitle = $item ? $item->title : 'Selected book';
                
                // Build note
                $readingNote = 'Auto-start from QR self-check-in';
                if (!empty($validated['note'])) {
                    $readingNote = $validated['note'] . ' | ' . $readingNote;
                }
                
                // Create reading log
                $readingLog = LibraryReadingLog::create([
                    'visit_id' => $visit->id,
                    'library_item_id' => $validated['library_item_id'],
                    'copy_id' => $validated['copy_id'] ?? null,
                    'start_time' => Carbon::now(),
                    'end_time' => null,
                    'reading_type' => 'in_library',
                    'note' => $readingNote,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                    'is_active' => true,
                ]);
            }

            // Log activity (QR self-service check-in)
            $this->logActivity(
                "QR self-service check-in: {$user->name} ({$user->email}) - {$session} session" . ($bookTitle ? " (Reading: {$bookTitle})" : ""),
                $visit,
                [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'visit_date' => $visit->visit_date,
                    'check_in_time' => $visit->check_in_time,
                    'session' => $visit->session,
                    'purpose' => $visit->purpose,
                    'method' => 'QR self-service',
                    'reading_started' => (bool)$readingLog,
                    'book_title' => $bookTitle,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ],
                'library_visits'
            );

            DB::commit();

            // Build success message
            $message = 'Welcome! You have successfully checked in.';
            if ($readingLog) {
                $message .= ' Reading started for: ' . $bookTitle;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'visit_id' => $visit->id,
                    'check_in_time' => $visit->check_in_time->format('H:i'),
                    'session' => ucfirst($visit->session),
                    'reading_log' => $readingLog,
                    'reading_started' => (bool)$readingLog,
                    'book_title' => $bookTitle,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('QR Check-in Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Check-in failed. Please try again or contact library staff.'
            ], 500);
        }
    }

    /**
     * Handle self-service check-out
     * 
     * Business Rules:
     * - User must be logged in
     * - Must have an open visit to check-out
     * - Auto-fill: check_out_time=now()
     * - checked_out_by_staff_id = NULL (self-service)
     */
    public function handleCheckOut(Request $request)
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to check-out.'
            ], 401);
        }

        $user = Auth::user();

        try {
            DB::beginTransaction();

            // Find open visit for this user today
            $openVisit = $this->detectOpenVisit($user->id);

            if (!$openVisit) {
                return response()->json([
                    'success' => false,
                    'message' => 'No open visit found. You may have already checked out or never checked in.'
                ], 422);
            }

            // Update visit with check-out time
            $openVisit->check_out_time = Carbon::now();
            $openVisit->checked_out_by_staff_id = null; // Self-service (no staff)
            $openVisit->updated_by = $user->id;
            $openVisit->save();

            // Log activity (QR self-service check-out)
            $this->logActivity(
                "QR self-service check-out: {$user->name} ({$user->email}) - Visit #{$openVisit->id}",
                $openVisit,
                [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'visit_date' => $openVisit->visit_date,
                    'check_in_time' => $openVisit->check_in_time,
                    'check_out_time' => $openVisit->check_out_time,
                    'session' => $openVisit->session,
                    'duration' => $openVisit->duration,
                    'method' => 'QR self-service',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ],
                'library_visits'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Thank you! You have successfully checked out.',
                'data' => [
                    'check_out_time' => $openVisit->check_out_time->format('H:i'),
                    'duration' => $openVisit->duration,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('QR Check-out Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Check-out failed. Please try again or contact library staff.'
            ], 500);
        }
    }

    /**
     * Detect if user has an open visit today
     * 
     * @param int $userId
     * @return LibraryVisit|null
     */
    private function detectOpenVisit($userId)
    {
        return LibraryVisit::where('user_id', $userId)
            ->whereDate('visit_date', Carbon::today())
            ->whereNull('check_out_time')
            ->where('is_active', true)
            ->orderBy('check_in_time', 'desc')
            ->first();
    }

    /**
     * Determine session (morning/afternoon/evening) based on current time
     * 
     * Time Ranges:
     * - Morning: 06:00 - 11:59
     * - Afternoon: 12:00 - 17:59
     * - Evening: 18:00 - 23:59 (and 00:00 - 05:59)
     * 
     * @return string
     */
    private function determineSessionByTime()
    {
        $currentHour = Carbon::now()->hour;

        if ($currentHour >= 6 && $currentHour < 12) {
            return 'morning';
        } elseif ($currentHour >= 12 && $currentHour < 18) {
            return 'afternoon';
        } else {
            return 'evening';
        }
    }

    /**
     * Get visitor statistics for display (optional enhancement)
     * 
     * @return array
     */
    public function getVisitorStats()
    {
        if (!Auth::check()) {
            return [];
        }

        $userId = Auth::id();

        return [
            'total_visits' => LibraryVisit::where('user_id', $userId)
                ->where('is_active', true)
                ->count(),
            'visits_this_month' => LibraryVisit::where('user_id', $userId)
                ->where('is_active', true)
                ->whereMonth('visit_date', Carbon::now()->month)
                ->whereYear('visit_date', Carbon::now()->year)
                ->count(),
        ];
    }

    /**
     * Show QR Code Generator Page (for admin/staff to print QR code)
     * 
     * This page displays a QR code that visitors can scan to check-in/out.
     * Only accessible by admin, manager, staff (not principal).
     * 
     * @return \Illuminate\View\View
     */
    public function showQRGenerator()
    {
        // Check permissions (admin, manager, staff only)
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['admin', 'manager', 'staff'])) {
            abort(403, 'You do not have permission to access QR Code Generator.');
        }

        // Generate the URL that the QR code will point to
        $qrUrl = route('qr.library.visits.index');

        return view('admin.QRlibrary.QRvisits.qr_generator', [
            'qrUrl' => $qrUrl,
        ]);
    }

    /**
     * Show QR Code Generator for "Start Reading" (NEW)
     * 
     * This generates a SEPARATE QR code specifically for starting reading logs.
     * Users scan this QR AFTER they've already checked in.
     * 
     * Use case:
     * - User has already checked in
     * - Scans "Start Reading" QR code
     * - System auto-detects their open visit
     * - User selects book
     * - Reading log created immediately
     * 
     * @return \Illuminate\View\View
     */
    public function showStartReadingQRGenerator()
    {
        // Check permissions (admin, manager, staff only)
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['admin', 'manager', 'staff'])) {
            abort(403, 'You do not have permission to access QR Code Generator.');
        }

        // Generate the URL that the QR code will point to
        $qrUrl = route('qr.library.start-reading.form');

        return view('admin.QRlibrary.start_reading.qr_generator', [
            'qrUrl' => $qrUrl,
            'title' => 'Start Reading QR Generator',
            'description' => 'Scan this QR code to quickly start a reading log. You must be checked-in first.',
        ]);
    }

    /**
     * Show "Start Reading" form after scanning QR code (NEW)
     * 
     * This page:
     * 1. Checks if user is logged in
     * 2. Detects their open visit today
     * 3. Shows simple form to select book and start reading
     * 
     * @return \Illuminate\View\View
     */
    public function showStartReadingForm(Request $request)
    {
        // Must be logged in
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('intended', route('qr.library.start-reading.form'))
                ->with('info', 'Please login to start reading.');
        }

        $user = Auth::user();

        // Find open visit for today
        $openVisit = $this->detectOpenVisit($user->id);

        // Get library_item_id from query parameter (if passed from book details modal)
        $preselectedItemId = $request->query('library_item_id');

        // Prepare data
        $data = [
            'user' => $user,
            'openVisit' => $openVisit,
            'hasOpenVisit' => !is_null($openVisit),
            'currentTime' => Carbon::now()->format('H:i'),
            'currentSession' => $this->determineSessionByTime(),
            'preselectedItemId' => $preselectedItemId, // Pass to view for auto-selection
        ];

        return view('admin.QRlibrary.start_reading.form', $data);
    }

    /**
     * Handle "Start Reading" form submission (NEW)
     * 
     * Business Rules:
     * - User must be logged in
     * - User must have an open visit today
     * - Auto-fill: start_time = now(), visit_id from open visit
     * - User selects: library_item_id, optional copy_id, note
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleStartReading(Request $request)
    {
        // Must be logged in
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to start reading.'
            ], 401);
        }

        $user = Auth::user();

        // Validate request
        $validated = $request->validate([
            'library_item_id' => 'required|exists:library_items,id',
            'copy_id' => 'nullable|exists:library_copies,id',
            'note' => 'nullable|string|max:500',
        ], [
            'library_item_id.required' => 'Please select a book to read.',
            'library_item_id.exists' => 'Selected book does not exist.',
        ]);

        try {
            DB::beginTransaction();

            // Find open visit for today
            $openVisit = $this->detectOpenVisit($user->id);

            if (!$openVisit) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have an open visit today. Please check-in first!',
                    'action' => 'check-in',
                    'check_in_url' => route('qr.library.visits.index')
                ], 422);
            }

            // Check if user already has a running reading log for this visit
            $runningLog = LibraryReadingLog::where('visit_id', $openVisit->id)
                ->whereNotNull('start_time')
                ->whereNull('end_time')
                ->where('is_active', true)
                ->first();

            if ($runningLog) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active reading log. Please stop it first before starting a new one.',
                    'data' => [
                        'running_log' => [
                            'book' => $runningLog->item->title ?? 'Unknown',
                            'started_at' => $runningLog->start_time->format('H:i'),
                        ]
                    ]
                ], 422);
            }

            // Get book title for response
            $item = LibraryItem::find($validated['library_item_id']);
            $bookTitle = $item ? $item->title : 'Selected book';

            // Create reading log
            $readingLog = LibraryReadingLog::create([
                'visit_id' => $openVisit->id,
                'library_item_id' => $validated['library_item_id'],
                'copy_id' => $validated['copy_id'] ?? null,
                'start_time' => Carbon::now(),
                'end_time' => null,
                'reading_type' => 'in_library',
                'note' => $validated['note'] ?? 'Started via QR code scan',
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'is_active' => true,
            ]);

            // Log activity (QR start reading)
            $this->logActivity(
                "QR start reading: {$user->name} ({$user->email}) - '{$bookTitle}'",
                $readingLog,
                [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'visit_id' => $openVisit->id,
                    'library_item_id' => $validated['library_item_id'],
                    'book_title' => $bookTitle,
                    'barcode' => $readingLog->copy ? $readingLog->copy->barcode : null,
                    'start_time' => $readingLog->start_time->toDateTimeString(),
                    'method' => 'QR self-service',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ],
                'library_reading_logs'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reading started successfully! Enjoy your book.',
                'data' => [
                    'reading_log_id' => $readingLog->id,
                    'book_title' => $bookTitle,
                    'start_time' => $readingLog->start_time->format('H:i'),
                    'visit_session' => ucfirst($openVisit->session),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('QR Start Reading Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to start reading. Please try again or contact library staff.'
            ], 500);
        }
    }

    /**
     * Show user's own reading logs (NEW)
     * 
     * This page shows the current logged-in user's reading logs.
     * User can:
     * - View their reading logs
     * - Stop active reading log
     * - See details
     * 
     * @return \Illuminate\View\View
     */
    public function showMyReadingLogs()
    {
        // Must be logged in
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('intended', route('qr.library.my-reading-logs'))
                ->with('info', 'Please login to view your reading logs.');
        }

        $user = Auth::user();

        // Get user's reading logs (with relationships)
        $readingLogs = LibraryReadingLog::with(['visit', 'item', 'copy'])
            ->whereHas('visit', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Check if user has open visit
        $openVisit = $this->detectOpenVisit($user->id);

        // Check if user has active reading log
        $activeLog = null;
        if ($openVisit) {
            $activeLog = LibraryReadingLog::where('visit_id', $openVisit->id)
                ->whereNotNull('start_time')
                ->whereNull('end_time')
                ->where('is_active', true)
                ->with(['item', 'copy'])
                ->first();
        }

        return view('admin.QRlibrary.my_reading_logs.index', [
            'user' => $user,
            'readingLogs' => $readingLogs,
            'openVisit' => $openVisit,
            'activeLog' => $activeLog,
            'hasActiveLog' => !is_null($activeLog),
        ]);
    }

    /**
     * Stop user's active reading log (NEW)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function stopMyReading(Request $request)
    {
        // Must be logged in
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in.'
            ], 401);
        }

        $user = Auth::user();
        $logId = $request->input('log_id');

        try {
            DB::beginTransaction();

            // Find the reading log
            $log = LibraryReadingLog::with(['visit', 'item'])
                ->find($logId);

            if (!$log) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reading log not found.'
                ], 404);
            }

            // Verify this log belongs to the current user
            if ($log->visit->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This reading log does not belong to you.'
                ], 403);
            }

            // Check if already stopped
            if ($log->end_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'This reading log is already stopped.'
                ], 422);
            }

            // Stop reading
            $log->update([
                'end_time' => Carbon::now(),
                'updated_by' => $user->id,
            ]);

            $log->refresh();
            
            // Get book title for logging
            $bookTitle = $log->item ? $log->item->title : 'Unknown';

            // Log activity (QR stop reading)
            $this->logActivity(
                "QR stop reading: {$user->name} ({$user->email}) - '{$bookTitle}' (Duration: {$log->duration})",
                $log,
                [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'visit_id' => $log->visit_id,
                    'library_item_id' => $log->library_item_id,
                    'book_title' => $bookTitle,
                    'start_time' => $log->start_time ? $log->start_time->toDateTimeString() : null,
                    'end_time' => $log->end_time->toDateTimeString(),
                    'duration' => $log->duration,
                    'minutes_read' => $log->minutes_read,
                    'method' => 'QR self-service',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ],
                'library_reading_logs'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reading stopped successfully!',
                'data' => [
                    'end_time' => $log->end_time->format('H:i'),
                    'duration' => $log->duration,
                    'minutes' => $log->minutes_read,
                    'book_title' => $log->item->title ?? 'Unknown',
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error stopping reading log: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to stop reading. Please try again.'
            ], 500);
        }
    }

    /**
     * Get reading log details (NEW)
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function showMyReadingLogDetail($id)
    {
        // Must be logged in
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in.'
            ], 401);
        }

        $user = Auth::user();

        // Find the reading log with relationships
        $log = LibraryReadingLog::with(['visit', 'item', 'copy', 'creator'])
            ->find($id);

        if (!$log) {
            return response()->json([
                'success' => false,
                'message' => 'Reading log not found.'
            ], 404);
        }

        // Verify this log belongs to the current user
        if ($log->visit->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'This reading log does not belong to you.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $log->id,
                'book_title' => $log->item->title ?? 'N/A',
                'book_isbn' => $log->item->isbn ?? 'N/A',
                'copy_barcode' => $log->copy->barcode ?? 'N/A',
                'start_time' => $log->start_time ? $log->start_time->format('Y-m-d H:i') : 'N/A',
                'end_time' => $log->end_time ? $log->end_time->format('Y-m-d H:i') : 'Still reading...',
                'duration' => $log->duration ?? 'N/A',
                'minutes' => $log->minutes_read ?? 0,
                'reading_type' => ucfirst($log->reading_type),
                'note' => $log->note ?? 'No notes',
                'visit_date' => $log->visit->visit_date->format('Y-m-d'),
                'visit_session' => ucfirst($log->visit->session),
                'is_active' => $log->end_time ? false : true,
            ]
        ]);
    }

    /**
     * Detect borrower type and ID from logged-in user
     * 
     * @param User $user
     * @return array|null ['type' => 'student|teacher|staff|guest', 'id' => int, 'name' => string] or null
     */
    private function detectBorrower(User $user)
    {
        // Check if user is a student
        $student = Student::where('user_id', $user->id)->where('is_active', 1)->first();
        if ($student) {
            return [
                'type' => 'student',
                'id' => $student->id,
                'name' => $student->english_name ?? $student->khmer_name ?? $user->name,
                'identifier' => $student->code ?? ''
            ];
        }

        // Check if user is a teacher
        $teacher = Teacher::where('user_id', $user->id)->where('is_active', 1)->first();
        if ($teacher) {
            return [
                'type' => 'teacher',
                'id' => $teacher->id,
                'name' => $teacher->english_name ?? $teacher->khmer_name ?? $user->name,
                'identifier' => $teacher->teacher_code ?? ''
            ];
        }

        // Check if user is a staff
        $staff = Staff::where('user_id', $user->id)->where('is_active', 1)->first();
        if ($staff) {
            return [
                'type' => 'staff',
                'id' => $staff->id,
                'name' => $staff->english_name ?? $staff->khmer_name ?? $user->name,
                'identifier' => $staff->staff_code ?? ''
            ];
        }

        // Check if user is a guest (guests might not have user_id, so we'll skip for now)
        // For guests, they would need to register separately

        return null;
    }

    /**
     * Show loan request form (QR-based self-service)
     * 
     * Users can request to borrow a book. The request will be pending until approved by staff.
     */
    public function showLoanRequestForm(Request $request)
    {
        // Must be logged in
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('intended', route('qr.library.loan-request.form'))
                ->with('info', 'Please login to request a book loan.');
        }

        $user = Auth::user();

        // Detect borrower
        $borrower = $this->detectBorrower($user);

        if (!$borrower) {
            return view('admin.QRlibrary.loan_request.error', [
                'message' => 'Your account is not linked to a student, teacher, or staff record. Please contact the administrator.',
                'user' => $user
            ]);
        }

        // Get library_item_id from query parameter (if passed from book details)
        $preselectedItemId = $request->query('library_item_id');

        // Prepare data
        $data = [
            'user' => $user,
            'borrower' => $borrower,
            'currentTime' => Carbon::now()->format('H:i'),
            'currentDate' => Carbon::now()->format('Y-m-d'),
            'preselectedItemId' => $preselectedItemId,
        ];

        return view('admin.QRlibrary.loan_request.form', $data);
    }

    /**
     * Handle loan request submission
     * 
     * Creates a loan request with status 'requested' (pending approval)
     */
    public function handleLoanRequest(Request $request)
    {
        // Must be logged in
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to request a book loan.'
            ], 401);
        }

        $user = Auth::user();

        // Detect borrower
        $borrower = $this->detectBorrower($user);
        if (!$borrower) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not linked to a borrower record. Please contact the administrator.'
            ], 422);
        }

        // Validate request
        $validated = $request->validate([
            'library_item_id' => 'required|exists:library_items,id',
            'copy_id' => 'nullable|exists:library_copies,id',
            'due_date' => 'required|date|after_or_equal:today',
            'note' => 'nullable|string|max:1000',
        ], [
            'library_item_id.required' => 'Please select a book to borrow.',
            'library_item_id.exists' => 'Selected book does not exist.',
            'due_date.required' => 'Please select a due date.',
            'due_date.after_or_equal' => 'Due date must be today or later.',
        ]);

        try {
            DB::beginTransaction();

            // Get the book/item
            $item = LibraryItem::findOrFail($validated['library_item_id']);
            $bookTitle = $item->title;

            // If copy_id is provided, use it; otherwise, find an available copy
            $copy = null;
            if ($validated['copy_id']) {
                $copy = LibraryCopy::where('id', $validated['copy_id'])
                    ->where('library_item_id', $item->id)
                    ->where('is_active', true)
                    ->first();
            } else {
                // Find first available copy
                $copy = LibraryCopy::where('library_item_id', $item->id)
                    ->where('is_active', true)
                    ->where('status', 'available')
                    ->first();
            }

            if (!$copy) {
                return response()->json([
                    'success' => false,
                    'message' => 'No available copy found for this book. Please select a specific copy or try another book.'
                ], 422);
            }

            // Check if copy is available
            if ($copy->status !== 'available') {
                return response()->json([
                    'success' => false,
                    'message' => 'This copy is not available. Current status: ' . $copy->status
                ], 422);
            }

            // Create loan request (status: 'requested' - pending approval)
            $loan = LibraryLoan::create([
                'borrower_type' => $borrower['type'],
                'borrower_id' => $borrower['id'],
                'library_copy_id' => $copy->id,
                'borrowed_at' => Carbon::now()->toDateString(),
                'due_date' => $validated['due_date'],
                'returned_at' => null,
                'processed_by_staff_id' => null, // Will be set when approved by staff
                'received_by_staff_id' => null,
                'status' => 'requested', // Pending approval
                'note' => $validated['note'] ?? 'Requested via QR self-service'
            ]);

            // Log activity
            $this->logActivity(
                "Loan requested: {$bookTitle} (Barcode: {$copy->barcode}) by {$borrower['type']} #{$borrower['id']} ({$borrower['name']})",
                $loan,
                [
                    'borrower_type' => $borrower['type'],
                    'borrower_id' => $borrower['id'],
                    'borrower_name' => $borrower['name'],
                    'barcode' => $copy->barcode,
                    'book_title' => $bookTitle,
                    'due_date' => $validated['due_date'],
                    'status' => 'requested',
                    'method' => 'QR self-service',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ],
                'library_loans'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Loan request submitted successfully! Your request is pending approval by library staff.',
                'data' => [
                    'loan_id' => $loan->id,
                    'book_title' => $bookTitle,
                    'barcode' => $copy->barcode,
                    'due_date' => $loan->due_date->format('Y-m-d'),
                    'status' => 'requested',
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating loan request: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit loan request. Please try again.'
            ], 500);
        }
    }
}

