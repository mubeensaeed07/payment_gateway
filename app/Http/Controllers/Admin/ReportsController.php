<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportsController extends Controller
{
    /**
     * Show non-paid invoices report
     */
    public function nonPaidInvoices(Request $request)
    {
        $adminId = Auth::id();
        
        $query = Invoice::where('admin_id', $adminId)
            ->where('status', 'pending')
            ->with('customer')
            ->orderBy('created_at', 'desc');
        
        // Filter by created date if provided
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $invoices = $query->paginate(15);
        
        return view('admin.reports.non-paid-invoices', compact('invoices'));
    }
    
    /**
     * Show paid invoices report
     */
    public function paidInvoices(Request $request)
    {
        $adminId = Auth::id();
        
        $query = Invoice::where('admin_id', $adminId)
            ->where('status', 'paid')
            ->with('customer')
            ->orderBy('paid_at', 'desc');
        
        // Filter by paid date if provided
        if ($request->filled('date_from')) {
            $query->whereDate('paid_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('paid_at', '<=', $request->date_to);
        }
        
        $invoices = $query->paginate(15);
        
        return view('admin.reports.paid-invoices', compact('invoices'));
    }
    
    /**
     * Show all invoices report with summary cards
     */
    public function allInvoices(Request $request)
    {
        $adminId = Auth::id();
        
        // Get totals
        $totalNonPaid = Invoice::where('admin_id', $adminId)
            ->where('status', 'pending')
            ->count();
        
        $totalPaid = Invoice::where('admin_id', $adminId)
            ->where('status', 'paid')
            ->count();
        
        // Build query for invoices
        $query = Invoice::where('admin_id', $adminId)
            ->with('customer')
            ->orderBy('created_at', 'desc');
        
        // Filter by status if clicked from card
        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->where('status', 'pending');
            } elseif ($request->status === 'paid') {
                $query->where('status', 'paid');
            }
        }
        
        $invoices = $query->paginate(15);
        
        return view('admin.reports.all-invoices', compact('invoices', 'totalNonPaid', 'totalPaid'));
    }
}

