<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierApplication;
use App\Models\SupplierProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class SupplierController extends Controller
{
    /**
     * Display suppliers directory
     */
    public function index(Request $request)
    {
        $category = $request->query('category');
        $search = $request->query('search');
        
        $query = Supplier::active()->verified();
        
        if ($category && $category !== 'all') {
            $query->byCategory($category);
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('description_ar', 'like', "%{$search}%");
            });
        }
        
        $suppliers = $query->orderBy('name')->get();
        $categories = Supplier::getCategories();
        
        return view('suppliers.index', [
            'suppliers' => $suppliers,
            'categories' => $categories,
            'currentCategory' => $category ?? 'all',
            'search' => $search,
            'currentLang' => App::getLocale(),
            'isRtl' => App::getLocale() === 'ar'
        ]);
    }

    /**
     * Browse all products from suppliers
     */
    public function products(Request $request)
    {
        $category = $request->query('category');
        $supplierId = $request->query('supplier');
        $search = $request->query('search');
        $sort = $request->query('sort', 'newest');
        
        $query = SupplierProduct::with('supplier')
            ->available()
            ->whereHas('supplier', function($q) {
                $q->active()->verified();
            });
        
        if ($category && $category !== 'all') {
            $query->byCategory($category);
        }
        
        if ($supplierId) {
            $query->bySupplier($supplierId);
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('description_ar', 'like', "%{$search}%");
            });
        }
        
        // Sorting
        switch ($sort) {
            case 'price_low':
                $query->orderBy('base_price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('base_price', 'desc');
                break;
            case 'moq_low':
                $query->orderBy('min_order_quantity', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }
        
        $products = $query->paginate(20);
        $categories = Supplier::getCategories();
        $suppliers = Supplier::active()->verified()->orderBy('name')->get();
        
        return view('suppliers.products', [
            'products' => $products,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'currentCategory' => $category ?? 'all',
            'currentSupplier' => $supplierId,
            'search' => $search,
            'sort' => $sort,
            'currentLang' => App::getLocale(),
            'isRtl' => App::getLocale() === 'ar'
        ]);
    }

    /**
     * Show single supplier
     */
    public function show($id)
    {
        $supplier = Supplier::active()->verified()->findOrFail($id);
        
        return view('suppliers.show', [
            'supplier' => $supplier,
            'currentLang' => App::getLocale(),
            'isRtl' => App::getLocale() === 'ar'
        ]);
    }

    /**
     * Show single product
     */
    public function showProduct($id)
    {
        $product = SupplierProduct::with('supplier')
            ->available()
            ->whereHas('supplier', function($q) {
                $q->active()->verified();
            })
            ->findOrFail($id);
        
        // Get related products from same supplier
        $relatedProducts = SupplierProduct::available()
            ->bySupplier($product->supplier_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();
        
        return view('suppliers.product-show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'currentLang' => App::getLocale(),
            'isRtl' => App::getLocale() === 'ar'
        ]);
    }

    /**
     * Show application form
     */
    public function showApplicationForm()
    {
        $categories = Supplier::getCategories();
        
        return view('suppliers.apply', [
            'categories' => $categories,
            'currentLang' => App::getLocale(),
            'isRtl' => App::getLocale() === 'ar'
        ]);
    }

    /**
     * Submit supplier application
     */
    public function submitApplication(Request $request)
    {
        $request->validate([
            'supplier_name' => 'required|string|max:255',
            'supplier_name_ar' => 'required|string|max:255',
            'applicant_email' => 'required|email|max:255',
            'applicant_phone' => 'required|string|max:50',
            'category' => 'required|in:electronics,tools,car_accessories,fashion,home,sports,toys,general',
            'description' => 'nullable|string|max:1000',
            'description_ar' => 'nullable|string|max:1000',
            'telegram_group_link' => 'nullable|url|required_without:website',
            'website' => 'nullable|url|required_without:telegram_group_link',
            'location' => 'nullable|string|max:255',
            'location_ar' => 'nullable|string|max:255',
            'proof_documents' => 'required|array|min:1',
            'proof_documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'telegram_group_link.required_without' => __('suppliers.validation.contact_required'),
            'website.required_without' => __('suppliers.validation.contact_required'),
            'proof_documents.required' => __('suppliers.validation.proof_required'),
        ]);

        // Upload documents
        $uploadedDocuments = [];
        if ($request->hasFile('proof_documents')) {
            foreach ($request->file('proof_documents') as $file) {
                $path = $file->store('supplier-applications', 'public');
                $uploadedDocuments[] = $path;
            }
        }

        // Create application
        $application = SupplierApplication::create([
            'supplier_name' => $request->supplier_name,
            'supplier_name_ar' => $request->supplier_name_ar,
            'applicant_email' => $request->applicant_email,
            'applicant_phone' => $request->applicant_phone,
            'category' => $request->category,
            'description' => $request->description,
            'description_ar' => $request->description_ar,
            'telegram_group_link' => $request->telegram_group_link,
            'website' => $request->website,
            'location' => $request->location,
            'location_ar' => $request->location_ar,
            'proof_documents' => $uploadedDocuments,
            'status' => SupplierApplication::STATUS_PENDING,
        ]);

        return redirect()->route('suppliers.apply')
            ->with('success', __('suppliers.application_submitted'));
    }
}
