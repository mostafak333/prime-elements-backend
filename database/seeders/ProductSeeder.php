<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Models\ProductImage;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // School Books with specific categories
        $schoolCategories = Category::where('title_id', 1)->get();
        $schoolBooks = [
            [
                'name_en' => 'Mathematics Grade 6',
                'name_ar' => 'الرياضيات الصف السادس',
                'category_slug' => 'grade-6',
                'short_description_en' => 'Comprehensive mathematics textbook for grade 6 students',
                'short_description_ar' => 'كتاب رياضيات شامل لطلاب الصف السادس',
                'price' => 49.99,
                'discount' => 10.00,
                'stock' => 100,
                'publisher' => 'Education Publishing House',
                'book_title' => 'Mathematics Grade 6',
                'author' => 'Dr. Ahmed Al-Saud',
                'language' => 'English',
                'pages' => 350,
                'isbn' => '978-3-16-148410-0',
                'publication_date' => '2024-01-15',
                'description_en' => 'This book covers all essential topics for grade 6 mathematics including algebra, geometry, and statistics.',
                'description_ar' => 'يغطي هذا الكتاب جميع المواضيع الأساسية لرياضيات الصف السادس بما في ذلك الجبر والهندسة والإحصاء.',
            ],
            [
                'name_en' => 'Science Grade 7',
                'name_ar' => 'العلوم الصف السابع',
                'category_slug' => 'grade-7',
                'short_description_en' => 'Interactive science textbook for grade 7 students',
                'short_description_ar' => 'كتاب علوم تفاعلي لطلاب الصف السابع',
                'price' => 54.99,
                'discount' => 5.00,
                'stock' => 85,
                'publisher' => 'Scientific Publications',
                'book_title' => 'Science Grade 7',
                'author' => 'Prof. Sarah Johnson',
                'language' => 'English',
                'pages' => 400,
                'isbn' => '978-3-16-148411-7',
                'publication_date' => '2024-02-20',
                'description_en' => 'Explore the wonders of science with this interactive textbook covering biology, chemistry, and physics.',
                'description_ar' => 'استكشف عجائب العلوم مع هذا الكتاب التفاعلي الذي يغطي الأحياء والكيمياء والفيزياء.',
            ],
            [
                'name_en' => 'English Language Grade 8',
                'name_ar' => 'اللغة الإنجليزية الصف الثامن',
                'category_slug' => 'grade-8',
                'short_description_en' => 'Comprehensive English language textbook for grade 8',
                'short_description_ar' => 'كتاب شامل للغة الإنجليزية لطلاب الصف الثامن',
                'price' => 44.99,
                'discount' => 0.00,
                'stock' => 120,
                'publisher' => 'Language Learning Press',
                'book_title' => 'English Language Grade 8',
                'author' => 'Dr. Michael Brown',
                'language' => 'English',
                'pages' => 320,
                'isbn' => '978-3-16-148412-4',
                'publication_date' => '2024-03-10',
                'description_en' => 'Enhance English language skills with this comprehensive textbook covering grammar, vocabulary, and writing.',
                'description_ar' => 'عزز مهارات اللغة الإنجليزية مع هذا الكتاب الشامل الذي يغطي القواعد والمفردات والكتابة.',
            ],
            [
                'name_en' => 'History Grade 9',
                'name_ar' => 'التاريخ الصف التاسع',
                'category_slug' => 'grade-9',
                'short_description_en' => 'A journey through world history for grade 9',
                'short_description_ar' => 'رحلة عبر تاريخ العالم للصف التاسع',
                'price' => 59.99,
                'discount' => 15.00,
                'stock' => 70,
                'publisher' => 'Historical Books Publishing',
                'book_title' => 'History Grade 9',
                'author' => 'Dr. James Wilson',
                'language' => 'English',
                'pages' => 450,
                'isbn' => '978-3-16-148413-1',
                'publication_date' => '2024-04-05',
                'description_en' => 'Explore the fascinating events and civilizations that shaped our world in this comprehensive history textbook.',
                'description_ar' => 'استكشف الأحداث والحضارات الرائعة التي شكلت عالمنا في كتاب التاريخ الشامل هذا.',
            ],
        ];

        // University Books (Children categories)
        $universityCategories = Category::where('title_id', 2)->whereNotNull('parent_id')->get();
        $universityBooks = [
            [
                'name_en' => 'Introduction to Computer Science',
                'name_ar' => 'مقدمة في علوم الحاسوب',
                'category_slug' => null, // Will be assigned randomly
                'short_description_en' => 'Fundamental concepts of computer science for university students',
                'short_description_ar' => 'مفاهيم أساسية في علوم الحاسوب لطلاب الجامعة',
                'price' => 89.99,
                'discount' => 20.00,
                'stock' => 50,
                'publisher' => 'Academic Press',
                'book_title' => 'Introduction to Computer Science',
                'author' => 'Dr. Robert Chen',
                'language' => 'English',
                'pages' => 600,
                'isbn' => '978-3-16-148414-8',
                'publication_date' => '2024-05-15',
                'description_en' => 'A comprehensive introduction to computer science covering programming, algorithms, data structures, and more.',
                'description_ar' => 'مقدمة شاملة لعلوم الحاسوب تغطي البرمجة والخوارزميات وهياكل البيانات والمزيد.',
            ],
            [
                'name_en' => 'Advanced Physics',
                'name_ar' => 'الفيزياء المتقدمة',
                'category_slug' => null,
                'short_description_en' => 'Advanced physics concepts for science majors',
                'short_description_ar' => 'مفاهيم فيزياء متقدمة لتخصصات العلوم',
                'price' => 99.99,
                'discount' => 10.00,
                'stock' => 40,
                'publisher' => 'Science Publishers',
                'book_title' => 'Advanced Physics',
                'author' => 'Prof. Maria Garcia',
                'language' => 'English',
                'pages' => 700,
                'isbn' => '978-3-16-148415-5',
                'publication_date' => '2024-06-20',
                'description_en' => 'Dive deep into advanced physics topics including quantum mechanics, relativity, and particle physics.',
                'description_ar' => 'تعمق في مواضيع الفيزياء المتقدمة بما في ذلك ميكانيكا الكم والنسبية وفيزياء الجسيمات.',
            ],
            [
                'name_en' => 'Business Administration Fundamentals',
                'name_ar' => 'أساسيات إدارة الأعمال',
                'category_slug' => null,
                'short_description_en' => 'Core concepts of business administration',
                'short_description_ar' => 'مفاهيم أساسية في إدارة الأعمال',
                'price' => 79.99,
                'discount' => 5.00,
                'stock' => 60,
                'publisher' => 'Business Publishing House',
                'book_title' => 'Business Administration Fundamentals',
                'author' => 'Dr. Lisa Thompson',
                'language' => 'English',
                'pages' => 500,
                'isbn' => '978-3-16-148416-2',
                'publication_date' => '2024-07-10',
                'description_en' => 'Learn the essential principles of business administration including management, marketing, and finance.',
                'description_ar' => 'تعلم المبادئ الأساسية لإدارة الأعمال بما في ذلك الإدارة والتسويق والمالية.',
            ],
        ];

        // Fiction Books
        $fictionCategories = Category::where('title_id', 4)->get();
        $fictionBooks = [
            [
                'name_en' => 'The Lost Kingdom',
                'name_ar' => 'المملكة المفقودة',
                'category_slug' => 'fantasy',
                'short_description_en' => 'An epic fantasy adventure in a magical kingdom',
                'short_description_ar' => 'مغامرة خيالية ملحمية في مملكة سحرية',
                'price' => 24.99,
                'discount' => 0.00,
                'stock' => 200,
                'publisher' => 'Fantasy World Publishing',
                'book_title' => 'The Lost Kingdom',
                'author' => 'Elena Rodriguez',
                'language' => 'English',
                'pages' => 380,
                'isbn' => '978-3-16-148418-6',
                'publication_date' => '2024-09-01',
                'description_en' => 'Join the heroes on their quest to find the lost kingdom and restore peace to the land.',
                'description_ar' => 'انضم إلى الأبطال في رحلتهم للعثور على المملكة المفقودة واستعادة السلام في الأرض.',
            ],
            [
                'name_en' => 'Shadows of the Past',
                'name_ar' => 'ظلال الماضي',
                'category_slug' => 'mystery',
                'short_description_en' => 'A gripping mystery thriller',
                'short_description_ar' => 'رواية إثارة وغموض مثيرة',
                'price' => 22.99,
                'discount' => 10.00,
                'stock' => 150,
                'publisher' => 'Mystery Press',
                'book_title' => 'Shadows of the Past',
                'author' => 'Mark Anderson',
                'language' => 'English',
                'pages' => 320,
                'isbn' => '978-3-16-148419-3',
                'publication_date' => '2024-10-15',
                'description_en' => 'A detective must uncover the truth behind a series of mysterious events that haunt a small town.',
                'description_ar' => 'يجب على المحقق كشف الحقيقة وراء سلسلة من الأحداث الغامضة التي تطارد بلدة صغيرة.',
            ],
            [
                'name_en' => 'Midnight Romance',
                'name_ar' => 'رومانسية منتصف الليل',
                'category_slug' => 'romance',
                'short_description_en' => 'A heartwarming love story',
                'short_description_ar' => 'قصة حب دافئة للقلب',
                'price' => 19.99,
                'discount' => 0.00,
                'stock' => 180,
                'publisher' => 'Romance Books',
                'book_title' => 'Midnight Romance',
                'author' => 'Sophie Williams',
                'language' => 'English',
                'pages' => 280,
                'isbn' => '978-3-16-148420-9',
                'publication_date' => '2024-11-20',
                'description_en' => 'Two strangers find love in the most unexpected place, proving that destiny works in mysterious ways.',
                'description_ar' => 'يجد غريبان الحب في أكثر الأماكن غير المتوقعة، مما يثبت أن القدر يعمل بطرق غامضة.',
            ],
            [
                'name_en' => 'Nightmare Hollow',
                'name_ar' => 'وادي الكوابيس',
                'category_slug' => 'horror',
                'short_description_en' => 'A terrifying horror story',
                'short_description_ar' => 'قصة رعب مرعبة',
                'price' => 21.99,
                'discount' => 5.00,
                'stock' => 130,
                'publisher' => 'Horror House',
                'book_title' => 'Nightmare Hollow',
                'author' => 'Stephen Black',
                'language' => 'English',
                'pages' => 300,
                'isbn' => '978-3-16-148421-6',
                'publication_date' => '2024-12-05',
                'description_en' => 'A group of friends discovers an ancient evil lurking in the abandoned hollow, where nightmares come to life.',
                'description_ar' => 'تكتشف مجموعة من الأصدقاء شرًا قديمًا كامنًا في الوادي المهجور، حيث تتحول الكوابيس إلى واقع.',
            ],
            [
                'name_en' => 'The Silent Witness',
                'name_ar' => 'الشاهد الصامت',
                'category_slug' => 'thriller',
                'short_description_en' => 'A gripping thriller that will keep you on the edge of your seat',
                'short_description_ar' => 'رواية إثارة ستجعلك على حافة مقعدك',
                'price' => 26.99,
                'discount' => 0.00,
                'stock' => 110,
                'publisher' => 'Thriller Books',
                'book_title' => 'The Silent Witness',
                'author' => 'David Clark',
                'language' => 'English',
                'pages' => 340,
                'isbn' => '978-3-16-148422-3',
                'publication_date' => '2024-12-20',
                'description_en' => 'A witness to a crime must stay silent to survive, but the truth will always find a way to come out.',
                'description_ar' => 'يجب على شاهد جريمة أن يظل صامتًا للبقاء على قيد الحياة، لكن الحقيقة ستجد دائمًا طريقة للخروج.',
            ],
        ];

        // Helper function to create product
        $createProduct = function ($bookData, $categoryId, $index) {
            $product = Product::create([
                'category_id' => $categoryId,
                'name_en' => $bookData['name_en'],
                'name_ar' => $bookData['name_ar'],
                'short_description_en' => $bookData['short_description_en'],
                'short_description_ar' => $bookData['short_description_ar'],
                'price' => $bookData['price'],
                'discount' => $bookData['discount'],
                'stock' => $bookData['stock'],
                'status' => 1,
                'is_new_arrival' => $index % 2 == 0 ? 1 : 0,
                'is_best_seller' => $index % 3 == 0 ? 1 : 0,
                'is_e_copy' => $index % 4 == 0 ? 1 : 0,
                'publisher' => $bookData['publisher'],
                'created_by' => 1,
                'updated_by' => 1,
            ]);

            ProductDetail::create([
                'product_id' => $product->id,
                'title_en' => $bookData['name_en'],
                'title_ar' => $bookData['name_ar'],
                'description_en' => $bookData['description_en'],
                'description_ar' => $bookData['description_ar'],
                'book_title' => $bookData['book_title'],
                'author' => $bookData['author'],
                'publisher' => $bookData['publisher'],
                'language' => $bookData['language'],
                'pages' => $bookData['pages'],
                'isbn' => $bookData['isbn'],
                'publication_date' => $bookData['publication_date'],
                'is_active' => 1,
            ]);

            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => 'assets/test.jpg',
            ]);

        };

        // Create School Books
        foreach ($schoolBooks as $index => $book) {
            $category = $schoolCategories->where('slug', $book['category_slug'])->first();
            if ($category) {
                $createProduct($book, $category->id, $index);
            }
        }

        // Create University Books (random categories)
        foreach ($universityBooks as $index => $book) {
            $randomCategory = $universityCategories->random();
            if ($randomCategory) {
                $createProduct($book, $randomCategory->id, $index);
            }
        }

        // Create Fiction Books
        foreach ($fictionBooks as $index => $book) {
            $category = $fictionCategories->where('slug', $book['category_slug'])->first();
            if ($category) {
                $createProduct($book, $category->id, $index);
            }
        }
    }
}
