<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2 font-bold text-lg text-gray-900 dark:text-white">
                <x-heroicon-o-bolt style="width: 24px; height: 24px; color: #f59e0b;" />
                <span>الوصول السريع واختصارات النظام</span>
            </div>
        </x-slot>

        {{-- ─── قسم: الإدارة الأكاديمية ─── --}}
        <div style="margin-bottom: 1.5rem;">
            <p style="font-size: 0.8rem; font-weight: 700; color: #9ca3af; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                <x-heroicon-o-academic-cap style="width: 18px; height: 18px;" />
                الإدارة الأكاديمية
            </p>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 0.75rem;">

                <a href="{{ url('/admin/students/create') }}" class="gandy-shortcut-card">
                    <x-heroicon-o-user-plus style="width: 28px; height: 28px; color: #2563eb;" />
                    <span class="gandy-shortcut-text">تسجيل طالب</span>
                </a>

                <a href="{{ url('/admin/student-imports') }}" class="gandy-shortcut-card">
                    <x-heroicon-o-arrow-up-tray style="width: 28px; height: 28px; color: #10b981;" />
                    <span class="gandy-shortcut-text">استيراد Excel</span>
                </a>

                <a href="{{ url('/admin/students') }}" class="gandy-shortcut-card">
                    <x-heroicon-o-users style="width: 28px; height: 28px; color: #2563eb;" />
                    <span class="gandy-shortcut-text">قائمة الطلاب</span>
                </a>

                <a href="{{ url('/admin/groups/create') }}" class="gandy-shortcut-card">
                    <x-heroicon-o-user-group style="width: 28px; height: 28px; color: #d97706;" />
                    <span class="gandy-shortcut-text">مجموعة جديدة</span>
                </a>

                <a href="{{ url('/admin/groups') }}" class="gandy-shortcut-card">
                    <x-heroicon-o-rectangle-stack style="width: 28px; height: 28px; color: #d97706;" />
                    <span class="gandy-shortcut-text">المجموعات</span>
                </a>

                <a href="{{ url('/admin/group-sessions') }}" class="gandy-shortcut-card">
                    <x-heroicon-o-clipboard-document-check style="width: 28px; height: 28px; color: #9333ea;" />
                    <span class="gandy-shortcut-text">تسجيل حضور</span>
                </a>

                <a href="{{ url('/admin/exams/create') }}" class="gandy-shortcut-card">
                    <x-heroicon-o-document-check style="width: 28px; height: 28px; color: #e11d48;" />
                    <span class="gandy-shortcut-text">امتحان جديد</span>
                </a>

                <a href="{{ url('/admin/exams') }}" class="gandy-shortcut-card">
                    <x-heroicon-o-clipboard-document-list style="width: 28px; height: 28px; color: #e11d48;" />
                    <span class="gandy-shortcut-text">الامتحانات</span>
                </a>

            </div>
        </div>

        {{-- ─── قسم: الإدارة المالية ─── --}}
        <div style="margin-bottom: 1.5rem;">
            <p style="font-size: 0.8rem; font-weight: 700; color: #9ca3af; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                <x-heroicon-o-banknotes style="width: 18px; height: 18px;" />
                الإدارة المالية
            </p>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 0.75rem;">

                <a href="{{ url('/admin/student-payments/create') }}" class="gandy-shortcut-card">
                    <x-heroicon-o-banknotes style="width: 28px; height: 28px; color: #059669;" />
                    <span class="gandy-shortcut-text">قبض رسوم</span>
                </a>

                <a href="{{ url('/admin/student-payments') }}" class="gandy-shortcut-card">
                    <x-heroicon-o-credit-card style="width: 28px; height: 28px; color: #059669;" />
                    <span class="gandy-shortcut-text">سجل المدفوعات</span>
                </a>

                <a href="{{ url('/admin/salaries/create') }}" class="gandy-shortcut-card">
                    <x-heroicon-o-currency-dollar style="width: 28px; height: 28px; color: #0d9488;" />
                    <span class="gandy-shortcut-text">صرف راتب</span>
                </a>

                <a href="{{ url('/admin/salaries') }}" class="gandy-shortcut-card">
                    <x-heroicon-o-wallet style="width: 28px; height: 28px; color: #0d9488;" />
                    <span class="gandy-shortcut-text">الرواتب</span>
                </a>

                <a href="{{ url('/admin/expenses/create') }}" class="gandy-shortcut-card">
                    <x-heroicon-o-arrow-trending-down style="width: 28px; height: 28px; color: #ea580c;" />
                    <span class="gandy-shortcut-text">مصروف جديد</span>
                </a>

                <a href="{{ url('/admin/expenses') }}" class="gandy-shortcut-card">
                    <x-heroicon-o-receipt-percent style="width: 28px; height: 28px; color: #ea580c;" />
                    <span class="gandy-shortcut-text">المصروفات</span>
                </a>

                <a href="{{ url('/admin/reports') }}" class="gandy-shortcut-card">
                    <x-heroicon-o-chart-bar style="width: 28px; height: 28px; color: #4f46e5;" />
                    <span class="gandy-shortcut-text">التقارير المالية</span>
                </a>

            </div>
        </div>

        {{-- ─── قسم: الإعدادات والصلاحيات ─── --}}
        <div>
            <p style="font-size: 0.8rem; font-weight: 700; color: #9ca3af; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                <x-heroicon-o-cog-6-tooth style="width: 18px; height: 18px;" />
                الإعدادات والصلاحيات
            </p>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 0.75rem;">

                <a href="{{ url('/admin/users/create') }}" class="gandy-shortcut-card">
                    <x-heroicon-o-user-circle style="width: 28px; height: 28px; color: #64748b;" />
                    <span class="gandy-shortcut-text">مستخدم جديد</span>
                </a>

                <a href="{{ url('/admin/users') }}" class="gandy-shortcut-card">
                    <x-heroicon-o-users style="width: 28px; height: 28px; color: #64748b;" />
                    <span class="gandy-shortcut-text">المستخدمون</span>
                </a>

                <a href="{{ url('/admin/roles') }}" class="gandy-shortcut-card">
                    <x-heroicon-o-shield-check style="width: 28px; height: 28px; color: #64748b;" />
                    <span class="gandy-shortcut-text">الصلاحيات</span>
                </a>

                <a href="{{ url('/admin/educational-stages') }}" class="gandy-shortcut-card">
                    <x-heroicon-o-academic-cap style="width: 28px; height: 28px; color: #64748b;" />
                    <span class="gandy-shortcut-text">المراحل</span>
                </a>

                <a href="{{ url('/admin/subjects') }}" class="gandy-shortcut-card">
                    <x-heroicon-o-book-open style="width: 28px; height: 28px; color: #64748b;" />
                    <span class="gandy-shortcut-text">المواد</span>
                </a>

                <a href="{{ url('/admin/manage-settings') }}" class="gandy-shortcut-card">
                    <x-heroicon-o-cog-6-tooth style="width: 28px; height: 28px; color: #64748b;" />
                    <span class="gandy-shortcut-text">الإعدادات</span>
                </a>

                <a href="{{ url('/admin/backups') }}" class="gandy-shortcut-card">
                    <x-heroicon-o-server-stack style="width: 28px; height: 28px; color: #64748b;" />
                    <span class="gandy-shortcut-text">النسخ الاحتياطي</span>
                </a>

            </div>
        </div>

    </x-filament::section>
</x-filament-widgets::widget>

<style>
.gandy-shortcut-card {
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 12px 8px !important;
    border-radius: 12px !important;
    border: 1px solid rgba(156, 163, 175, 0.2) !important;
    background-color: rgba(243, 244, 246, 0.5) !important;
    text-align: center !important;
    text-decoration: none !important;
    transition: all 0.2s ease !important;
}
.dark .gandy-shortcut-card {
    background-color: rgba(31, 41, 55, 0.5) !important;
    border-color: rgba(75, 85, 99, 0.4) !important;
}
.gandy-shortcut-card:hover {
    transform: translateY(-2px) !important;
    border-color: #f59e0b !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
}
.gandy-shortcut-text {
    font-size: 12px !important;
    font-weight: 700 !important;
    margin-top: 6px !important;
    color: #374151 !important;
}
.dark .gandy-shortcut-text {
    color: #e5e7eb !important;
}
</style>
