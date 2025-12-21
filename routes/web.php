<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\SuperAdminController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/login', function () {
    // If already authenticated, redirect to dashboard
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->isSuperAdmin()) {
            return redirect()->route('superadmin.dashboard');
        } elseif ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } else {
            return redirect()->route('reseller.dashboard');
        }
    }
    return view('auth.login');
})->name('login');

// Debug route to check credentials (remove after testing)
Route::get('/debug-google', function () {
    return [
        'client_id' => config('services.google.client_id'),
        'client_secret' => substr(config('services.google.client_secret'), 0, 10) . '...' . substr(config('services.google.client_secret'), -5),
        'redirect' => config('services.google.redirect'),
        'env_client_id' => env('GOOGLE_CLIENT_ID'),
        'env_redirect' => env('GOOGLE_REDIRECT_URI'),
    ];
});

// Debug route to test assets
Route::get('/test-assets', function () {
    return [
        'style_css' => asset('assets/css/style.css'),
        'plugins_css' => asset('assets/css/plugins.css'),
        'file_exists' => file_exists(public_path('assets/css/style.css')),
        'public_path' => public_path('assets/css/style.css'),
    ];
});

// Google OAuth routes
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('google.callback');

// Protected routes
Route::middleware(['auth', 'prevent.back'])->group(function () {
    Route::get('/', function () {
        $user = auth()->user();
        if ($user->isSuperAdmin()) {
            return redirect()->route('superadmin.dashboard');
        } elseif ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } else {
            return redirect()->route('reseller.dashboard');
        }
    });

    // SuperAdmin routes
    Route::prefix('superadmin')->name('superadmin.')->middleware('role:superadmin')->group(function () {
        Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/admins', [SuperAdminController::class, 'admins'])->name('admins');
        Route::get('/resellers', [SuperAdminController::class, 'resellers'])->name('resellers');
        Route::post('/users/create', [SuperAdminController::class, 'createUser'])->name('users.create');
        Route::put('/users/{id}/prefix', [SuperAdminController::class, 'updatePrefix'])->name('users.update-prefix');
        Route::delete('/users/{id}', [SuperAdminController::class, 'deleteUser'])->name('users.delete');
        Route::get('/admins/{id}/slabs', [SuperAdminController::class, 'getSlabs'])->name('admins.slabs.get');
        Route::post('/admins/{id}/slabs', [SuperAdminController::class, 'storeSlabs'])->name('admins.slabs.store');
        Route::get('/admins/{id}/external-provider', [SuperAdminController::class, 'getExternalProvider'])->name('admins.external-provider.get');
        Route::post('/admins/{id}/external-provider', [SuperAdminController::class, 'storeExternalProvider'])->name('admins.external-provider.store');
    });

    // Admin routes
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', function () {
            $adminId = auth()->id();
            
            // Today's statistics
            $todayStart = now()->startOfDay();
            $todayEnd = now()->endOfDay();
            
            $invoicesToday = \App\Models\Invoice::where('admin_id', $adminId)
                ->whereBetween('created_at', [$todayStart, $todayEnd])
                ->count();
            
            $paidToday = \App\Models\Invoice::where('admin_id', $adminId)
                ->where('status', 'paid')
                ->whereBetween('paid_at', [$todayStart, $todayEnd])
                ->sum('amount');
            
            // This week's statistics
            $weekStart = now()->startOfWeek();
            $weekEnd = now()->endOfWeek();
            
            $invoicesThisWeek = \App\Models\Invoice::where('admin_id', $adminId)
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->count();
            
            $paidThisWeek = \App\Models\Invoice::where('admin_id', $adminId)
                ->where('status', 'paid')
                ->whereBetween('paid_at', [$weekStart, $weekEnd])
                ->sum('amount');
            
            // This month's statistics
            $monthStart = now()->startOfMonth();
            $monthEnd = now()->endOfMonth();
            
            $invoicesThisMonth = \App\Models\Invoice::where('admin_id', $adminId)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();
            
            $paidThisMonth = \App\Models\Invoice::where('admin_id', $adminId)
                ->where('status', 'paid')
                ->whereBetween('paid_at', [$monthStart, $monthEnd])
                ->sum('amount');
            
            // Total counts
            $customerCount = \App\Models\Customer::where('admin_id', $adminId)->count();
            $invoiceCount = \App\Models\Invoice::where('admin_id', $adminId)->count();
            
            return view('admin.dashboard', compact(
                'customerCount',
                'invoiceCount',
                'invoicesToday',
                'paidToday',
                'invoicesThisWeek',
                'paidThisWeek',
                'invoicesThisMonth',
                'paidThisMonth'
            ));
        })->name('dashboard');
        
        // Customer management routes
        Route::get('/customers', [\App\Http\Controllers\Admin\AdminCustomerController::class, 'index'])->name('customers.index');
        Route::post('/customers', [\App\Http\Controllers\Admin\AdminCustomerController::class, 'store'])->name('customers.store');
        Route::post('/customers/{id}/create-invoice', [\App\Http\Controllers\Admin\AdminCustomerController::class, 'createInvoice'])->name('customers.create-invoice');
        Route::get('/customers/{id}/generate-invoice', [\App\Http\Controllers\Admin\AdminCustomerController::class, 'showGenerateInvoice'])->name('customers.generate-invoice');
        Route::post('/customers/{id}/generate-invoice', [\App\Http\Controllers\Admin\AdminCustomerController::class, 'generateInvoice'])->name('customers.generate-invoice');
        Route::get('/customers/{id}/invoice-history', [\App\Http\Controllers\Admin\AdminCustomerController::class, 'showInvoiceHistory'])->name('customers.invoice-history');
        Route::delete('/customers/{id}', [\App\Http\Controllers\Admin\AdminCustomerController::class, 'destroy'])->name('customers.destroy');
        
        // Invoice routes
        Route::get('/invoices/{id}', [\App\Http\Controllers\Admin\AdminCustomerController::class, 'showInvoice'])->name('invoices.show');
        
        // Reports routes
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/non-paid-invoices', [\App\Http\Controllers\Admin\ReportsController::class, 'nonPaidInvoices'])->name('non-paid-invoices');
            Route::get('/paid-invoices', [\App\Http\Controllers\Admin\ReportsController::class, 'paidInvoices'])->name('paid-invoices');
            Route::get('/all-invoices', [\App\Http\Controllers\Admin\ReportsController::class, 'allInvoices'])->name('all-invoices');
        });
        
        // Tools routes
        Route::get('/tools', [\App\Http\Controllers\Admin\ToolsController::class, 'index'])->name('tools.index');
        
        // Logs routes
        Route::get('/logs', [\App\Http\Controllers\Admin\AdminLogsController::class, 'index'])->name('logs.index');
    });

    // Reseller routes
    Route::prefix('reseller')->name('reseller.')->middleware('role:reseller')->group(function () {
        Route::get('/dashboard', function () {
            return view('reseller.dashboard');
        })->name('dashboard');
    });

    // Logout
    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    })->name('logout');
});

// API routes (no authentication required for external access)
Route::prefix('api')->name('api.')->group(function () {
    Route::post('/billinquiry', [\App\Http\Controllers\Api\BillApiController::class, 'inquiry'])->name('bill.inquiry');
    Route::post('/billpayment', [\App\Http\Controllers\Api\BillApiController::class, 'payment'])->name('bill.payment');
});
