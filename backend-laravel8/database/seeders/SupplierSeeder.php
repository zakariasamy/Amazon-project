<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    public function run()
    {
        $suppliers = [
            [
                'name' => 'Cairo Electronics Hub',
                'name_ar' => 'مركز القاهرة للإلكترونيات',
                'category' => 'electronics',
                'description' => 'Wholesale electronics supplier specializing in mobile accessories, chargers, cables, and smart home devices. Best prices in the market.',
                'description_ar' => 'مورد جملة للإلكترونيات متخصص في إكسسوارات الموبايل والشواحن والكابلات والأجهزة المنزلية الذكية. أفضل الأسعار في السوق.',
                'phone' => '+201001234567',
                'whatsapp' => '201001234567',
                'telegram_group_link' => 'https://t.me/cairoelectronics',
                'website' => 'https://cairoelectronics.example.com',
                'location' => 'El-Attaba, Cairo',
                'location_ar' => 'العتبة، القاهرة',
                'is_verified' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Mosky Wholesale Market',
                'name_ar' => 'سوق الموسكي للجملة',
                'category' => 'fashion',
                'description' => 'Fashion and accessories wholesaler. Bags, jewelry, watches, and clothing items at bulk prices.',
                'description_ar' => 'تاجر جملة للموضة والإكسسوارات. شنط ومجوهرات وساعات وملابس بأسعار الجملة.',
                'phone' => '+201112345678',
                'whatsapp' => '201112345678',
                'telegram_group_link' => 'https://t.me/moskywholesale',
                'location' => 'El-Mosky, Cairo',
                'location_ar' => 'الموسكي، القاهرة',
                'is_verified' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Tool Masters Egypt',
                'name_ar' => 'أساتذة العدة مصر',
                'category' => 'tools',
                'description' => 'Hand tools, power tools, and construction equipment. Direct import from China with competitive wholesale prices.',
                'description_ar' => 'عدد يدوية وكهربائية ومعدات بناء. استيراد مباشر من الصين بأسعار جملة تنافسية.',
                'phone' => '+201223456789',
                'whatsapp' => '201223456789',
                'website' => 'https://toolmasters.example.com',
                'location' => 'El-Husseinia, Cairo',
                'location_ar' => 'الحسينية، القاهرة',
                'is_verified' => true,
                'is_active' => true,
            ],
            [
                'name' => 'AutoParts Egypt',
                'name_ar' => 'قطع غيار السيارات مصر',
                'category' => 'car_accessories',
                'description' => 'Car accessories, LED lights, car phone holders, dash cams, and interior accessories for all car models.',
                'description_ar' => 'إكسسوارات سيارات ولمبات LED وحوامل موبايل وكاميرات داش وإكسسوارات داخلية لجميع موديلات السيارات.',
                'phone' => '+201334567890',
                'whatsapp' => '201334567890',
                'telegram_group_link' => 'https://t.me/autopartseg',
                'location' => 'El-Tawfikiya, Cairo',
                'location_ar' => 'التوفيقية، القاهرة',
                'is_verified' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Home & Kitchen World',
                'name_ar' => 'عالم المنزل والمطبخ',
                'category' => 'home',
                'description' => 'Kitchen gadgets, home organization products, storage solutions, and household items at wholesale prices.',
                'description_ar' => 'أدوات مطبخ ومنتجات تنظيم المنزل وحلول تخزين ومستلزمات منزلية بأسعار الجملة.',
                'phone' => '+201445678901',
                'whatsapp' => '201445678901',
                'telegram_group_link' => 'https://t.me/homekitchenworld',
                'website' => 'https://homekitchen.example.com',
                'location' => 'El-Mosky, Cairo',
                'location_ar' => 'الموسكي، القاهرة',
                'is_verified' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Sports Zone Wholesale',
                'name_ar' => 'منطقة الرياضة للجملة',
                'category' => 'sports',
                'description' => 'Sports equipment, gym accessories, fitness products, and outdoor gear. Direct import from factories.',
                'description_ar' => 'معدات رياضية وإكسسوارات جيم ومنتجات لياقة ومعدات خارجية. استيراد مباشر من المصانع.',
                'phone' => '+201556789012',
                'telegram_group_link' => 'https://t.me/sportszoneeg',
                'location' => 'El-Attaba, Cairo',
                'location_ar' => 'العتبة، القاهرة',
                'is_verified' => true,
                'is_active' => true,
            ],
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::create($supplierData);
        }
    }
}
