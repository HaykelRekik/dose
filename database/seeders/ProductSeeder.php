<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ProductOptionGroupType;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // For idempotency, we clear the tables in the correct order to avoid foreign key constraint issues.
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Product::truncate();
        DB::table('product_option_groups')->truncate();
        DB::table('product_options')->truncate();
        DB::table('category_product')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Fetch all categories and map their English names to their IDs for easy lookup.
        $categories = Category::pluck('id', 'name_en')->all();

        $productsData = $this->getProductsData();

        // A progress bar is nice for seeding a large amount of data.
        $this->command->getOutput()->progressStart(count($productsData));

        foreach ($productsData as $productData) {
            /** @var Product $product */
            $product = Product::create([
                'name_en' => $productData['name_en'],
                'name_ar' => $productData['name_ar'],
                'description_en' => $productData['description_en'],
                'description_ar' => $productData['description_ar'],
                'price' => $productData['price'],
                'estimated_preparation_time' => $productData['estimated_preparation_time'],
                'image_url' => $productData['image_url'], // Assumes images are in storage/app/public/products
                'is_active' => true,
            ]);

            // Attach categories to the product
            $categoryIds = collect($productData['categories'])->map(fn($catName) => $categories[$catName] ?? null)->filter()->all();
            if (!empty($categoryIds)) {
                $product->categories()->attach($categoryIds);
            }

            // Create option groups and their options
            foreach ($productData['option_groups'] as $groupData) {
                $group = $product->optionGroups()->create([
                    'name_en' => $groupData['name_en'],
                    'name_ar' => $groupData['name_ar'],
                    'type' => $groupData['type'],
                    'is_required' => $groupData['is_required'],
                ]);

                foreach ($groupData['options'] as $optionData) {
                    $group->options()->create($optionData);
                }
            }

            $this->command->getOutput()->progressAdvance();
        }

        $this->command->getOutput()->progressFinish();
    }

    /**
     * Provides a structured array of realistic product data.
     *
     * @return array
     */
    private function getProductsData(): array
    {
        return [
            // Product 1: Coffee
            [
                'name_en' => 'Caramel Macchiato',
                'name_ar' => 'كراميل ماكياتو',
                'description_en' => 'Freshly steamed milk with vanilla-flavored syrup marked with espresso and topped with a caramel drizzle.',
                'description_ar' => 'حليب طازج مبخر مع شراب بنكهة الفانيليا، ممزوج بالإسبريسو ومغطى برذاذ الكراميل.',
                'price' => 15.00,
                'estimated_preparation_time' => 5, // in minutes
                'image_url' => 'products/caramel-macchiato.jpg',
                'categories' => ['Espresso Based', 'Iced Drinks'],
                'option_groups' => [
                    [
                        'name_en' => 'Size', 'name_ar' => 'الحجم',
                        'type' => ProductOptionGroupType::SINGLE_SELECT, 'is_required' => true,
                        'options' => [
                            ['name_en' => 'Small', 'name_ar' => 'صغير', 'extra_price' => 0.00],
                            ['name_en' => 'Medium', 'name_ar' => 'وسط', 'extra_price' => 2.00],
                            ['name_en' => 'Large', 'name_ar' => 'كبير', 'extra_price' => 4.00],
                        ],
                    ],
                    [
                        'name_en' => 'Milk Type', 'name_ar' => 'نوع الحليب',
                        'type' => ProductOptionGroupType::SINGLE_SELECT, 'is_required' => true,
                        'options' => [
                            ['name_en' => 'Whole Milk', 'name_ar' => 'حليب كامل الدسم', 'extra_price' => 0.00],
                            ['name_en' => 'Skim Milk', 'name_ar' => 'حليب خالي الدسم', 'extra_price' => 0.00],
                            ['name_en' => 'Oat Milk', 'name_ar' => 'حليب الشوفان', 'extra_price' => 3.00, 'is_active' => true],
                            ['name_en' => 'Almond Milk', 'name_ar' => 'حليب اللوز', 'extra_price' => 3.00, 'is_active' => false], // Example of an inactive option
                        ],
                    ],
                    [
                        'name_en' => 'Add-ons', 'name_ar' => 'الإضافات',
                        'type' => ProductOptionGroupType::MULTI_SELECT, 'is_required' => false,
                        'options' => [
                            ['name_en' => 'Extra Espresso Shot', 'name_ar' => 'جرعة إسبريسو إضافية', 'extra_price' => 5.00],
                            ['name_en' => 'Whipped Cream', 'name_ar' => 'كريمة مخفوقة', 'extra_price' => 2.50],
                        ],
                    ],
                ],
            ],
            // Product 2: Burger
            [
                'name_en' => 'Classic Angus Burger',
                'name_ar' => 'برجر أنجوس كلاسيك',
                'description_en' => 'A juicy Angus beef patty with cheddar cheese, lettuce, tomato, and our special sauce in a brioche bun.',
                'description_ar' => 'قطعة لحم أنجوس طرية مع جبنة شيدر، خس، طماطم، وصلصتنا الخاصة في خبز بريوش.',
                'price' => 35.00,
                'estimated_preparation_time' => 15,
                'image_url' => 'products/classic-burger.jpg',
                'categories' => ['Gourmet Burgers'],
                'option_groups' => [
                    [
                        'name_en' => 'Cooking Preference', 'name_ar' => 'درجة الطهي',
                        'type' => ProductOptionGroupType::SINGLE_SELECT, 'is_required' => true,
                        'options' => [
                            ['name_en' => 'Medium', 'name_ar' => 'متوسط', 'extra_price' => 0],
                            ['name_en' => 'Medium Well', 'name_ar' => 'متوسطة الإستواء', 'extra_price' => 0],
                            ['name_en' => 'Well Done', 'name_ar' => 'جيدة الإستواء', 'extra_price' => 0],
                        ],
                    ],
                    [
                        'name_en' => 'Side Dish', 'name_ar' => 'الطبق الجانبي',
                        'type' => ProductOptionGroupType::SINGLE_SELECT, 'is_required' => true,
                        'options' => [
                            ['name_en' => 'French Fries', 'name_ar' => 'بطاطس مقلية', 'extra_price' => 0],
                            ['name_en' => 'Side Salad', 'name_ar' => 'سلطة جانبية', 'extra_price' => 5.00],
                        ],
                    ],
                    [
                        'name_en' => 'Extra Toppings', 'name_ar' => 'إضافات',
                        'type' => ProductOptionGroupType::MULTI_SELECT, 'is_required' => false,
                        'options' => [
                            ['name_en' => 'Extra Cheese', 'name_ar' => 'جبنة إضافية', 'extra_price' => 4.00],
                            ['name_en' => 'Bacon', 'name_ar' => 'بيكون', 'extra_price' => 6.00],
                            ['name_en' => 'Mushroom', 'name_ar' => 'فطر', 'extra_price' => 3.00],
                        ],
                    ],
                ],
            ],
            // Product 3: Sandwich
            [
                'name_en' => 'Grilled Chicken Sandwich',
                'name_ar' => 'ساندويتش دجاج مشوي',
                'description_en' => 'Grilled chicken breast, provolone cheese, and fresh greens on toasted sourdough.',
                'description_ar' => 'صدر دجاج مشوي، جبنة بروفولون، وخضروات طازجة على خبز الساوردو المحمص.',
                'price' => 28.00,
                'estimated_preparation_time' => 10,
                'image_url' => 'products/chicken-sandwich.jpg',
                'categories' => ['Sandwiches'],
                'option_groups' => [
                    [
                        'name_en' => 'Bread Choice', 'name_ar' => 'اختر الخبز',
                        'type' => ProductOptionGroupType::SINGLE_SELECT, 'is_required' => true,
                        'options' => [
                            ['name_en' => 'Sourdough', 'name_ar' => 'ساوردو', 'extra_price' => 0],
                            ['name_en' => 'Whole Wheat', 'name_ar' => 'قمح كامل', 'extra_price' => 0],
                            ['name_en' => 'Ciabatta', 'name_ar' => 'شياباتا', 'extra_price' => 2.00],
                        ],
                    ],
                ],
            ],
            [
                'name_en' => 'Chocolate Fudge Cake',
                'name_ar' => 'كيكة الشوكولاتة فدج',
                'description_en' => 'A rich and moist chocolate cake layered with decadent fudge frosting.',
                'description_ar' => 'كيكة شوكولاتة غنية ورطبة مغطاة بطبقات من كريمة الفدج الفاخرة.',
                'price' => 22.00,
                'estimated_preparation_time' => 2,
                'image_url' => 'products/chocolate-cake.jpg',
                'categories' => ['Cakes'],
                'option_groups' => [
                    [
                        'name_en' => 'Add-ons', 'name_ar' => 'الإضافات',
                        'type' => ProductOptionGroupType::MULTI_SELECT, 'is_required' => false,
                        'options' => [
                            ['name_en' => 'Scoop of Ice Cream', 'name_ar' => 'كرة آيس كريم', 'extra_price' => 7.00],
                            ['name_en' => 'Extra Chocolate Sauce', 'name_ar' => 'صلصة شوكولاتة إضافية', 'extra_price' => 3.00],
                        ],
                    ],
                ],
            ],
        ];
    }
}
