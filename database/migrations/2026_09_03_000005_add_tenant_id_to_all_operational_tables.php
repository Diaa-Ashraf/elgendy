<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. users: nullable tenant_id with composite index (بدون global scope لاحقاً)
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->nullOnDelete();
            $table->index(['tenant_id', 'email'], 'users_tenant_email_idx');
        });

        // 2. educational_stages
        Schema::table('educational_stages', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'id'], 'ed_stages_tenant_id_idx');
        });

        // 3. subjects
        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'id'], 'subjects_tenant_id_idx');
        });

        // 4. students
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'stage_id'], 'students_tenant_stage_idx');
            $table->index(['tenant_id', 'parent_phone'], 'students_tenant_pphone_idx');
        });

        // 5. groups
        Schema::table('groups', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'stage_id', 'status'], 'groups_tenant_stage_status_idx');
        });

        // 6. group_schedules
        Schema::table('group_schedules', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'group_id'], 'grp_sched_tenant_group_idx');
        });

        // 7. group_sessions
        Schema::table('group_sessions', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'group_id'], 'grp_sess_tenant_group_idx');
        });

        // 8. group_student
        Schema::table('group_student', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'group_id', 'student_id'], 'grp_std_tenant_grp_std_idx');
        });

        // 9. attendances
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'student_id', 'status'], 'attendances_tenant_std_stat_idx');
        });

        // 10. student_payments
        Schema::table('student_payments', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'student_id', 'paid_at'], 'std_payments_tenant_std_paid_idx');
        });

        // 11. salaries
        Schema::table('salaries', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'user_id', 'month'], 'salaries_tenant_user_month_idx');
        });

        // 12. exams
        Schema::table('exams', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'stage_id', 'is_online'], 'exams_tenant_stage_online_idx');
        });

        // 13. exam_results
        Schema::table('exam_results', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'exam_id', 'student_id'], 'exam_res_tenant_exam_std_idx');
        });

        // 14. questions
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'subject_id', 'stage_id'], 'questions_tenant_subj_stg_idx');
        });

        // 15. exam_questions
        Schema::table('exam_questions', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'exam_id'], 'exam_q_tenant_exam_idx');
        });

        // 16. online_exam_attempts
        Schema::table('online_exam_attempts', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'exam_id', 'student_id'], 'onl_atmpt_tenant_exam_std_idx');
        });

        // 17. expense_categories
        Schema::table('expense_categories', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'id'], 'exp_cat_tenant_id_idx');
        });

        // 18. expenses
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'category_id', 'date'], 'expenses_tenant_cat_date_idx');
        });

        // 19. inventory_items
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'id'], 'inv_items_tenant_id_idx');
        });

        // 20. inventory_movements
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'item_id'], 'inv_mov_tenant_item_idx');
        });

        // 21. discounts
        Schema::table('discounts', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'id'], 'discounts_tenant_id_idx');
        });

        // 22. student_applications
        Schema::table('student_applications', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'stage_id', 'status'], 'std_app_tenant_stg_stat_idx');
        });

        // 23. study_materials
        Schema::table('study_materials', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'stage_id'], 'materials_tenant_stg_idx');
        });

        // 24. student_material_deliveries (short index name to prevent MySQL 64 char limit)
        Schema::table('student_material_deliveries', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'student_id', 'payment_status'], 'std_mat_deliv_t_s_p_idx');
        });

        // 25. online_payment_requests
        Schema::table('online_payment_requests', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'student_id', 'status'], 'onl_pay_req_t_s_st_idx');
        });

        // 26. student_imports
        Schema::table('student_imports', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'status'], 'std_imports_tenant_stat_idx');
        });

        // 27. notifications
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'notifiable_id'], 'notif_tenant_notifiable_idx');
        });

        // 28. activity_log (Audit logs)
        if (Schema::hasTable('activity_log')) {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
                $table->index(['tenant_id', 'created_at'], 'act_log_tenant_created_idx');
            });
        }
    }

    public function down(): void
    {
        // Safe reverse
        $tables = [
            'activity_log', 'notifications', 'student_imports', 'online_payment_requests',
            'student_material_deliveries', 'study_materials', 'student_applications',
            'discounts', 'inventory_movements', 'inventory_items', 'expenses',
            'expense_categories', 'online_exam_attempts', 'exam_questions', 'questions',
            'exam_results', 'exams', 'salaries', 'student_payments', 'attendances',
            'group_student', 'group_sessions', 'group_schedules', 'groups', 'students',
            'subjects', 'educational_stages', 'users'
        ];

        foreach ($tables as $tbl) {
            if (Schema::hasTable($tbl) && Schema::hasColumn($tbl, 'tenant_id')) {
                Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                    $table->dropForeign([ 'tenant_id' ]);
                    $table->dropColumn('tenant_id');
                });
            }
        }
    }
};
