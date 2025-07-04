<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds for a flat list of categories.
     */
    public function run(): void
    {
        DB::table('categories')->truncate();

        $categories = $this->getCategoryData();
        $dataToInsert = [];

        foreach ($categories as $index => $category) {
            $dataToInsert[] = [
                'name_en' => $category['en'],
                'name_ar' => $category['ar'],
                'position' => $index,
                'slug' => Str::slug($category['en']),
                'is_active' => fake()->boolean(chanceOfGettingTrue: 80),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ( ! empty($dataToInsert)) {
            DB::table('categories')->insert($dataToInsert);
        }
    }

    /**
     * Defines a flat list of all categories.
     */
    private function getCategoryData(): array
    {
        return [
            ['en' => 'Espresso Based', 'ar' => 'مشروبات الإسبريسو'],
            ['en' => 'Brewed Coffee', 'ar' => 'القهوة المقطرة'],
            ['en' => 'Iced Drinks', 'ar' => 'المشروبات الباردة'],
            ['en' => 'Tea & Infusions', 'ar' => 'الشاي والنقيع'],
            ['en' => 'Fresh Juices', 'ar' => 'عصائر طازجة'],
            ['en' => 'Breakfast', 'ar' => 'الفطور'],
            ['en' => 'Sandwiches', 'ar' => 'الساندويتشات'],
            ['en' => 'Gourmet Burgers', 'ar' => 'برجر جورميه'],
            ['en' => 'Salads', 'ar' => 'السلطات'],
            ['en' => 'Sides', 'ar' => 'الأطباق الجانبية'],
            ['en' => 'Cakes', 'ar' => 'الكيك'],
            ['en' => 'Cookies', 'ar' => 'الكوكيز'],
            ['en' => 'Croissants', 'ar' => 'الكرواسان'],
            ['en' => 'Muffins', 'ar' => 'المافن'],
            ['en' => 'Coffee Beans', 'ar' => 'حبوب القهوة'],
            ['en' => 'Brewing Tools', 'ar' => 'أدوات التحضير'],
        ];
    }
}
