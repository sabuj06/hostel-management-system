<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AssetCategoryController;
use App\Http\Controllers\Admin\AssetController;
use App\Http\Controllers\Admin\AssetDamageReportController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\BedController;
use App\Http\Controllers\Admin\BlockController;
use App\Http\Controllers\Admin\ComplaintCategoryController;
use App\Http\Controllers\Admin\ComplaintController;
use App\Http\Controllers\Admin\FeeStructureController;
use App\Http\Controllers\Admin\FloorController;
use App\Http\Controllers\Admin\GuardianController;
use App\Http\Controllers\Admin\HostelController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\LeaveRequestController;
use App\Http\Controllers\Admin\MealMenuController;
use App\Http\Controllers\Admin\MessBillController;
use App\Http\Controllers\Admin\MessCutController;
use App\Http\Controllers\Admin\NoticeController;
use App\Http\Controllers\Admin\NotificationLogController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PolicyDocumentController;
use App\Http\Controllers\Admin\QrAttendanceController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RoomAllocationController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\SmartSearchController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentDocumentController as AdminStudentDocumentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VisitorController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\Student\ChatbotController;
use App\Http\Controllers\Student\PaymentGatewayController;
use App\Http\Controllers\Student\PolicyQaController;
use App\Http\Controllers\Student\StudentDocumentController;
use App\Http\Controllers\Student\StudentPortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Phase 2 — Hostel structure (admin + warden only)
    Route::middleware('role:admin,warden')->group(function () {
        Route::resource('hostels', HostelController::class)->except(['show']);
        Route::resource('blocks', BlockController::class)->except(['show']);
        Route::resource('floors', FloorController::class)->except(['show']);
        Route::resource('rooms', RoomController::class)->except(['show']);

        Route::get('/beds', [BedController::class, 'index'])->name('beds.index');
        Route::post('/beds', [BedController::class, 'store'])->name('beds.store');
        Route::patch('/beds/{bed}/status', [BedController::class, 'updateStatus'])->name('beds.status');
        Route::delete('/beds/{bed}', [BedController::class, 'destroy'])->name('beds.destroy');
    });

    // Phase 3 — Students, Guardians, Room Allocation (admin + warden only)
    Route::middleware('role:admin,warden')->group(function () {
        Route::resource('students', StudentController::class);

        Route::post('/students/{student}/guardians', [GuardianController::class, 'store'])->name('guardians.store');
        Route::delete('/guardians/{guardian}', [GuardianController::class, 'destroy'])->name('guardians.destroy');

        Route::get('/room-allocations', [RoomAllocationController::class, 'index'])->name('room-allocations.index');
        Route::get('/room-allocations/create', [RoomAllocationController::class, 'create'])->name('room-allocations.create');
        Route::post('/room-allocations', [RoomAllocationController::class, 'store'])->name('room-allocations.store');
        Route::post('/room-allocations/{allocation}/transfer', [RoomAllocationController::class, 'transfer'])->name('room-allocations.transfer');
        Route::post('/room-allocations/{allocation}/checkout', [RoomAllocationController::class, 'checkout'])->name('room-allocations.checkout');
        Route::get('/rooms/{room}/available-beds', [RoomAllocationController::class, 'availableBeds'])->name('rooms.available-beds');
    });

    // Activity Logs — admin + warden
    Route::middleware('role:admin,warden')->group(function () {
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });

    // Phase 4 — Fee & Payment Management (admin + warden only)
    Route::middleware('role:admin,warden')->group(function () {
        Route::resource('fee-structures', FeeStructureController::class)->except(['show']);

        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');

        Route::post('/invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
    });

    // AI complaint suggestion — available to every authenticated role (students included)
    Route::post('/complaints-ai-suggest', [ComplaintController::class, 'suggest'])->name('complaints.ai-suggest');
    Route::post('/complaints-ai-generate', [ComplaintController::class, 'generateFromNote'])->name('complaints.ai-generate');

    // Phase 5 — Complaints & Maintenance (admin + warden + staff)
    Route::middleware('role:admin,warden,staff')->group(function () {
        Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
        Route::get('/complaints/create', [ComplaintController::class, 'create'])->name('complaints.create');
        Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
        Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');
        Route::patch('/complaints/{complaint}/status', [ComplaintController::class, 'updateStatus'])->name('complaints.status');
        Route::patch('/complaints/{complaint}/assign', [ComplaintController::class, 'assign'])->name('complaints.assign');
        Route::post('/complaints/{complaint}/comments', [ComplaintController::class, 'addComment'])->name('complaints.comments.store');

        Route::get('/complaint-categories', [ComplaintCategoryController::class, 'index'])->name('complaint-categories.index');
        Route::post('/complaint-categories', [ComplaintCategoryController::class, 'store'])->name('complaint-categories.store');
        Route::delete('/complaint-categories/{complaintCategory}', [ComplaintCategoryController::class, 'destroy'])->name('complaint-categories.destroy');
    });

    // Phase 6 — Notices & Notifications
    Route::get('/notices', [NoticeController::class, 'index'])->name('notices.index');
    Route::post('/notices/{notice}/read', [NoticeController::class, 'markRead'])->name('notices.read');

    Route::middleware('role:admin,warden')->group(function () {
        Route::get('/notices-manage', [NoticeController::class, 'manage'])->name('notices.manage');
        Route::get('/notices/create', [NoticeController::class, 'create'])->name('notices.create');
        Route::post('/notices', [NoticeController::class, 'store'])->name('notices.store');
        Route::get('/notices/{notice}/edit', [NoticeController::class, 'edit'])->name('notices.edit');
        Route::put('/notices/{notice}', [NoticeController::class, 'update'])->name('notices.update');
        Route::delete('/notices/{notice}', [NoticeController::class, 'destroy'])->name('notices.destroy');
        Route::post('/notices-ai-generate', [NoticeController::class, 'generateDraft'])->name('notices.ai-generate');
    });

    Route::post('/notices/{notice}/translate', [NoticeController::class, 'translate'])->name('notices.translate');

    // Phase 7 — Visitor Management (admin + warden + staff, gate duty)
    Route::middleware('role:admin,warden,staff')->group(function () {
        Route::get('/visitors', [VisitorController::class, 'index'])->name('visitors.index');
        Route::get('/visitors/create', [VisitorController::class, 'create'])->name('visitors.create');
        Route::post('/visitors', [VisitorController::class, 'store'])->name('visitors.store');
        Route::post('/visitors/{visitor}/checkout', [VisitorController::class, 'checkout'])->name('visitors.checkout');
        Route::get('/visitors-search-students', [VisitorController::class, 'searchStudents'])->name('visitors.search-students');
    });

    // Phase 9 — Reports & Analytics (admin + warden only)
    Route::middleware('role:admin,warden')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/ai-summary', [ReportController::class, 'aiSummary'])->name('reports.ai-summary');

        Route::get('/smart-search', [SmartSearchController::class, 'index'])->name('smart-search.index');
        Route::post('/smart-search', [SmartSearchController::class, 'search'])->name('smart-search.search');
    });

    // Phase 10 — User & Role Management (admin only)
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
        Route::post('/roles/{role}/permissions/toggle', [RoleController::class, 'togglePermission'])->name('roles.permissions.toggle');
    });

    // Document / ID Verification (admin + warden)
    Route::middleware('role:admin,warden')->group(function () {
        Route::get('/student-documents', [AdminStudentDocumentController::class, 'index'])->name('student-documents.index');
        Route::get('/student-documents/{studentDocument}/download', [AdminStudentDocumentController::class, 'download'])->name('student-documents.download');
        Route::post('/student-documents/{studentDocument}/review', [AdminStudentDocumentController::class, 'review'])->name('student-documents.review');
        Route::delete('/student-documents/{studentDocument}', [AdminStudentDocumentController::class, 'destroy'])->name('student-documents.destroy');
    });

    // Phase 11 — Student Self-Service Portal (student role only)
    Route::middleware('role:student')->prefix('portal')->name('student-portal.')->group(function () {
        Route::get('/', [StudentPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [StudentPortalController::class, 'profile'])->name('profile');
        Route::put('/profile', [StudentPortalController::class, 'updateProfile'])->name('profile.update');
        Route::get('/invoices', [StudentPortalController::class, 'invoices'])->name('invoices');
        Route::get('/complaints', [StudentPortalController::class, 'complaints'])->name('complaints');
        Route::get('/complaints/create', [StudentPortalController::class, 'createComplaint'])->name('complaints.create');
        Route::post('/complaints', [StudentPortalController::class, 'storeComplaint'])->name('complaints.store');
        Route::get('/complaints/{complaint}', [StudentPortalController::class, 'showComplaint'])->name('complaints.show');

        Route::get('/attendance', [StudentPortalController::class, 'attendance'])->name('attendance');
        Route::get('/leave-requests', [StudentPortalController::class, 'leaveRequests'])->name('leave-requests');
        Route::get('/leave-requests/create', [StudentPortalController::class, 'createLeaveRequest'])->name('leave-requests.create');
        Route::post('/leave-requests', [StudentPortalController::class, 'storeLeaveRequest'])->name('leave-requests.store');

        Route::get('/meal-menu', [StudentPortalController::class, 'mealMenu'])->name('meal-menu');
        Route::get('/mess-cuts', [StudentPortalController::class, 'messCuts'])->name('mess-cuts');
        Route::get('/mess-cuts/create', [StudentPortalController::class, 'createMessCut'])->name('mess-cuts.create');
        Route::post('/mess-cuts', [StudentPortalController::class, 'storeMessCut'])->name('mess-cuts.store');

        Route::get('/chatbot/history', [ChatbotController::class, 'history'])->name('chatbot.history');
        Route::post('/chatbot/ask', [ChatbotController::class, 'ask'])->name('chatbot.ask');

        Route::get('/policy-qa', [PolicyQaController::class, 'index'])->name('policy-qa');
        Route::post('/policy-qa/ask', [PolicyQaController::class, 'ask'])->name('policy-qa.ask');

        Route::get('/invoices/{invoice}/pay', [PaymentGatewayController::class, 'checkout'])->name('invoices.pay');
        Route::post('/payments/verify', [PaymentGatewayController::class, 'verify'])->name('payments.verify');

        Route::get('/documents', [StudentDocumentController::class, 'index'])->name('documents.index');
        Route::post('/documents', [StudentDocumentController::class, 'store'])->name('documents.store');
        Route::delete('/documents/{document}', [StudentDocumentController::class, 'destroy'])->name('documents.destroy');

        // Student's own QR code (for staff to scan at gate/attendance)
        Route::get('/my-qr-code', [StudentPortalController::class, 'myQrCode'])->name('my-qr-code');
    });
});

// Phase 15 — Inventory & Asset Management (admin + warden)
Route::middleware(['auth', 'role:admin,warden'])->group(function () {
    Route::resource('assets', AssetController::class);

    Route::post('/assets/{asset}/assign', [AssetController::class, 'assign'])->name('assets.assign');
    Route::post('/assets/{asset}/write-off', [AssetController::class, 'writeOff'])->name('assets.write-off');

    Route::resource('asset-categories', AssetCategoryController::class)->except(['show']);

    Route::resource('asset-damage-reports', AssetDamageReportController::class)->except(['show']);

    Route::patch('/asset-damage-reports/{assetDamageReport}/status', [AssetDamageReportController::class, 'updateStatus'])
        ->name('asset-damage-reports.status');
});

// Receipts — accessible to any authenticated role, self-scoped inside the controller
Route::middleware('auth')->group(function () {
    Route::get('/receipts/{payment}', [ReceiptController::class, 'show'])->name('receipts.show');
    Route::get('/receipts/{payment}/download', [ReceiptController::class, 'download'])->name('receipts.download');
});

// Public webhook — no auth, no CSRF (exempted in bootstrap/app.php).
Route::post('/webhooks/razorpay', [PaymentWebhookController::class, 'razorpay'])->name('webhooks.razorpay');

// Phase 12 — Attendance / Curfew Tracking + Leave Requests (admin + warden + staff)
Route::middleware(['auth', 'role:admin,warden,staff'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/mark', [AttendanceController::class, 'mark'])->name('attendance.mark');
    Route::post('/attendance/mark-all-present', [AttendanceController::class, 'markAllPresent'])->name('attendance.mark-all-present');
    Route::get('/attendance-curfew-settings', [AttendanceController::class, 'curfewSettings'])->name('attendance.curfew-settings');
    Route::post('/attendance-curfew-settings', [AttendanceController::class, 'saveCurfewSettings'])->name('attendance.curfew-settings.save');

    Route::get('/attendance-qr-scanner', [QrAttendanceController::class, 'scanner'])->name('attendance.qr-scanner');
    Route::post('/attendance-qr-scan', [QrAttendanceController::class, 'scan'])->name('attendance.qr-scan');
    Route::get('/students/{student}/id-card', [QrAttendanceController::class, 'idCard'])->name('students.id-card');

    Route::get('/leave-requests', [LeaveRequestController::class, 'index'])->name('leave-requests.index');
    Route::post('/leave-requests/{leaveRequest}/review', [LeaveRequestController::class, 'review'])->name('leave-requests.review');
});

// Phase 13 — Mess / Meal Management (admin + warden)
Route::middleware(['auth', 'role:admin,warden'])->group(function () {
    Route::get('/meal-menu', [MealMenuController::class, 'index'])->name('meal-menu.index');
    Route::post('/meal-menu/save-cell', [MealMenuController::class, 'saveCell'])->name('meal-menu.save-cell');

    Route::get('/mess-cuts', [MessCutController::class, 'index'])->name('mess-cuts.index');
    Route::get('/mess-cuts/create', [MessCutController::class, 'create'])->name('mess-cuts.create');
    Route::post('/mess-cuts', [MessCutController::class, 'store'])->name('mess-cuts.store');
    Route::delete('/mess-cuts/{messCut}', [MessCutController::class, 'destroy'])->name('mess-cuts.destroy');

    Route::get('/mess-rates', [MessCutController::class, 'rates'])->name('mess-cuts.rates');
    Route::post('/mess-rates', [MessCutController::class, 'saveRates'])->name('mess-cuts.rates.save');

    Route::get('/mess-bills/create', [MessBillController::class, 'create'])->name('mess-bills.create');
    Route::post('/mess-bills/generate', [MessBillController::class, 'generate'])->name('mess-bills.generate');
});

// Phase 14 — Notification Logs (admin only)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/notification-logs', [NotificationLogController::class, 'index'])->name('notification-logs.index');

    Route::get('/policy-documents', [PolicyDocumentController::class, 'index'])->name('policy-documents.index');
    Route::get('/policy-documents/create', [PolicyDocumentController::class, 'create'])->name('policy-documents.create');
    Route::post('/policy-documents', [PolicyDocumentController::class, 'store'])->name('policy-documents.store');
    Route::delete('/policy-documents/{policyDocument}', [PolicyDocumentController::class, 'destroy'])->name('policy-documents.destroy');
});