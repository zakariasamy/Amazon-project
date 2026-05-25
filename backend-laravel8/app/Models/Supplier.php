<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'category',
        'description',
        'description_ar',
        'phone',
        'whatsapp',
        'telegram_username',
        'telegram_group_link',
        'website',
        'location',
        'location_ar',
        'logo',
        'is_verified',
        'is_active',
        'verification_documents',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'verification_documents' => 'array',
    ];

    // Category constants
    const CATEGORY_ELECTRONICS = 'electronics';
    const CATEGORY_TOOLS = 'tools';
    const CATEGORY_CAR_ACCESSORIES = 'car_accessories';
    const CATEGORY_FASHION = 'fashion';
    const CATEGORY_HOME = 'home';
    const CATEGORY_SPORTS = 'sports';
    const CATEGORY_TOYS = 'toys';
    const CATEGORY_GENERAL = 'general';

    public static function getCategories()
    {
        return [
            self::CATEGORY_ELECTRONICS => ['en' => 'Electronics', 'ar' => 'الإلكترونيات'],
            self::CATEGORY_TOOLS => ['en' => 'Tools', 'ar' => 'العدد والأدوات'],
            self::CATEGORY_CAR_ACCESSORIES => ['en' => 'Car Accessories', 'ar' => 'مستلزمات السيارات'],
            self::CATEGORY_FASHION => ['en' => 'Fashion', 'ar' => 'الموضة والملابس'],
            self::CATEGORY_HOME => ['en' => 'Home & Kitchen', 'ar' => 'المنزل والمطبخ'],
            self::CATEGORY_SPORTS => ['en' => 'Sports', 'ar' => 'الرياضة'],
            self::CATEGORY_TOYS => ['en' => 'Toys', 'ar' => 'الألعاب'],
            self::CATEGORY_GENERAL => ['en' => 'General', 'ar' => 'عام'],
        ];
    }

    public function getCategoryNameAttribute()
    {
        $locale = app()->getLocale();
        $categories = self::getCategories();
        return $categories[$this->category][$locale] ?? $this->category;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get products for this supplier
     */
    public function products()
    {
        return $this->hasMany(SupplierProduct::class);
    }

    /**
     * Get available products count
     */
    public function getAvailableProductsCountAttribute()
    {
        return $this->products()->available()->count();
    }
}
