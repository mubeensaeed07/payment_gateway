<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminApiLogsController extends Controller
{
    /**
     * Display internal API logs with search functionality
     */
    public function index(Request $request)
    {
        $adminId = Auth::id();
        
        // Build query - only show logs for this admin's internal API calls
        $query = ApiLog::where('admin_id', $adminId)
            ->orderBy('created_at', 'desc');
        
        // Search by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Search by customer name
        if ($request->filled('customer_name')) {
            $query->where('customer_name', 'like', '%' . $request->customer_name . '%');
        }
        
        // Search by invoice number (full or partial)
        if ($request->filled('invoice_number')) {
            $invoiceNumber = $request->invoice_number;
            $query->where(function($q) use ($invoiceNumber) {
                $q->where('invoice_number', 'like', '%' . $invoiceNumber . '%')
                  ->orWhere('customer_number', 'like', '%' . $invoiceNumber . '%');
            });
        }
        
        $logs = $query->paginate(20);
        
        return view('admin.api-logs.index', compact('logs'));
    }
}
