<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_name',
        'supplier_name_ar',
        'applicant_email',
        'applicant_phone',
        'category',
        'description',
        'description_ar',
        'telegram_group_link',
        'website',
        'location',
        'location_ar',
        'proof_documents',
        'status',
        'admin_notes',
    ];

    protected $casts = [
        'proof_documents' => 'array',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Convert approved application to Supplier
     */
    public function convertToSupplier()
    {
        if (!$this->isApproved()) {
            return null;
        }

        return Supplier::create([
            'name' => $this->supplier_name,
            'name_ar' => $this->supplier_name_ar,
            'category' => $this->category,
            'description' => $this->description,
            'description_ar' => $this->description_ar,
            'telegram_group_link' => $this->telegram_group_link,
            'website' => $this->website,
            'location' => $this->location,
            'location_ar' => $this->location_ar,
            'is_verified' => true,
            'is_active' => true,
            'verification_documents' => $this->proof_documents,
        ]);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }
}
