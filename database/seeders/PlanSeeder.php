<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'بداية',
                'slug' => 'starter',
                'price_monthly' => 299.00,
                'max_students' => 200,
                'max_teachers' => 1,
                'max_groups' => 15,
                'is_popular' => false,
                'sort_order' => 1,
                'features' => [
                    'حتى 200 طالب نشط',
                    '1 مدرس (حساب رئيسي)',
                    '15 مجموعة دراسية',
                    'حضور وغياب ذكي بكروت الـ QR',
                    'بوابة ولي الأمر للمتابعة الفورية',
                    'موقع إلكتروني خاص بروفايل للمدرس',
                    'تقارير مالية وتتبع الاشتراكات الأساسية',
                ],
            ],
            [
                'name' => 'نمو',
                'slug' => 'growth',
                'price_monthly' => 499.00,
                'max_students' => 1000,
                'max_teachers' => 10,
                'max_groups' => 50,
                'is_popular' => true,
                'sort_order' => 2,
                'features' => [
                    'حتى 1,000 طالب نشط',
                    'حتى 10 مدرسين ومساعدين بصلاحيات',
                    '50 مجموعة دراسية',
                    'كل مميزات باقة "بداية"',
                    'امتحانات إلكترونية وبنك أسئلة بتصحيح فوري',
                    'بوابة الطالب الذكية للاختبارات',
                    'إدارة المخزن وتسليم الملازم والكتب',
                    'استيراد الطلاب بملفات إكسيل بضغطة زر',
                ],
            ],
            [
                'name' => 'احتراف',
                'slug' => 'pro',
                'price_monthly' => 599.00,
                'max_students' => 5000,
                'max_teachers' => 50,
                'max_groups' => 200,
                'is_popular' => false,
                'sort_order' => 3,
                'features' => [
                    'حتى 5,000 طالب نشط (سعة استيعابية ضخمة)',
                    'حتى 50 مساعد مع إدارة الرواتب',
                    '200 مجموعة دراسية وسنnumberOfسناتر متعددة',
                    'كل مميزات باقة "نمو"',
                    'تقارير وتحليلات أداء وأرباح متقدمة',
                    'سداد إلكتروني مع تأكيد الإيصالات الفورية',
                    'دعم فني مخصص وأولوية في التحديثات',
                    'تصدير شامل للبيانات والنسخ الاحتياطية',
                ],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
