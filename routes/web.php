<?php

use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\EmploymentTypeController;
use App\Http\Controllers\Admin\Library\LibraryAuthorController;
use App\Http\Controllers\Admin\Library\LibraryCategoryController;
use App\Http\Controllers\Admin\Library\LibraryCopyController;
use App\Http\Controllers\Admin\Library\LibraryFineController;
use App\Http\Controllers\Admin\Library\LibraryGuestController;
use App\Http\Controllers\Admin\Library\LibraryItemController;
use App\Http\Controllers\Admin\Library\LibraryItemAuthorController;
use App\Http\Controllers\Admin\Library\LibraryLoanController;
use App\Http\Controllers\Admin\Library\LibraryPublisherController;
use App\Http\Controllers\Admin\Library\LibraryShelfController;
use App\Http\Controllers\Admin\Library\LibraryVisitController;
use App\Http\Controllers\Admin\Library\LibraryReadingLogController;
use App\Http\Controllers\Admin\Library\LibraryReadingDashboardController;
use App\Http\Controllers\Admin\Library\LibraryStockTakingController;
use App\Http\Controllers\Admin\Library\LibraryStockTakingItemController;
use App\Http\Controllers\Admin\Library\LibraryReservationController;
use App\Http\Controllers\Admin\Library\Reports\OverdueLoansReportController;
use App\Http\Controllers\Admin\Library\Reports\ActiveLoansReportController;
use App\Http\Controllers\Admin\Library\Reports\OutstandingFinesReportController;
use App\Http\Controllers\Admin\Library\Reports\CollectionSummaryReportController;
use App\Http\Controllers\Admin\Library\Reports\DailyVisitReportController;
use App\Http\Controllers\Admin\Library\Reports\LibraryBooksReportController;
use App\Http\Controllers\Admin\QRLibrary\QRLibraryVisitController;
use App\Http\Controllers\Chart\AnalyticsChartController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Backup_Modern_Controller;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SampleUserTableController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentControllerDataTable;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CommuneController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\UserPageController;
use App\Http\Controllers\VillageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Test jQuery Loading (for debugging)
Route::get('/test-jquery', function () {
    return view('test-jquery');
})->middleware('auth');

Route::get('/vite-test', function () {
    return view('vite-test');
});

Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'km'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('lang.switch');

// Public Pages
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'contactSubmit'])->name('contact.submit');

// User Page (for students, teachers, staff, guests)
Route::get('/user-page', [UserPageController::class, 'index'])->name('user_page.index');

Route::get('/dashboard', [AnalyticsChartController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// Home page (after login)
Route::get('/home', function () {
    return view('home');
})->middleware(['auth', 'verified'])->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Activity Logs Routes
    Route::get('/logs', [ActivityLogController::class, 'index'])->name('logs.index');
    Route::get('/logs/{id}', [ActivityLogController::class, 'show'])->name('logs.show');
    Route::get('/logs-stats', [ActivityLogController::class, 'getStats'])->name('logs.stats');
    Route::match(['delete', 'post'], '/logs/delete-by-period', [ActivityLogController::class, 'deleteByPeriod'])->name('logs.delete-by-period');
    
    // Backup & Restore Routes (Admin only)
    Route::middleware('role:admin')->group(function () {
        Route::get('/backup', [Backup_Modern_Controller::class, 'index'])->name('backup.index');
        Route::post('/backup/run', [Backup_Modern_Controller::class, 'backup'])->name('backup.run');
        Route::post('/backup/run-as-sql', [Backup_Modern_Controller::class, 'backup_as_sql'])->name('backup.run_as_sql');
        Route::post('/backup/run-as-winra', [Backup_Modern_Controller::class, 'backup_as_winra'])->name('backup.run_as_winra');
        Route::post('/backup/restore', [Backup_Modern_Controller::class, 'restore'])->name('backup.restore');
        Route::post('/backup/restore-as-sql', [Backup_Modern_Controller::class, 'restore_as_sql'])->name('backup.restore_as_sql');
        Route::post('/backup/restore-as-winra', [Backup_Modern_Controller::class, 'restore_as_winra'])->name('backup.restore_as_winra');
        Route::post('/backup/clean', [Backup_Modern_Controller::class, 'clean'])->name('backup.clean');
        Route::get('/backup/progress', [Backup_Modern_Controller::class, 'getBackupProgress'])->name('backup.getBackupProgress');
        Route::post('/queue/start', [Backup_Modern_Controller::class, 'startQueueWorker'])->name('queue.start');
        Route::post('/queue/stop', [Backup_Modern_Controller::class, 'stopQueueWorker'])->name('queue.stop');
    });
});

// Admin Routes - Protected by auth and role middleware
Route::middleware(['auth', 'role:admin,manager'])->prefix('admin')->name('admin.')->group(function () {
    
    // User Management Routes - Admin only
    Route::resource('users', UserController::class);
    Route::get('users-stats', [UserController::class, 'stats'])->name('users.stats');
    Route::post('users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
    
    // Role Management Routes - Admin only
    Route::resource('roles', RoleController::class);
    Route::post('roles/{id}/restore', [RoleController::class, 'restore'])->name('roles.restore');
    Route::post('roles/{id}/assign-users', [RoleController::class, 'assignUsers'])->name('roles.assign-users');
    
    // Sample DataTable Demo
    Route::get('sample-users', [SampleUserTableController::class, 'index'])->name('sample-users.index');
    Route::get('sample-users/data', [SampleUserTableController::class, 'getData'])->name('sample-users.data');
    Route::delete('sample-users/{id}', [SampleUserTableController::class, 'destroy'])->name('sample-users.destroy');
    
    // Students DataTable (New Modular Version)
    Route::get('students-dt', [StudentControllerDataTable::class, 'index'])->name('students-dt.index');
    Route::get('students-dt/stats', [StudentControllerDataTable::class, 'stats'])->name('students-dt.stats');
    Route::get('students-dt/search-users', [StudentControllerDataTable::class, 'searchUsers'])->name('students-dt.search-users');
    Route::get('students-dt/{id}', [StudentControllerDataTable::class, 'show'])->name('students-dt.show');
    Route::post('students-dt', [StudentControllerDataTable::class, 'store'])->name('students-dt.store');
    Route::put('students-dt/{id}', [StudentControllerDataTable::class, 'update'])->name('students-dt.update');
    Route::put('students-dt/{id}/deactivate', [StudentControllerDataTable::class, 'deactivate'])->name('students-dt.deactivate');
});

// Student Management Routes - Protected by auth and role middleware
// Admin, Manager, Principal, Staff have access (read/write varies by role inside controller)
Route::middleware(['auth', 'role:admin,manager,principal,staff'])->prefix('admin')->name('admin.')->group(function () {
    // Student Location API (must be before /students/{id} to avoid route conflicts)
    Route::get('/students/search-provinces', [StudentController::class, 'searchProvinces'])->name('students.search-provinces');
    Route::get('/students/districts', [StudentController::class, 'getDistricts'])->name('students.districts');
    Route::get('/students/communes', [StudentController::class, 'getCommunes'])->name('students.communes');
    Route::get('/students/villages', [StudentController::class, 'getVillages'])->name('students.villages');
    
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/{id}', [StudentController::class, 'show'])->name('students.show');
    Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    Route::put('/students/{id}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/students/{id}', [StudentController::class, 'destroy'])->name('students.destroy');
    Route::post('/students/{id}/restore', [StudentController::class, 'restore'])->name('students.restore');
    Route::post('/students/{id}/toggle-status', [StudentController::class, 'toggleStatus'])->name('students.toggle-status');
    
    // Student Phone Management
    Route::get('/students/{id}/phones', [StudentController::class, 'getPhones'])->name('students.phones.index');
    Route::post('/students/{id}/phones', [StudentController::class, 'addPhone'])->name('students.phones.store');
    Route::put('/students/{studentId}/phones/{phoneId}', [StudentController::class, 'updatePhone'])->name('students.phones.update');
    Route::delete('/students/{studentId}/phones/{phoneId}', [StudentController::class, 'deletePhone'])->name('students.phones.destroy');
    Route::post('/students/{studentId}/phones/{phoneId}/set-primary', [StudentController::class, 'setPrimaryPhone'])->name('students.phones.set-primary');
    
    // Departments Management
    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::get('/departments/{id}', [DepartmentController::class, 'show'])->name('departments.show');
    Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
    Route::put('/departments/{id}', [DepartmentController::class, 'update'])->name('departments.update');
    Route::delete('/departments/{id}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
    Route::post('/departments/{id}/restore', [DepartmentController::class, 'restore'])->name('departments.restore');
    Route::post('/departments/{id}/toggle-status', [DepartmentController::class, 'toggleStatus'])->name('departments.toggle-status');
    
    // Employment Types Management
    Route::get('/employment-types', [EmploymentTypeController::class, 'index'])->name('employment-types.index');
    Route::get('/employment-types/{id}', [EmploymentTypeController::class, 'show'])->name('employment-types.show');
    Route::post('/employment-types', [EmploymentTypeController::class, 'store'])->name('employment-types.store');
    Route::put('/employment-types/{id}', [EmploymentTypeController::class, 'update'])->name('employment-types.update');
    Route::delete('/employment-types/{id}', [EmploymentTypeController::class, 'destroy'])->name('employment-types.destroy');
    Route::post('/employment-types/{id}/restore', [EmploymentTypeController::class, 'restore'])->name('employment-types.restore');
    Route::post('/employment-types/{id}/toggle-status', [EmploymentTypeController::class, 'toggleStatus'])->name('employment-types.toggle-status');
    
    // Positions Management
    Route::get('/positions', [PositionController::class, 'index'])->name('positions.index');
    Route::get('/positions/{id}', [PositionController::class, 'show'])->name('positions.show');
    Route::post('/positions', [PositionController::class, 'store'])->name('positions.store');
    Route::put('/positions/{id}', [PositionController::class, 'update'])->name('positions.update');
    Route::delete('/positions/{id}', [PositionController::class, 'destroy'])->name('positions.destroy');
    Route::post('/positions/{id}/restore', [PositionController::class, 'restore'])->name('positions.restore');
    Route::post('/positions/{id}/toggle-status', [PositionController::class, 'toggleStatus'])->name('positions.toggle-status');
    
    // Teachers Management
    // Teacher Location API (must be before /teachers/{id} to avoid route conflicts)
    Route::get('/teachers/search-provinces', [TeacherController::class, 'searchProvinces'])->name('teachers.search-provinces');
    Route::get('/teachers/districts', [TeacherController::class, 'getDistricts'])->name('teachers.districts');
    Route::get('/teachers/communes', [TeacherController::class, 'getCommunes'])->name('teachers.communes');
    Route::get('/teachers/villages', [TeacherController::class, 'getVillages'])->name('teachers.villages');
    Route::get('/teachers/search-users', [TeacherController::class, 'searchUsers'])->name('teachers.search-users');
    
    Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
    Route::get('/teachers/stats', [TeacherController::class, 'stats'])->name('teachers.stats');
    Route::get('/teachers/{id}', [TeacherController::class, 'show'])->name('teachers.show');
    Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
    Route::put('/teachers/{id}', [TeacherController::class, 'update'])->name('teachers.update');
    Route::delete('/teachers/{id}', [TeacherController::class, 'destroy'])->name('teachers.destroy');
    Route::post('/teachers/{id}/toggle-status', [TeacherController::class, 'toggleStatus'])->name('teachers.toggle-status');
    
    // Teacher Phone Management
    Route::get('/teachers/{id}/phones', [TeacherController::class, 'getPhones'])->name('teachers.phones.index');
    Route::post('/teachers/{id}/phones', [TeacherController::class, 'addPhone'])->name('teachers.phones.store');
    Route::put('/teachers/{teacherId}/phones/{phoneId}', [TeacherController::class, 'updatePhone'])->name('teachers.phones.update');
    Route::delete('/teachers/{teacherId}/phones/{phoneId}', [TeacherController::class, 'deletePhone'])->name('teachers.phones.destroy');
    Route::post('/teachers/{teacherId}/phones/{phoneId}/set-primary', [TeacherController::class, 'setPrimaryPhone'])->name('teachers.phones.set-primary');
    
    // Staff Management
    // Staff Location API (must be before /staff/{id} to avoid route conflicts)
    Route::get('/staff/search-provinces', [StaffController::class, 'searchProvinces'])->name('staff.search-provinces');
    Route::get('/staff/districts', [StaffController::class, 'getDistricts'])->name('staff.districts');
    Route::get('/staff/communes', [StaffController::class, 'getCommunes'])->name('staff.communes');
    Route::get('/staff/villages', [StaffController::class, 'getVillages'])->name('staff.villages');
    Route::get('/staff/search-users', [StaffController::class, 'searchUsers'])->name('staff.search-users');
    
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/stats', [StaffController::class, 'stats'])->name('staff.stats');
    Route::get('/staff/{id}', [StaffController::class, 'show'])->name('staff.show');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::put('/staff/{id}', [StaffController::class, 'update'])->name('staff.update');
    Route::delete('/staff/{id}', [StaffController::class, 'destroy'])->name('staff.destroy');
    Route::post('/staff/{id}/toggle-status', [StaffController::class, 'toggleStatus'])->name('staff.toggle-status');
    
    // Staff Phone Management
    Route::get('/staff/{id}/phones', [StaffController::class, 'getPhones'])->name('staff.phones.index');
    Route::post('/staff/{id}/phones', [StaffController::class, 'addPhone'])->name('staff.phones.store');
    Route::put('/staff/{staffId}/phones/{phoneId}', [StaffController::class, 'updatePhone'])->name('staff.phones.update');
    Route::delete('/staff/{staffId}/phones/{phoneId}', [StaffController::class, 'deletePhone'])->name('staff.phones.destroy');
    Route::post('/staff/{staffId}/phones/{phoneId}/set-primary', [StaffController::class, 'setPrimaryPhone'])->name('staff.phones.set-primary');
});

// Hard Delete Route - ADMIN ONLY
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::delete('/students/{id}/force-delete', [StudentController::class, 'forceDelete'])->name('students.force-delete');
    Route::delete('/teachers/{id}/force-delete', [TeacherController::class, 'forceDelete'])->name('teachers.force-delete');
    Route::delete('/staff/{id}/force-delete', [StaffController::class, 'forceDelete'])->name('staff.force-delete');
    Route::delete('/students-dt/{id}', [StudentControllerDataTable::class, 'destroy'])->name('students-dt.destroy');
});

// Location Management Routes - Protected by auth
Route::middleware(['auth'])->group(function () {
    // Provinces
    Route::resource('provinces', ProvinceController::class);
    
    // Districts
    Route::resource('districts', DistrictController::class);
    Route::get('api/districts/{provinceId}', [DistrictController::class, 'byProvince'])->name('api.districts');
    Route::get('api/districts/by-province/{provinceId}', [DistrictController::class, 'byProvince'])->name('districts.by-province');
    
    // Communes
    Route::resource('communes', CommuneController::class);
    Route::get('api/communes/{districtId}', [CommuneController::class, 'byDistrict'])->name('api.communes');
    Route::get('api/communes/by-district/{districtId}', [CommuneController::class, 'byDistrict'])->name('communes.by-district');
    
    // Villages
    Route::resource('villages', VillageController::class);
    Route::get('api/villages/{communeId}', [VillageController::class, 'byCommune'])->name('api.villages');
    Route::get('api/villages/by-commune/{communeId}', [VillageController::class, 'byCommune'])->name('villages.by-commune');
});

// Library Management Routes - Protected by auth
Route::middleware(['auth'])
    ->prefix('admin/library')
    ->name('admin.library.')
    ->group(function () {
        // Book Categories
        Route::get('/categories', [LibraryCategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/{id}', [LibraryCategoryController::class, 'show'])->name('categories.show');
        Route::post('/categories', [LibraryCategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{id}', [LibraryCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{id}', [LibraryCategoryController::class, 'destroy'])->name('categories.destroy');
        Route::post('/categories/{id}/toggle-status', [LibraryCategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
        Route::delete('/categories/{id}/force-delete', [LibraryCategoryController::class, 'forceDelete'])->name('categories.force-delete');
        
        // Publishers
        Route::get('/publishers', [LibraryPublisherController::class, 'index'])->name('publishers.index');
        Route::get('/publishers/{id}', [LibraryPublisherController::class, 'show'])->name('publishers.show');
        Route::post('/publishers', [LibraryPublisherController::class, 'store'])->name('publishers.store');
        Route::put('/publishers/{id}', [LibraryPublisherController::class, 'update'])->name('publishers.update');
        Route::delete('/publishers/{id}', [LibraryPublisherController::class, 'destroy'])->name('publishers.destroy');
        Route::post('/publishers/{id}/toggle-status', [LibraryPublisherController::class, 'toggleStatus'])->name('publishers.toggle-status');
        Route::delete('/publishers/{id}/force-delete', [LibraryPublisherController::class, 'forceDelete'])->name('publishers.force-delete');
        
        // Authors
        Route::get('/authors', [LibraryAuthorController::class, 'index'])->name('authors.index');
        Route::get('/authors/search', [LibraryItemAuthorController::class, 'searchAuthors'])->name('authors.search');
        Route::get('/authors/{id}', [LibraryAuthorController::class, 'show'])->name('authors.show');
        Route::post('/authors', [LibraryAuthorController::class, 'store'])->name('authors.store');
        Route::put('/authors/{id}', [LibraryAuthorController::class, 'update'])->name('authors.update');
        Route::delete('/authors/{id}', [LibraryAuthorController::class, 'destroy'])->name('authors.destroy');
        Route::post('/authors/{id}/toggle-status', [LibraryAuthorController::class, 'toggleStatus'])->name('authors.toggle-status');
        Route::delete('/authors/{id}/force-delete', [LibraryAuthorController::class, 'forceDelete'])->name('authors.force-delete');
        
        // Item-Author Pivot Management
        Route::get('/items/{item}/authors/data', [LibraryItemAuthorController::class, 'index'])->name('items.authors.data');
        Route::post('/items/{item}/authors', [LibraryItemAuthorController::class, 'store'])->name('items.authors.store');
        Route::put('/items/{item}/authors', [LibraryItemAuthorController::class, 'update'])->name('items.authors.update');
        Route::post('/items/{item}/authors/toggle', [LibraryItemAuthorController::class, 'toggleStatus'])->name('items.authors.toggle');
        Route::delete('/items/{item}/authors', [LibraryItemAuthorController::class, 'destroy'])->name('items.authors.destroy');
        
        // Shelves
        Route::get('/shelves', [LibraryShelfController::class, 'index'])->name('shelves.index');
        Route::get('/shelves/{id}', [LibraryShelfController::class, 'show'])->name('shelves.show');
        Route::post('/shelves', [LibraryShelfController::class, 'store'])->name('shelves.store');
        Route::put('/shelves/{id}', [LibraryShelfController::class, 'update'])->name('shelves.update');
        Route::delete('/shelves/{id}', [LibraryShelfController::class, 'destroy'])->name('shelves.destroy');
        Route::post('/shelves/{id}/toggle-status', [LibraryShelfController::class, 'toggleStatus'])->name('shelves.toggle-status');
        Route::delete('/shelves/{id}/force-delete', [LibraryShelfController::class, 'forceDelete'])->name('shelves.force-delete');
        
        // Library Items (Books)
        Route::get('/items', [LibraryItemController::class, 'index'])->name('items.index');
        Route::get('/items/stats', [LibraryItemController::class, 'stats'])->name('items.stats');
        Route::get('/items/{id}', [LibraryItemController::class, 'show'])->name('items.show');
        Route::post('/items', [LibraryItemController::class, 'store'])->name('items.store');
        Route::put('/items/{id}', [LibraryItemController::class, 'update'])->name('items.update');
        Route::delete('/items/{id}', [LibraryItemController::class, 'destroy'])->name('items.destroy');
        Route::post('/items/{id}/toggle-status', [LibraryItemController::class, 'toggleStatus'])->name('items.toggle-status');
        Route::delete('/items/{id}/force-delete', [LibraryItemController::class, 'forceDelete'])->name('items.force-delete');
        
        // Library Copies (Barcode)
        Route::get('/copies', [LibraryCopyController::class, 'index'])->name('copies.index');
        Route::get('/copies/{id}', [LibraryCopyController::class, 'show'])->name('copies.show');
        Route::post('/copies', [LibraryCopyController::class, 'store'])->name('copies.store');
        Route::put('/copies/{id}', [LibraryCopyController::class, 'update'])->name('copies.update');
        Route::delete('/copies/{id}', [LibraryCopyController::class, 'destroy'])->name('copies.destroy');
        Route::post('/copies/{id}/toggle-status', [LibraryCopyController::class, 'toggleStatus'])->name('copies.toggle-status');
        Route::delete('/copies/{id}/force-delete', [LibraryCopyController::class, 'forceDelete'])->name('copies.force-delete');
        Route::get('/copies/{id}/history', [LibraryCopyController::class, 'history'])->name('copies.history');
        Route::get('/copies-by-barcode/{barcode}', [LibraryCopyController::class, 'findByBarcode'])->name('copies.find-by-barcode');
        
        // Library Loans (Borrow/Return)
        // Library Loans routes
        Route::get('/loans', [LibraryLoanController::class, 'index'])->name('loans.index');
        Route::get('/loans/stats', [LibraryLoanController::class, 'stats'])->name('loans.stats');
        Route::get('/loans/trends', [LibraryLoanController::class, 'trendStats'])->name('loans.trends');
        Route::get('/loans/search-books', [LibraryLoanController::class, 'searchBooks'])->name('loans.search-books');
        Route::post('/loans/trigger-notifications', [LibraryLoanController::class, 'triggerNotifications'])->name('loans.trigger-notifications');
        Route::get('/loans/{id}', [LibraryLoanController::class, 'show'])->name('loans.show');
        Route::post('/loans', [LibraryLoanController::class, 'store'])->name('loans.store');
        Route::post('/loans/{id}/return', [LibraryLoanController::class, 'returnCopy'])->name('loans.return');
        Route::post('/loans/{id}/approve', [LibraryLoanController::class, 'approve'])->name('loans.approve');
        Route::put('/loans/{id}', [LibraryLoanController::class, 'update'])->name('loans.update');
        Route::delete('/loans/{id}', [LibraryLoanController::class, 'destroy'])->name('loans.destroy');
        Route::get('/loans/find-copy/{barcode}', [LibraryLoanController::class, 'findCopyByBarcode'])->name('loans.find-copy-by-barcode');
        Route::get('/borrowers/search', [LibraryLoanController::class, 'searchBorrowers'])->name('loans.search-borrowers');

        // Library Fines routes
        Route::get('/fines', [LibraryFineController::class, 'index'])->name('fines.index');
        Route::get('/fines/stats', [LibraryFineController::class, 'stats'])->name('fines.stats');
        Route::get('/fines/{id}', [LibraryFineController::class, 'show'])->name('fines.show');
        Route::post('/fines', [LibraryFineController::class, 'store'])->name('fines.store');
        Route::put('/fines/{id}', [LibraryFineController::class, 'update'])->name('fines.update');
        Route::delete('/fines/{id}', [LibraryFineController::class, 'destroy'])->name('fines.destroy');
        Route::post('/fines/{id}/toggle', [LibraryFineController::class, 'toggleStatus'])->name('fines.toggle-status');
        Route::delete('/fines/{id}/force-delete', [LibraryFineController::class, 'forceDelete'])->name('fines.force-delete');
        Route::post('/fines/{id}/pay', [LibraryFineController::class, 'pay'])->name('fines.pay');
        Route::post('/fines/{id}/waive', [LibraryFineController::class, 'waive'])->name('fines.waive');

        // Library Guests routes
        Route::get('/guests', [LibraryGuestController::class, 'index'])->name('guests.index');
        Route::get('/guests/search-users', [LibraryGuestController::class, 'searchUsers'])->name('guests.search-users');
        Route::get('/guests/{id}', [LibraryGuestController::class, 'show'])->name('guests.show');
        Route::post('/guests', [LibraryGuestController::class, 'store'])->name('guests.store');
        Route::put('/guests/{id}', [LibraryGuestController::class, 'update'])->name('guests.update');
        Route::delete('/guests/{id}', [LibraryGuestController::class, 'destroy'])->name('guests.destroy');
        Route::post('/guests/{id}/toggle', [LibraryGuestController::class, 'toggleStatus'])->name('guests.toggle');
        Route::delete('/guests/{id}/force-delete', [LibraryGuestController::class, 'forceDelete'])->name('guests.force-delete');

        // Library Visits (Entry/Exit Session) routes
        Route::get('/visits', [LibraryVisitController::class, 'index'])->name('visits.index');
        Route::get('/visits/{id}', [LibraryVisitController::class, 'show'])->name('visits.show');
        Route::post('/visits', [LibraryVisitController::class, 'store'])->name('visits.store');
        Route::put('/visits/{id}', [LibraryVisitController::class, 'update'])->name('visits.update');
        Route::delete('/visits/{id}', [LibraryVisitController::class, 'destroy'])->name('visits.destroy');
        Route::post('/visits/{id}/toggle-status', [LibraryVisitController::class, 'toggleStatus'])->name('visits.toggle-status');
        Route::delete('/visits/{id}/force-delete', [LibraryVisitController::class, 'forceDelete'])->name('visits.force-delete');
        Route::post('/visits/check-in', [LibraryVisitController::class, 'checkIn'])->name('visits.check-in');
        Route::post('/visits/check-out', [LibraryVisitController::class, 'checkOut'])->name('visits.check-out');
        Route::post('/visits/find-open', [LibraryVisitController::class, 'findOpenVisit'])->name('visits.find-open');

        // Library Reading Logs (In-Library Reading Activity) routes
        Route::get('/reading-logs', [LibraryReadingLogController::class, 'index'])->name('reading-logs.index');
        Route::get('/reading-logs/{id}', [LibraryReadingLogController::class, 'show'])->name('reading-logs.show');
        Route::post('/reading-logs', [LibraryReadingLogController::class, 'store'])->name('reading-logs.store');
        Route::put('/reading-logs/{id}', [LibraryReadingLogController::class, 'update'])->name('reading-logs.update');
        Route::delete('/reading-logs/{id}', [LibraryReadingLogController::class, 'destroy'])->name('reading-logs.destroy');
        Route::post('/reading-logs/{id}/toggle-status', [LibraryReadingLogController::class, 'toggleStatus'])->name('reading-logs.toggle-status');
        Route::delete('/reading-logs/{id}/force-delete', [LibraryReadingLogController::class, 'forceDelete'])->name('reading-logs.force-delete');
        Route::post('/reading-logs/{id}/start', [LibraryReadingLogController::class, 'start'])->name('reading-logs.start');
        Route::post('/reading-logs/{id}/stop', [LibraryReadingLogController::class, 'stop'])->name('reading-logs.stop');
        Route::get('/reading-logs/visit-stats/{visitId}', [LibraryReadingLogController::class, 'getVisitStats'])->name('reading-logs.visit-stats');
        Route::get('/reading-logs-search/visits', [LibraryReadingLogController::class, 'searchVisits'])->name('reading-logs.search-visits');
        Route::get('/reading-logs-search/items', [LibraryReadingLogController::class, 'searchItems'])->name('reading-logs.search-items');
        Route::get('/reading-logs-search/copies', [LibraryReadingLogController::class, 'searchCopies'])->name('reading-logs.search-copies');

        // Library Reading Dashboard (Analytics & Statistics) NEW
        Route::get('/reading-dashboard', [LibraryReadingDashboardController::class, 'index'])->name('reading-dashboard.index');
        Route::get('/reading-dashboard/summary', [LibraryReadingDashboardController::class, 'summary'])->name('reading-dashboard.summary');
        Route::get('/reading-dashboard/chart', [LibraryReadingDashboardController::class, 'chart'])->name('reading-dashboard.chart');

        // Library Stock Taking (Inventory Audit) routes
        Route::resource('stock-takings', LibraryStockTakingController::class);
        Route::delete('/stock-takings/{id}/force-delete', [LibraryStockTakingController::class, 'forceDelete'])->name('stock-takings.force-delete');
        
        // Stock Taking Items (Scanner) routes
        Route::get('/stock-taking-items/{stockTakingId}/data', [LibraryStockTakingItemController::class, 'itemsData'])->name('stock-taking-items.data');
        Route::post('/stock-takings/{stockTakingId}/scan', [LibraryStockTakingItemController::class, 'scan'])->name('stock-taking-items.scan');
        Route::get('/stock-taking-items/{id}', [LibraryStockTakingItemController::class, 'getItem'])->name('stock-taking-items.show');
        Route::put('/stock-taking-items/{id}', [LibraryStockTakingItemController::class, 'updateItem'])->name('stock-taking-items.update');
        Route::delete('/stock-taking-items/{id}', [LibraryStockTakingItemController::class, 'destroyItem'])->name('stock-taking-items.destroy');
        Route::delete('/stock-taking-items/{id}/force-delete', [LibraryStockTakingItemController::class, 'forceDeleteItem'])->name('stock-taking-items.force-delete');
        
        // Library Reservations (Book Hold/Queue System) routes
        Route::get('/reservations', [LibraryReservationController::class, 'index'])->name('reservations.index');
        Route::get('/reservations/search-users', [LibraryReservationController::class, 'searchUsers'])->name('reservations.search-users');
        Route::post('/reservations/auto-cancel-expired', [LibraryReservationController::class, 'autoCancelExpiredReservations'])->name('reservations.auto-cancel-expired');
        Route::get('/reservations/{itemId}/available-copies', [LibraryReservationController::class, 'getAvailableCopies'])->name('reservations.available-copies');
        Route::post('/reservations', [LibraryReservationController::class, 'store'])->name('reservations.store');
        Route::get('/reservations/{id}', [LibraryReservationController::class, 'show'])->name('reservations.show');
        Route::put('/reservations/{id}', [LibraryReservationController::class, 'update'])->name('reservations.update');
        Route::post('/reservations/{id}/assign-copy', [LibraryReservationController::class, 'assignCopy'])->name('reservations.assign-copy');
        Route::post('/reservations/{id}/fulfill', [LibraryReservationController::class, 'fulfill'])->name('reservations.fulfill');
        Route::post('/reservations/{id}/cancel', [LibraryReservationController::class, 'cancel'])->name('reservations.cancel');
        Route::delete('/reservations/{id}', [LibraryReservationController::class, 'destroy'])->name('reservations.destroy');
        Route::delete('/reservations/{id}/force-delete', [LibraryReservationController::class, 'forceDelete'])->name('reservations.force-delete');
        
        // Library Reports routes
        Route::get('/reports/overdue-loans', [OverdueLoansReportController::class, 'index'])->name('reports.overdue_loans.index');
        Route::get('/reports/overdue-loans/data', [OverdueLoansReportController::class, 'data'])->name('reports.overdue_loans.data');
        Route::get('/reports/overdue-loans/summary', [OverdueLoansReportController::class, 'summary'])->name('reports.overdue_loans.summary');
        
        // Active Loans Report
        Route::get('/reports/active-loans', [ActiveLoansReportController::class, 'index'])->name('reports.active_loans.index');
        Route::get('/reports/active-loans/data', [ActiveLoansReportController::class, 'data'])->name('reports.active_loans.data');
        Route::get('/reports/active-loans/summary', [ActiveLoansReportController::class, 'summary'])->name('reports.active_loans.summary');
        
        // Outstanding Fines Report
        Route::get('/reports/outstanding-fines', [OutstandingFinesReportController::class, 'index'])->name('reports.outstanding_fines.index');
        Route::get('/reports/outstanding-fines/data', [OutstandingFinesReportController::class, 'data'])->name('reports.outstanding_fines.data');
        Route::get('/reports/outstanding-fines/summary', [OutstandingFinesReportController::class, 'summary'])->name('reports.outstanding_fines.summary');
        Route::post('/reports/outstanding-fines/{id}/mark-paid', [OutstandingFinesReportController::class, 'markAsPaid'])->name('reports.outstanding_fines.mark_paid');
        
        // Collection Summary Report
        Route::get('/reports/collection-summary', [CollectionSummaryReportController::class, 'index'])->name('reports.collection_summary.index');
        Route::get('/reports/collection-summary/data', [CollectionSummaryReportController::class, 'data'])->name('reports.collection_summary.data');
        Route::get('/reports/collection-summary/summary', [CollectionSummaryReportController::class, 'summary'])->name('reports.collection_summary.summary');
        Route::get('/reports/collection-summary/by-status', [CollectionSummaryReportController::class, 'byStatus'])->name('reports.collection_summary.by_status');
        Route::get('/reports/collection-summary/dashboard-widget', [CollectionSummaryReportController::class, 'dashboardWidget'])->name('reports.collection_summary.dashboard_widget');
        
        // Books Report (Grid View)
        Route::get('/books-report', [LibraryBooksReportController::class, 'index'])->name('books_report.index');
        Route::get('/books-report/data', [LibraryBooksReportController::class, 'data'])->name('books_report.data');
        Route::post('/books-report/{libraryItem}/reserve', [LibraryBooksReportController::class, 'reserve'])->name('books_report.reserve');
        
        // Daily Visit Statistics Report
        Route::get('/reports/daily-visits', [DailyVisitReportController::class, 'index'])->name('reports.daily_visits.index');
        Route::get('/reports/daily-visits/summary', [DailyVisitReportController::class, 'summary'])->name('reports.daily_visits.summary');
        Route::get('/reports/daily-visits/data', [DailyVisitReportController::class, 'data'])->name('reports.daily_visits.data');
        Route::get('/reports/daily-visits/open-sessions', [DailyVisitReportController::class, 'openSessionsData'])->name('reports.daily_visits.open_sessions');
        Route::get('/reports/daily-visits/today-operations', [DailyVisitReportController::class, 'todayOperations'])->name('reports.daily_visits.today_operations');
        Route::post('/reports/daily-visits/{id}/force-checkout', [DailyVisitReportController::class, 'forceCheckout'])->name('reports.daily_visits.force_checkout');
    });

// Library Analytics Chart Routes
Route::middleware(['auth'])
    ->prefix('charts')
    ->name('charts.')
    ->group(function () {
        Route::get('/analytics', [AnalyticsChartController::class, 'index'])->name('analytics.index');
    });

// QR-based Library Visit Routes (Self-Service)
// These routes work ALONGSIDE existing admin CRUD without modifying them
Route::middleware(['auth'])
    ->prefix('qr/library')
    ->name('qr.library.')
    ->group(function () {
        // QR Generator Page (for admin/staff to print QR code)
        Route::get('/qr-generator', [QRLibraryVisitController::class, 'showQRGenerator'])->name('qr-generator');
        
        // QR Visit Routes (for visitors using QR code)
        Route::get('/visits', [QRLibraryVisitController::class, 'showQRPage'])->name('visits.index');
        Route::post('/visits/check-in', [QRLibraryVisitController::class, 'handleCheckIn'])->name('visits.check-in');
        Route::post('/visits/check-out', [QRLibraryVisitController::class, 'handleCheckOut'])->name('visits.check-out');
        
        // NEW: QR Start Reading System (scan QR after check-in to start reading log)
        Route::get('/start-reading-qr-generator', [QRLibraryVisitController::class, 'showStartReadingQRGenerator'])->name('start-reading.qr-generator');
        Route::get('/start-reading', [QRLibraryVisitController::class, 'showStartReadingForm'])->name('start-reading.form');
        Route::post('/start-reading', [QRLibraryVisitController::class, 'handleStartReading'])->name('start-reading.submit');
        
        // NEW: User's Own Reading Logs (view, stop, details)
        Route::get('/my-reading-logs', [QRLibraryVisitController::class, 'showMyReadingLogs'])->name('my-reading-logs');
        Route::post('/my-reading-logs/stop', [QRLibraryVisitController::class, 'stopMyReading'])->name('my-reading-logs.stop');
        Route::get('/my-reading-logs/{id}', [QRLibraryVisitController::class, 'showMyReadingLogDetail'])->name('my-reading-logs.detail');
        
        // NEW: QR Loan Request System (self-service book borrowing)
        Route::get('/LibraryLoan_fetch_automatic', [QRLibraryVisitController::class, 'showLoanRequestForm'])->name('loan-request.form');
        Route::post('/LibraryLoan_fetch_automatic', [QRLibraryVisitController::class, 'handleLoanRequest'])->name('loan-request.submit');
        
        // NEW: QR Books Report Generator (scan QR to browse library books)
        Route::get('/books-report-qr-generator', [LibraryBooksReportController::class, 'showQRGenerator'])->name('books-report.qr-generator');
        
        // NEW: User Page QR Generator (scan QR to access all library services)
        Route::get('/user_page/user_page-qr-generator', [UserPageController::class, 'showQRGenerator'])->name('user_page.qr-generator');
    });

require __DIR__.'/auth.php';
