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
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Product::truncate();
        DB::table('product_option_groups')->truncate();
        DB::table('product_options')->truncate();
        DB::table('category_product')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $categories = Category::pluck('id', 'name_en')->all();

        $productsData = $this->getProductsData();

        foreach ($productsData as $productData) {
            /** @var Product $product */
            $product = Product::create([
                'name_en' => $productData['name_en'],
                'name_ar' => $productData['name_ar'],
                'description_en' => $productData['description_en'],
                'description_ar' => $productData['description_ar'],
                'price' => $productData['price'],
                'estimated_preparation_time' => $productData['estimated_preparation_time'],
                'image_url' => $productData['image_url'],
                'is_active' => true,
            ]);

            $categoryIds = collect($productData['categories'])->map(fn ($catName) => $categories[$catName] ?? null)->filter()->all();
            if ( ! empty($categoryIds)) {
                $product->categories()->attach($categoryIds);
            }

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
        }
    }

    /**
     * Provides a structured array of realistic product data.
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
                            ['name_en' => 'Almond Milk', 'name_ar' => 'حليب اللوز', 'extra_price' => 3.00, 'is_active' => false],
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
            // Product 4: Cake
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
            // Product 5: Juice
            [
                'name_en' => 'Fresh Orange Juice',
                'name_ar' => 'عصير برتقال طازج',
                'description_en' => 'Squeezed daily from fresh, ripe oranges. A pure taste of sunshine.',
                'description_ar' => 'معصور يوميًا من برتقال طازج وناضج. طعم نقي من أشعة الشمس.',
                'price' => 18.00,
                'estimated_preparation_time' => 3,
                'image_url' => 'products/orange-juice.jpg',
                'categories' => ['Fresh Juices'],
                'option_groups' => [
                    [
                        'name_en' => 'Size', 'name_ar' => 'الحجم',
                        'type' => ProductOptionGroupType::SINGLE_SELECT, 'is_required' => true,
                        'options' => [
                            ['name_en' => 'Regular', 'name_ar' => 'عادي', 'extra_price' => 0],
                            ['name_en' => 'Large', 'name_ar' => 'كبير', 'extra_price' => 4.00],
                        ],
                    ],
                ],
            ],
            // Product 6: Salad
            [
                'name_en' => 'Classic Caesar Salad',
                'name_ar' => 'سلطة سيزر كلاسيك',
                'description_en' => 'Crisp romaine lettuce, parmesan cheese, crunchy croutons, and our signature Caesar dressing.',
                'description_ar' => 'خس روماني مقرمش، جبنة بارميزان، خبز محمص، وصلصة السيزر الخاصة بنا.',
                'price' => 32.00,
                'estimated_preparation_time' => 8,
                'image_url' => 'products/caesar-salad.jpg',
                'categories' => ['Salads'],
                'option_groups' => [
                    [
                        'name_en' => 'Add Protein', 'name_ar' => 'إضافة بروتين',
                        'type' => ProductOptionGroupType::MULTI_SELECT, 'is_required' => false,
                        'options' => [
                            ['name_en' => 'Grilled Chicken', 'name_ar' => 'دجاج مشوي', 'extra_price' => 10.00],
                            ['name_en' => 'Grilled Shrimp', 'name_ar' => 'روبيان مشوي', 'extra_price' => 15.00],
                        ],
                    ],
                ],
            ],
            // Product 7: Breakfast
            [
                'name_en' => 'Scrambled Eggs on Toast',
                'name_ar' => 'بيض مخفوق على التوست',
                'description_en' => 'Creamy scrambled eggs served on a slice of your favorite toasted bread.',
                'description_ar' => 'بيض مخفوق كريمي يقدم على شريحة من خبز التوست المفضل لديك.',
                'price' => 25.00,
                'estimated_preparation_time' => 12,
                'image_url' => 'products/scrambled-eggs.jpg',
                'categories' => ['Breakfast'],
                'option_groups' => [
                    [
                        'name_en' => 'Bread Choice', 'name_ar' => 'اختر الخبز',
                        'type' => ProductOptionGroupType::SINGLE_SELECT, 'is_required' => true,
                        'options' => [
                            ['name_en' => 'Sourdough', 'name_ar' => 'ساوردو', 'extra_price' => 0],
                            ['name_en' => 'Multigrain', 'name_ar' => 'حبوب متعددة', 'extra_price' => 2.00],
                            ['name_en' => 'Brioche', 'name_ar' => 'بريوش', 'extra_price' => 3.00],
                        ],
                    ],
                    [
                        'name_en' => 'Add-ons', 'name_ar' => 'إضافات',
                        'type' => ProductOptionGroupType::MULTI_SELECT, 'is_required' => false,
                        'options' => [
                            ['name_en' => 'Avocado Slices', 'name_ar' => 'شرائح أفوكادو', 'extra_price' => 8.00],
                            ['name_en' => 'Sautéed Mushrooms', 'name_ar' => 'فطر سوتيه', 'extra_price' => 5.00],
                        ],
                    ],
                ],
            ],
            // Product 8: Croissant
            [
                'name_en' => 'Almond Croissant',
                'name_ar' => 'كرواسان باللوز',
                'description_en' => 'A flaky butter croissant with a sweet almond paste filling, topped with toasted almonds.',
                'description_ar' => 'كرواسان زبدة هش بحشوة عجينة اللوز الحلوة، مغطى باللوز المحمص.',
                'price' => 14.00,
                'estimated_preparation_time' => 2,
                'image_url' => 'products/almond-croissant.jpg',
                'categories' => ['Croissants'],
                'option_groups' => [
                    [
                        'name_en' => 'Preparation', 'name_ar' => 'التحضير',
                        'type' => ProductOptionGroupType::SINGLE_SELECT, 'is_required' => false,
                        'options' => [
                            ['name_en' => 'Served as is', 'name_ar' => 'كما هو', 'extra_price' => 0],
                            ['name_en' => 'Warmed up', 'name_ar' => 'تسخين', 'extra_price' => 0],
                        ],
                    ],
                ],
            ],
            // Product 9: Iced Coffee
            [
                'name_en' => 'Iced Americano',
                'name_ar' => 'أمريكانو مثلج',
                'description_en' => 'Rich, full-bodied espresso shots combined with water and served over ice.',
                'description_ar' => 'جرعات إسبريسو غنية وكاملة القوام ممزوجة بالماء وتقدم فوق الثلج.',
                'price' => 12.00,
                'estimated_preparation_time' => 3,
                'image_url' => 'products/iced-americano.jpg',
                'categories' => ['Iced Drinks', 'Espresso Based'],
                'option_groups' => [
                    [
                        'name_en' => 'Size', 'name_ar' => 'الحجم',
                        'type' => ProductOptionGroupType::SINGLE_SELECT, 'is_required' => true,
                        'options' => [
                            ['name_en' => 'Medium', 'name_ar' => 'وسط', 'extra_price' => 0],
                            ['name_en' => 'Large', 'name_ar' => 'كبير', 'extra_price' => 3.00],
                        ],
                    ],
                ],
            ],
            // Product 10: Brewed Coffee
            [
                'name_en' => 'V60 Pour-Over',
                'name_ar' => 'قهوة مقطرة V60',
                'description_en' => 'A clean, crisp, and flavorful cup of coffee, hand-brewed to order. Choose your favorite SINGLE_SELECT-origin bean.',
                'description_ar' => 'فنجان قهوة نظيف ونقي ومليء بالنكهة، يتم تحضيره يدويًا حسب الطلب. اختر حبوب القهوة ذات المنشأ الواحد المفضلة لديك.',
                'price' => 25.00,
                'estimated_preparation_time' => 7,
                'image_url' => 'products/v60-coffee.jpg',
                'categories' => ['Brewed Coffee', 'Coffee Beans'],
                'option_groups' => [
                    [
                        'name_en' => 'Coffee Bean Selection', 'name_ar' => 'اختر نوع حبوب القهوة',
                        'type' => ProductOptionGroupType::SINGLE_SELECT, 'is_required' => true,
                        'options' => [
                            ['name_en' => 'Ethiopian Yirgacheffe', 'name_ar' => 'إثيوبي يرقاشيف', 'extra_price' => 0],
                            ['name_en' => 'Colombian Supremo', 'name_ar' => 'كولومبي سوبريمو', 'extra_price' => 2.00],
                            ['name_en' => 'Kenyan AA', 'name_ar' => 'كيني AA', 'extra_price' => 4.00],
                        ],
                    ],
                ],
            ],
        ];
    }
}
