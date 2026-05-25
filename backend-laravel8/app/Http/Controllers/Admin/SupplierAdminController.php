<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\SupplierApplication;
use Illuminate\Http\Request;

class SupplierAdminController extends Controller
{
    /**
     * List all pending applications
     */
    public function applications(Request $request)
    {
        $status = $request->query('status', 'pending');
        
        $query = SupplierApplication::orderBy('created_at', 'desc');
        
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        $applications = $query->paginate(20);
        
        return view('admin.suppliers.applications', [
            'applications' => $applications,
            'currentStatus' => $status,
        ]);
    }

    /**
     * View single application
     */
    public function viewApplication($id)
    {
        $application = SupplierApplication::findOrFail($id);
        
        return view('admin.suppliers.application-detail', [
            'application' => $application,
        ]);
    }

    /**
     * Approve application
     */
    public function approveApplication(Request $request, $id)
    {
        $application = SupplierApplication::findOrFail($id);
        
        if (!$application->isPending()) {
            return back()->with('error', 'This application has already been processed.');
        }
        
        $application->status = SupplierApplication::STATUS_APPROVED;
        $application->admin_notes = $request->admin_notes;
        $application->save();
        
        // Create the supplier
        $supplier = $application->convertToSupplier();
        
        return redirect()->route('admin.suppliers.applications')
            ->with('success', 'Application approved and supplier created successfully.');
    }

    /**
     * Reject application
     */
    public function rejectApplication(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);
        
        $application = SupplierApplication::findOrFail($id);
        
        if (!$application->isPending()) {
            return back()->with('error', 'This application has already been processed.');
        }
        
        $application->status = SupplierApplication::STATUS_REJECTED;
        $application->admin_notes = $request->admin_notes;
        $application->save();
        
        return redirect()->route('admin.suppliers.applications')
            ->with('success', 'Application rejected.');
    }

    /**
     * List all suppliers
     */
    public function suppliers(Request $request)
    {
        $suppliers = Supplier::orderBy('created_at', 'desc')->paginate(20);
        
        return view('admin.suppliers.index', [
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Toggle supplier active status
     */
    public function toggleStatus($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->is_active = !$supplier->is_active;
        $supplier->save();
        
        return back()->with('success', 'Supplier status updated.');
    }

    /**
     * Delete supplier
     */
    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();
        
        return back()->with('success', 'Supplier deleted.');
    }
}
