<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // قائمة جميع الصلاحيات في النظام (CRUD + صفحات الموديولات)
        $permissions = [
            // الإعدادات والمراحل والمواد
            ['name' => 'view_educational_stages', 'display_name' => 'عرض المراحل الدراسية'],
            ['name' => 'create_educational_stages', 'display_name' => 'إضافة مرحلة دراسية'],
            ['name' => 'edit_educational_stages', 'display_name' => 'تعديل مرحلة دراسية'],
            ['name' => 'delete_educational_stages', 'display_name' => 'حذف مرحلة دراسية'],

            ['name' => 'view_subjects', 'display_name' => 'عرض المواد الدراسية'],
            ['name' => 'create_subjects', 'display_name' => 'إضافة مادة دراسية'],
            ['name' => 'edit_subjects', 'display_name' => 'تعديل مادة دراسية'],
            ['name' => 'delete_subjects', 'display_name' => 'حذف مادة دراسية'],

            // إدارة الطلاب وطلبات الانضمام
            ['name' => 'view_students', 'display_name' => 'عرض قائمة الطلاب'],
            ['name' => 'create_students', 'display_name' => 'إضافة طالب جديد'],
            ['name' => 'edit_students', 'display_name' => 'تعديل بيانات طالب'],
            ['name' => 'delete_students', 'display_name' => 'حذف طالب'],

            ['name' => 'view_student_applications', 'display_name' => 'عرض طلبات التقديم أونلاين'],
            ['name' => 'edit_student_applications', 'display_name' => 'مراجعة وتعديل طلبات التقديم'],
            ['name' => 'delete_student_applications', 'display_name' => 'حذف طلب تقديم'],

            // المجموعات، الحصص، والحضور والماسح
            ['name' => 'view_groups', 'display_name' => 'عرض المجموعات الدراسية'],
            ['name' => 'create_groups', 'display_name' => 'إضافة مجموعة جديدة'],
            ['name' => 'edit_groups', 'display_name' => 'تعديل مجموعة دراسية'],
            ['name' => 'delete_groups', 'display_name' => 'حذف مجموعة دراسية'],

            ['name' => 'view_group_sessions', 'display_name' => 'عرض سجل الحضور والغياب'],
            ['name' => 'create_group_sessions', 'display_name' => 'إضافة حصة / جلسة دراسية'],
            ['name' => 'edit_group_sessions', 'display_name' => 'تعديل سجل حضور حصة'],
            ['name' => 'delete_group_sessions', 'display_name' => 'حذف حصة دراسية'],

            ['name' => 'use_qr_scanner', 'display_name' => 'استخدام ماسح QR للحضور السريع'],
            ['name' => 'view_weekly_timetable', 'display_name' => 'عرض الجدول الأسبوعي'],

            // الامتحانات والنتائج
            ['name' => 'view_exams', 'display_name' => 'عرض الامتحانات والنتائج'],
            ['name' => 'create_exams', 'display_name' => 'إضافة امتحان جديد'],
            ['name' => 'edit_exams', 'display_name' => 'تعديل بيانات امتحان ونتائجه'],
            ['name' => 'delete_exams', 'display_name' => 'حذف امتحان'],

            // الملازم والكتب وتسليم الملازم
            ['name' => 'view_study_materials', 'display_name' => 'عرض الملازم والمطبوعات'],
            ['name' => 'create_study_materials', 'display_name' => 'إضافة ملزمة / كتاب'],
            ['name' => 'edit_study_materials', 'display_name' => 'تعديل ملزمة / كتاب'],
            ['name' => 'delete_study_materials', 'display_name' => 'حذف ملزمة'],

            ['name' => 'view_material_deliveries', 'display_name' => 'عرض سجل تسليم الملازم'],
            ['name' => 'create_material_deliveries', 'display_name' => 'تسليم ملزمة لطالب'],
            ['name' => 'edit_material_deliveries', 'display_name' => 'تعديل تسليم ملزمة'],
            ['name' => 'delete_material_deliveries', 'display_name' => 'حذف عملية تسليم ملزمة'],

            // الإدارة المالية (سداد الطلاب، الرواتب، المصروفات، التصنيفات، الخصومات)
            ['name' => 'view_student_payments', 'display_name' => 'عرض مدفوعات الطلاب'],
            ['name' => 'create_student_payments', 'display_name' => 'تسجيل سداد طالب'],
            ['name' => 'edit_student_payments', 'display_name' => 'تعديل دفعة مالية لطالب'],
            ['name' => 'delete_student_payments', 'display_name' => 'حذف دفعة مالية'],

            ['name' => 'view_salaries', 'display_name' => 'عرض رواتب ومستحقات الموظفين'],
            ['name' => 'create_salaries', 'display_name' => 'صرف راتب / مستحق لموظف'],
            ['name' => 'edit_salaries', 'display_name' => 'تعديل سند صرف راتب'],
            ['name' => 'delete_salaries', 'display_name' => 'حذف سند صرف راتب'],

            ['name' => 'view_expenses', 'display_name' => 'عرض المصروفات العامة'],
            ['name' => 'create_expenses', 'display_name' => 'تسجيل مصروف جديد'],
            ['name' => 'edit_expenses', 'display_name' => 'تعديل سند مصروف'],
            ['name' => 'delete_expenses', 'display_name' => 'حذف سند مصروف'],

            ['name' => 'view_expense_categories', 'display_name' => 'عرض تصنيفات المصروفات'],
            ['name' => 'create_expense_categories', 'display_name' => 'إضافة تصنيف مصروفات'],
            ['name' => 'edit_expense_categories', 'display_name' => 'تعديل تصنيف مصروفات'],
            ['name' => 'delete_expense_categories', 'display_name' => 'حذف تصنيف مصروفات'],

            ['name' => 'view_discounts', 'display_name' => 'عرض الخصومات والعروض'],
            ['name' => 'create_discounts', 'display_name' => 'إضافة خصم / عرض'],
            ['name' => 'edit_discounts', 'display_name' => 'تعديل خصم / عرض'],
            ['name' => 'delete_discounts', 'display_name' => 'حذف خصم / عرض'],

            // التقارير والتحليلات
            ['name' => 'view_reports', 'display_name' => 'عرض التقارير التحليلية والمالية'],
            ['name' => 'view_advanced_analytics', 'display_name' => 'عرض التحليلات المتقدمة'],

            // إدارة المستخدمين والصلاحيات وسجلات النظام
            ['name' => 'view_users', 'display_name' => 'عرض قائمة المستخدمين والموظفين'],
            ['name' => 'create_users', 'display_name' => 'إضافة مستخدم / موظف جديد'],
            ['name' => 'edit_users', 'display_name' => 'تعديل بيانات وتراخيص مستخدم'],
            ['name' => 'delete_users', 'display_name' => 'حذف مستخدم'],

            ['name' => 'view_roles', 'display_name' => 'عرض الأدوار والصلاحيات'],
            ['name' => 'create_roles', 'display_name' => 'إضافة دور / وظيفة جديدة'],
            ['name' => 'edit_roles', 'display_name' => 'تعديل صلاحيات دور معين'],
            ['name' => 'delete_roles', 'display_name' => 'حذف دور'],

            ['name' => 'view_activity_logs', 'display_name' => 'عرض سجل النشاطات والعمليات'],
            ['name' => 'manage_backups', 'display_name' => 'إدارة النسخ الاحتياطي'],
            ['name' => 'manage_settings', 'display_name' => 'تعديل إعدادات السنتر والنظام'],
        ];

        foreach ($permissions as $permData) {
            Permission::updateOrCreate(
                ['name' => $permData['name']],
                ['display_name' => $permData['display_name']]
            );
        }

        // Create Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $accountantRole = Role::firstOrCreate(['name' => 'accountant']);
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);

        // Give permissions to roles
        $adminRole->syncPermissions(Permission::all());

        $accountantRole->syncPermissions([
            'view_students',
            'view_student_payments',
            'create_student_payments',
            'edit_student_payments',
            'view_salaries',
            'create_salaries',
            'edit_salaries',
            'view_expenses',
            'create_expenses',
            'edit_expenses',
            'view_expense_categories',
            'create_expense_categories',
            'edit_expense_categories',
            'view_discounts',
            'create_discounts',
            'edit_discounts',
            'view_reports',
            'view_advanced_analytics',
        ]);

        $teacherRole->syncPermissions([
            'view_educational_stages',
            'view_subjects',
            'view_students',
            'view_groups',
            'view_group_sessions',
            'create_group_sessions',
            'edit_group_sessions',
            'use_qr_scanner',
            'view_weekly_timetable',
            'view_exams',
            'create_exams',
            'edit_exams',
            'view_study_materials',
            'view_material_deliveries',
            'create_material_deliveries',
        ]);

        $supervisorRole->syncPermissions([
            'view_students',
            'create_students',
            'edit_students',
            'view_student_applications',
            'edit_student_applications',
            'view_groups',
            'view_group_sessions',
            'create_group_sessions',
            'edit_group_sessions',
            'use_qr_scanner',
            'view_weekly_timetable',
            'view_material_deliveries',
            'create_material_deliveries',
        ]);

        // Assign Admin role to admin user
        $user = User::where('email', 'admin@admin.com')->first();
        if ($user) {
            $user->assignRole($adminRole);
        }
    }
}
