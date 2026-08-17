<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schoolBookscategoriesTitleId = 1;
        $universitycategoriesTitleId = 2;
        $academiccategoriesTitleId = 3;
        $fictioncategoriesTitleId = 4;

        $schoolBookscategories = [
            [
                'name_en' => 'Elementary School',
                'name_ar' => 'المدرسة الابتدائية',
                'slug' => 'elementary-school',
            ],
            [
                'name_en' => 'Middle School',
                'name_ar' => 'المدرسة المتوسطة',
                'slug' => 'middle-school',
            ],
            [
                'name_en' => 'High School',
                'name_ar' => 'المدرسة الثانوية',
                'slug' => 'high-school',
            ],
            [
                'name_en' => 'University',
                'name_ar' => 'الجامعة',
                'slug' => 'university',
            ],
            [
                'name_en' => 'Grade 6',
                'name_ar' => 'الصف السادس',
                'slug' => 'grade-6',
            ],
            [
                'name_en' => 'Grade 7',
                'name_ar' => 'الصف السابع',
                'slug' => 'grade-7',
            ],
            [
                'name_en' => 'Grade 8',
                'name_ar' => 'الصف الثامن',
                'slug' => 'grade-8',
            ],
            [
                'name_en' => 'Grade 9',
                'name_ar' => 'الصف التاسع',
                'slug' => 'grade-9',
            ],
            [
                'name_en' => 'Grade 10',
                'name_ar' => 'الصف العاشر',
                'slug' => 'grade-10',
            ],
            [
                'name_en' => 'Grade 11',
                'name_ar' => 'الصف الحادي عشر',
                'slug' => 'grade-11',
            ],
            [
                'name_en' => 'Grade 12',
                'name_ar' => 'الصف الثاني عشر',
                'slug' => 'grade-12',
            ],
        ];

        $universitycategories = [
            [
                'name_en' => 'Government Universities',
                'name_ar' => 'الجامعات الحكومية',
                'slug' => 'government-universities',
                'children' => [
                    [
                        'name_en' => 'King Saud University',
                        'name_ar' => 'جامعة الملك سعود',
                        'slug' => 'king-saud-university',
                        'description_en' => 'Riyadh',
                        'description_ar' => 'الرياض',
                        'image' => 'assets/test.jpg',
                    ],
                    [
                        'name_en' => 'King Abdulaziz University',
                        'name_ar' => 'جامعة الملك عبدالعزيز',
                        'slug' => 'king-abdulaziz-university',
                        'description_en' => 'Jeddah',
                        'description_ar' => 'جدة',
                        'image' => 'assets/test.jpg',
                    ],
                    [
                        'name_en' => 'King Fahd University of Petroleum and Minerals',
                        'name_ar' => 'جامعة الملك فهد للبترول والمعادن',
                        'slug' => 'king-fahd-university-petroleum-minerals',
                        'description_en' => 'Dhahran',
                        'description_ar' => 'الظهران',
                        'image' => 'assets/test.jpg',
                    ],
                    [
                        'name_en' => 'King Khalid University',
                        'name_ar' => 'جامعة الملك خالد',
                        'slug' => 'king-khalid-university',
                        'description_en' => 'Abha',
                        'description_ar' => 'أبها',
                        'image' => 'assets/test.jpg',
                    ],
                    [
                        'name_en' => 'Qassim University',
                        'name_ar' => 'جامعة القصيم',
                        'slug' => 'qassim-university',
                        'description_en' => 'Buraidah',
                        'description_ar' => 'بريدة',
                        'image' => 'assets/test.jpg',
                    ],
                    [
                        'name_en' => 'Taibah University',
                        'name_ar' => 'جامعة طيبة',
                        'slug' => 'taibah-university',
                        'description_en' => 'Medina',
                        'description_ar' => 'المدينة المنورة',
                        'image' => 'assets/test.jpg',
                    ],
                    [
                        'name_en' => 'Umm Al-Qura University',
                        'name_ar' => 'جامعة أم القرى',
                        'slug' => 'umm-al-qura-university',
                        'description_en' => 'Mecca',
                        'description_ar' => 'مكة المكرمة',
                        'image' => 'assets/test.jpg',
                    ],
                ]
            ],
            [
                'name_en' => 'National Universities',
                'name_ar' => 'الجامعات الوطنية',
                'slug' => 'national-universities',
                'children' => [
                    [
                        'name_en' => 'Princess Nora bint Abdul Rahman University',
                        'name_ar' => 'جامعة الأميرة نورة بنت عبدالرحمن',
                        'slug' => 'princess-nora-university',
                        'description_en' => 'Riyadh',
                        'description_ar' => 'الرياض',
                        'image' => 'assets/test.jpg',
                    ],
                    [
                        'name_en' => 'King Saud bin Abdulaziz University for Health Sciences',
                        'name_ar' => 'جامعة الملك سعود بن عبدالعزيز للعلوم الصحية',
                        'slug' => 'ksau-hs',
                        'description_en' => 'Riyadh',
                        'description_ar' => 'الرياض',
                        'image' => 'assets/test.jpg',
                    ],
                    [
                        'name_en' => 'Saudi Electronic University',
                        'name_ar' => 'الجامعة السعودية الإلكترونية',
                        'slug' => 'saudi-electronic-university',
                        'description_en' => 'Riyadh',
                        'description_ar' => 'الرياض',
                        'image' => 'assets/test.jpg',
                    ],
                    [
                        'name_en' => 'University of Jeddah',
                        'name_ar' => 'جامعة جدة',
                        'slug' => 'university-of-jeddah',
                        'description_en' => 'Jeddah',
                        'description_ar' => 'جدة',
                        'image' => 'assets/test.jpg',
                    ],
                    [
                        'name_en' => 'University of Hail',
                        'name_ar' => 'جامعة حائل',
                        'slug' => 'university-of-hail',
                        'description_en' => 'Hail',
                        'description_ar' => 'حائل',
                        'image' => 'assets/test.jpg',
                    ],
                    [
                        'name_en' => 'University of Bisha',
                        'name_ar' => 'جامعة بيشة',
                        'slug' => 'university-of-bisha',
                        'description_en' => 'Bisha',
                        'description_ar' => 'بيشة',
                        'image' => 'assets/test.jpg',
                    ],
                ]
            ],
            [
                'name_en' => 'Private Colleges & Academies',
                'name_ar' => 'الكليات والأكاديميات الخاصة',
                'slug' => 'private-colleges-academies',
                'children' => [
                    [
                        'name_en' => 'Prince Sultan University',
                        'name_ar' => 'جامعة الأمير سلطان',
                        'slug' => 'prince-sultan-university',
                        'description_en' => 'Riyadh',
                        'description_ar' => 'الرياض',
                        'image' => 'assets/test.jpg',
                    ],
                    [
                        'name_en' => 'Alfaisal University',
                        'name_ar' => 'جامعة الفيصل',
                        'slug' => 'alfaisal-university',
                        'description_en' => 'Riyadh',
                        'description_ar' => 'الرياض',
                        'image' => 'assets/test.jpg',
                    ],
                    [
                        'name_en' => 'Effat University',
                        'name_ar' => 'جامعة عفت',
                        'slug' => 'effat-university',
                        'description_en' => 'Jeddah',
                        'description_ar' => 'جدة',
                        'image' => 'assets/test.jpg',
                    ],
                    [
                        'name_en' => 'Dar Al Hekma University',
                        'name_ar' => 'جامعة دار الحكمة',
                        'slug' => 'dar-al-hekma-university',
                        'description_en' => 'Jeddah',
                        'description_ar' => 'جدة',
                        'image' => 'assets/test.jpg',
                    ],
                    [
                        'name_en' => 'Riyadh College of Dentistry and Pharmacy',
                        'name_ar' => 'كلية الرياض لطب الأسنان والصيدلة',
                        'slug' => 'riyadh-college-dentistry-pharmacy',
                        'description_en' => 'Riyadh',
                        'description_ar' => 'الرياض',
                        'image' => 'assets/test.jpg',
                    ],
                    [
                        'name_en' => 'Saudi German University',
                        'name_ar' => 'الجامعة الألمانية السعودية',
                        'slug' => 'saudi-german-university',
                        'description_en' => 'Riyadh',
                        'description_ar' => 'الرياض',
                        'image' => 'assets/test.jpg',
                    ],
                    [
                        'name_en' => 'Arab Open University',
                        'name_ar' => 'الجامعة العربية المفتوحة',
                        'slug' => 'arab-open-university',
                        'description_en' => 'Riyadh',
                        'description_ar' => 'الرياض',
                        'image' => 'assets/test.jpg',
                    ],
                    [
                        'name_en' => 'Al Yamamah University',
                        'name_ar' => 'جامعة اليمامة',
                        'slug' => 'al-yamamah-university',
                        'description_en' => 'Riyadh',
                        'description_ar' => 'الرياض',
                        'image' => 'assets/test.jpg',
                    ],
                ]
            ],
        ];

        $academiccategories = [
            [
                'name_en' => 'Language Learning',
                'name_ar' => 'تعلم اللغات',
                'slug' => 'language-learning',
            ],
            [
                'name_en' => 'Medical Books',
                'name_ar' => 'الكتب الطبية',
                'slug' => 'medical-books',
            ],
            [
                'name_en' => 'Engineering Books',
                'name_ar' => 'الكتب الهندسية',
                'slug' => 'engineering-books',
            ],
            [
                'name_en' => 'Law Books',
                'name_ar' => 'الكتب القانونية',
                'slug' => 'law-books',
            ],
            [
                'name_en' => 'Business & Management',
                'name_ar' => 'الأعمال والإدارة',
                'slug' => 'business-management',
            ],
            [
                'name_en' => 'Tourism & Hospitality',
                'name_ar' => 'السياحة والضيافة',
                'slug' => 'tourism-hospitality',
            ],
        ];

        $fictioncategories = [
            [
                'name_en' => 'Romance',
                'name_ar' => 'الرومانسية',
                'slug' => 'romance',
            ],
            [
                'name_en' => 'Fantasy',
                'name_ar' => 'الخيال',
                'slug' => 'fantasy',
            ],
            [
                'name_en' => 'Mystery',
                'name_ar' => 'الغموض',
                'slug' => 'mystery',
            ],
            [
                'name_en' => 'Thriller',
                'name_ar' => 'الإثارة',
                'slug' => 'thriller',
            ],
            [
                'name_en' => 'Horror',
                'name_ar' => 'الرعب',
                'slug' => 'horror',
            ],
        ];

        // Insert School Books categories (Title ID: 1)
        foreach ($schoolBookscategories as $SBCat) {
            Category::create([
                'title_id' => $schoolBookscategoriesTitleId,
                'parent_id' => null,
                'image' => null,
                'name_en' => $SBCat['name_en'],
                'name_ar' => $SBCat['name_ar'],
                'slug' => $SBCat['slug'],
                'description_en' => null,
                'description_ar' => null,
                'status' => 1,
                'is_filter' => 1,
                'created_by' => 1,
                'updated_by' => 1,
            ]);
        }

        // Insert University categories with children (Title ID: 2)
        foreach ($universitycategories as $UniCat) {
            // Create parent category
            $parent = Category::create([
                'title_id' => $universitycategoriesTitleId,
                'parent_id' => null,
                'image' => null,
                'name_en' => $UniCat['name_en'],
                'name_ar' => $UniCat['name_ar'],
                'slug' => $UniCat['slug'],
                'description_en' => null,
                'description_ar' => null,
                'status' => 1,
                'is_filter' => 0,
                'created_by' => 1,
                'updated_by' => 1,
            ]);

            // Create child categories if any
            if (!empty($UniCat['children'])) {
                foreach ($UniCat['children'] as $child) {
                    Category::create([
                        'title_id' => $universitycategoriesTitleId,
                        'parent_id' => $parent->id,
                        'image' => $child['image'] ?? null,
                        'name_en' => $child['name_en'],
                        'name_ar' => $child['name_ar'],
                        'slug' => $child['slug'],
                        'description_en' => $child['description_en'] ?? null,
                        'description_ar' => $child['description_ar'] ?? null,
                        'status' => 1,
                        'is_filter' => 0,
                        'created_by' => 1,
                        'updated_by' => 1,
                    ]);
                }
            }
        }

        // Insert Academic categories (Title ID: 3)
        foreach ($academiccategories as $AcadCat) {
            Category::create([
                'title_id' => $academiccategoriesTitleId,
                'parent_id' => null,
                'image' => null,
                'name_en' => $AcadCat['name_en'],
                'name_ar' => $AcadCat['name_ar'],
                'slug' => $AcadCat['slug'],
                'description_en' => null,
                'description_ar' => null,
                'status' => 1,
                'is_filter' => 0,
                'created_by' => 1,
                'updated_by' => 1,
            ]);
        }

        // Insert Fiction categories (Title ID: 4)
        foreach ($fictioncategories as $FicCat) {
            Category::create([
                'title_id' => $fictioncategoriesTitleId,
                'parent_id' => null,
                'image' => null,
                'name_en' => $FicCat['name_en'],
                'name_ar' => $FicCat['name_ar'],
                'slug' => $FicCat['slug'],
                'description_en' => null,
                'description_ar' => null,
                'status' => 1,
                'is_filter' => 1,
                'created_by' => 1,
                'updated_by' => 1,
            ]);
        }
    }
}
