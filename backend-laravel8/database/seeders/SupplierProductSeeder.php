<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\SupplierProduct;

class SupplierProductSeeder extends Seeder
{
    public function run()
    {
        // Get suppliers
        $electronicsSupplier = Supplier::where('category', 'electronics')->first();
        $toolsSupplier = Supplier::where('category', 'tools')->first();
        $fashionSupplier = Supplier::where('category', 'fashion')->first();
        $carSupplier = Supplier::where('category', 'car_accessories')->first();
        $homeSupplier = Supplier::where('category', 'home')->first();

        if ($electronicsSupplier) {
            // Electronics Products
            SupplierProduct::create([
                'supplier_id' => $electronicsSupplier->id,
                'name' => 'USB-C Fast Charging Cable 1.5m',
                'name_ar' => 'كابل شحن سريع USB-C 1.5 متر',
                'category' => 'electronics',
                'description' => 'High quality braided USB-C cable with fast charging support',
                'description_ar' => 'كابل USB-C مجدول عالي الجودة يدعم الشحن السريع',
                'min_order_quantity' => 50,
                'base_price' => 35.00,
                'price_tiers' => [
                    ['min_qty' => 50, 'max_qty' => 99, 'price' => 35.00],
                    ['min_qty' => 100, 'max_qty' => 499, 'price' => 28.00],
                    ['min_qty' => 500, 'max_qty' => null, 'price' => 22.00],
                ],
                'unit' => 'piece',
                'unit_ar' => 'قطعة',
                'is_available' => true,
                'origin_country' => 'China',
                'origin_country_ar' => 'الصين',
            ]);

            SupplierProduct::create([
                'supplier_id' => $electronicsSupplier->id,
                'name' => 'Wireless Bluetooth Earbuds',
                'name_ar' => 'سماعات بلوتوث لاسلكية',
                'category' => 'electronics',
                'description' => 'TWS earbuds with charging case, touch control, 20h battery',
                'description_ar' => 'سماعات TWS مع علبة شحن، تحكم باللمس، بطارية 20 ساعة',
                'min_order_quantity' => 20,
                'base_price' => 180.00,
                'price_tiers' => [
                    ['min_qty' => 20, 'max_qty' => 49, 'price' => 180.00],
                    ['min_qty' => 50, 'max_qty' => 99, 'price' => 145.00],
                    ['min_qty' => 100, 'max_qty' => null, 'price' => 120.00],
                ],
                'unit' => 'piece',
                'unit_ar' => 'قطعة',
                'is_available' => true,
                'origin_country' => 'China',
                'origin_country_ar' => 'الصين',
            ]);

            SupplierProduct::create([
                'supplier_id' => $electronicsSupplier->id,
                'name' => 'Phone Stand Holder Adjustable',
                'name_ar' => 'حامل موبايل قابل للتعديل',
                'category' => 'electronics',
                'description' => 'Aluminum alloy adjustable phone/tablet stand',
                'description_ar' => 'حامل ألمنيوم قابل للتعديل للموبايل والتابلت',
                'min_order_quantity' => 30,
                'base_price' => 65.00,
                'price_tiers' => [
                    ['min_qty' => 30, 'max_qty' => 99, 'price' => 65.00],
                    ['min_qty' => 100, 'max_qty' => 299, 'price' => 50.00],
                    ['min_qty' => 300, 'max_qty' => null, 'price' => 40.00],
                ],
                'unit' => 'piece',
                'unit_ar' => 'قطعة',
                'is_available' => true,
            ]);
        }

        if ($toolsSupplier) {
            // Tools Products
            SupplierProduct::create([
                'supplier_id' => $toolsSupplier->id,
                'name' => 'Professional Screwdriver Set 32pcs',
                'name_ar' => 'طقم مفكات احترافي 32 قطعة',
                'category' => 'tools',
                'description' => 'Complete set with magnetic tips, various sizes',
                'description_ar' => 'طقم كامل برؤوس مغناطيسية، أحجام متنوعة',
                'min_order_quantity' => 24,
                'base_price' => 120.00,
                'price_tiers' => [
                    ['min_qty' => 24, 'max_qty' => 47, 'price' => 120.00],
                    ['min_qty' => 48, 'max_qty' => 95, 'price' => 95.00],
                    ['min_qty' => 96, 'max_qty' => null, 'price' => 80.00],
                ],
                'unit' => 'box',
                'unit_ar' => 'صندوق',
                'is_available' => true,
                'origin_country' => 'China',
                'origin_country_ar' => 'الصين',
            ]);

            SupplierProduct::create([
                'supplier_id' => $toolsSupplier->id,
                'name' => 'Digital Multimeter',
                'name_ar' => 'أفوميتر رقمي',
                'category' => 'tools',
                'description' => 'LCD display, auto-ranging, voltage/current/resistance',
                'description_ar' => 'شاشة LCD، نطاق تلقائي، قياس الفولت/الأمبير/المقاومة',
                'min_order_quantity' => 12,
                'base_price' => 85.00,
                'price_tiers' => [
                    ['min_qty' => 12, 'max_qty' => 35, 'price' => 85.00],
                    ['min_qty' => 36, 'max_qty' => 99, 'price' => 70.00],
                    ['min_qty' => 100, 'max_qty' => null, 'price' => 55.00],
                ],
                'unit' => 'piece',
                'unit_ar' => 'قطعة',
                'is_available' => true,
            ]);
        }

        if ($fashionSupplier) {
            // Fashion Products
            SupplierProduct::create([
                'supplier_id' => $fashionSupplier->id,
                'name' => 'Stainless Steel Watch',
                'name_ar' => 'ساعة ستانلس ستيل',
                'category' => 'fashion',
                'description' => 'Elegant stainless steel watch with date display',
                'description_ar' => 'ساعة أنيقة ستانلس ستيل مع عرض التاريخ',
                'min_order_quantity' => 10,
                'base_price' => 250.00,
                'price_tiers' => [
                    ['min_qty' => 10, 'max_qty' => 29, 'price' => 250.00],
                    ['min_qty' => 30, 'max_qty' => 99, 'price' => 200.00],
                    ['min_qty' => 100, 'max_qty' => null, 'price' => 165.00],
                ],
                'unit' => 'piece',
                'unit_ar' => 'قطعة',
                'is_available' => true,
            ]);

            SupplierProduct::create([
                'supplier_id' => $fashionSupplier->id,
                'name' => 'Leather Crossbody Bag',
                'name_ar' => 'شنطة كروس جلد',
                'category' => 'fashion',
                'description' => 'PU leather crossbody bag, multiple colors available',
                'description_ar' => 'شنطة كروس جلد صناعي، متوفرة بألوان متعددة',
                'min_order_quantity' => 6,
                'base_price' => 180.00,
                'price_tiers' => [
                    ['min_qty' => 6, 'max_qty' => 23, 'price' => 180.00],
                    ['min_qty' => 24, 'max_qty' => 59, 'price' => 150.00],
                    ['min_qty' => 60, 'max_qty' => null, 'price' => 120.00],
                ],
                'unit' => 'piece',
                'unit_ar' => 'قطعة',
                'is_available' => true,
            ]);
        }

        if ($carSupplier) {
            // Car Accessories Products
            SupplierProduct::create([
                'supplier_id' => $carSupplier->id,
                'name' => 'Car Phone Mount Magnetic',
                'name_ar' => 'حامل موبايل مغناطيسي للسيارة',
                'category' => 'car_accessories',
                'description' => '360° rotation strong magnetic mount for dashboard',
                'description_ar' => 'حامل مغناطيسي قوي 360 درجة للتابلوه',
                'min_order_quantity' => 24,
                'base_price' => 55.00,
                'price_tiers' => [
                    ['min_qty' => 24, 'max_qty' => 99, 'price' => 55.00],
                    ['min_qty' => 100, 'max_qty' => 299, 'price' => 42.00],
                    ['min_qty' => 300, 'max_qty' => null, 'price' => 35.00],
                ],
                'unit' => 'piece',
                'unit_ar' => 'قطعة',
                'is_available' => true,
            ]);

            SupplierProduct::create([
                'supplier_id' => $carSupplier->id,
                'name' => 'LED Interior Light Strip 5m',
                'name_ar' => 'شريط LED داخلي 5 متر',
                'category' => 'car_accessories',
                'description' => 'RGB LED strip with remote control and music sync',
                'description_ar' => 'شريط LED RGB مع ريموت وتزامن مع الموسيقى',
                'min_order_quantity' => 12,
                'base_price' => 95.00,
                'price_tiers' => [
                    ['min_qty' => 12, 'max_qty' => 35, 'price' => 95.00],
                    ['min_qty' => 36, 'max_qty' => 99, 'price' => 75.00],
                    ['min_qty' => 100, 'max_qty' => null, 'price' => 60.00],
                ],
                'unit' => 'piece',
                'unit_ar' => 'قطعة',
                'is_available' => true,
            ]);
        }

        if ($homeSupplier) {
            // Home & Kitchen Products
            SupplierProduct::create([
                'supplier_id' => $homeSupplier->id,
                'name' => 'Silicone Kitchen Utensil Set 10pcs',
                'name_ar' => 'طقم أدوات مطبخ سيليكون 10 قطع',
                'category' => 'home',
                'description' => 'Heat resistant silicone cooking utensils with wooden handles',
                'description_ar' => 'أدوات طبخ سيليكون مقاومة للحرارة بمقابض خشبية',
                'min_order_quantity' => 12,
                'base_price' => 150.00,
                'price_tiers' => [
                    ['min_qty' => 12, 'max_qty' => 47, 'price' => 150.00],
                    ['min_qty' => 48, 'max_qty' => 119, 'price' => 120.00],
                    ['min_qty' => 120, 'max_qty' => null, 'price' => 95.00],
                ],
                'unit' => 'box',
                'unit_ar' => 'صندوق',
                'is_available' => true,
            ]);

            SupplierProduct::create([
                'supplier_id' => $homeSupplier->id,
                'name' => 'Drawer Organizer Set',
                'name_ar' => 'طقم منظم أدراج',
                'category' => 'home',
                'description' => 'Stackable drawer dividers, 8 pieces various sizes',
                'description_ar' => 'فواصل أدراج قابلة للتكديس، 8 قطع أحجام مختلفة',
                'min_order_quantity' => 24,
                'base_price' => 85.00,
                'price_tiers' => [
                    ['min_qty' => 24, 'max_qty' => 71, 'price' => 85.00],
                    ['min_qty' => 72, 'max_qty' => 143, 'price' => 68.00],
                    ['min_qty' => 144, 'max_qty' => null, 'price' => 55.00],
                ],
                'unit' => 'box',
                'unit_ar' => 'صندوق',
                'is_available' => true,
            ]);
        }
    }
}
