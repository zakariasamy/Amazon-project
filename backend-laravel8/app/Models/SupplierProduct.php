<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'name',
        'name_ar',
        'sku',
        'category',
        'description',
        'description_ar',
        'images',
        'min_order_quantity',
        'price_tiers',
        'base_price',
        'unit',
        'unit_ar',
        'is_available',
        'is_featured',
        'stock_quantity',
        'origin_country',
        'origin_country_ar',
    ];

    protected $casts = [
        'images' => 'array',
        'price_tiers' => 'array',
        'base_price' => 'decimal:2',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
        'min_order_quantity' => 'integer',
        'stock_quantity' => 'integer',
    ];

    // Unit constants
    const UNIT_PIECE = 'piece';
    const UNIT_BOX = 'box';
    const UNIT_CARTON = 'carton';
    const UNIT_KG = 'kg';
    const UNIT_METER = 'meter';
    const UNIT_DOZEN = 'dozen';

    public static function getUnits()
    {
        return [
            self::UNIT_PIECE => ['en' => 'Piece', 'ar' => 'قطعة'],
            self::UNIT_BOX => ['en' => 'Box', 'ar' => 'صندوق'],
            self::UNIT_CARTON => ['en' => 'Carton', 'ar' => 'كرتونة'],
            self::UNIT_KG => ['en' => 'Kilogram', 'ar' => 'كيلو'],
            self::UNIT_METER => ['en' => 'Meter', 'ar' => 'متر'],
            self::UNIT_DOZEN => ['en' => 'Dozen', 'ar' => 'درزن'],
        ];
    }

    /**
     * Get the supplier that owns this product
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get localized name based on current locale
     */
    public function getLocalizedNameAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name;
    }

    /**
     * Get price for a specific quantity
     */
    public function getPriceForQuantity($quantity)
    {
        if (!$this->price_tiers || empty($this->price_tiers)) {
            return $this->base_price;
        }

        // Sort tiers by min_qty descending to find best matching tier
        $tiers = collect($this->price_tiers)->sortByDesc('min_qty');
        
        foreach ($tiers as $tier) {
            if ($quantity >= $tier['min_qty']) {
                return $tier['price'];
            }
        }
        
        return $this->base_price;
    }

    /**
     * Format price tiers for display
     */
    public function getFormattedPriceTiersAttribute()
    {
        if (!$this->price_tiers || empty($this->price_tiers)) {
            return [];
        }

        $locale = app()->getLocale();
        $unit = $locale === 'ar' ? $this->unit_ar : $this->unit;
        
        return collect($this->price_tiers)->map(function ($tier) use ($unit) {
            return [
                'range' => $tier['min_qty'] . ($tier['max_qty'] ? '-' . $tier['max_qty'] : '+'),
                'price' => number_format($tier['price'], 2) . ' EGP/' . $unit,
            ];
        })->toArray();
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }
}
