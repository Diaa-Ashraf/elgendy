@php
    $__settingService = app(\App\Services\SettingService::class);
    $__favicon = $__settingService->url('site_favicon') ?: $__settingService->url('center_logo') ?: asset('favicon.ico');
@endphp
@if($__favicon)
    <link rel="icon" type="image/png" href="{{ $__favicon }}">
    <link rel="shortcut icon" href="{{ $__favicon }}">
    <link rel="apple-touch-icon" href="{{ $__favicon }}">
@endif

<style>
    /* ==========================================================================
       نظام تصميم وتنسيق الجداول الموحد والفاخر لجميع صفحات وموارد المنظومة
       ========================================================================== */

    /* ─── 1. حاوية الجداول العامة لـ Filament (Resource Tables Container) ─── */
    .fi-ta-ctn {
        border-radius: 1.25rem !important;
        overflow: hidden !important;
        border: 1.5px solid #e2e8f0 !important;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05) !important;
        background-color: #ffffff !important;
        transition: all 0.2s ease !important;
    }
    .dark .fi-ta-ctn {
        border-color: #374151 !important;
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.3) !important;
        background-color: #111827 !important;
    }

    /* ─── 2. ترويسة الجدول وشريط الأدوات والبحث ─── */
    .fi-ta-header-ctn,
    .fi-ta-header {
        background-color: #f8fafc !important;
        border-bottom: 1.5px solid #e2e8f0 !important;
        padding: 0.95rem 1.25rem !important;
    }
    .dark .fi-ta-header-ctn,
    .dark .fi-ta-header {
        background-color: #1f2937 !important;
        border-bottom-color: #374151 !important;
    }

    /* ─── 3. عناوين الأعمدة في الجداول (Header Cells) ─── */
    .fi-ta-header-cell,
    .fi-ta-table th,
    table.table-custom th {
        background-color: #f1f5f9 !important;
        color: #334155 !important;
        font-weight: 900 !important;
        font-size: 0.78rem !important;
        padding: 0.9rem 1.1rem !important;
        border-bottom: 1.5px solid #e2e8f0 !important;
        text-transform: none !important;
        white-space: nowrap !important;
    }
    .dark .fi-ta-header-cell,
    .dark .fi-ta-table th,
    .dark table.table-custom th {
        background-color: #182234 !important;
        color: #cbd5e1 !important;
        border-bottom-color: #374151 !important;
    }
    .fi-ta-header-cell-label {
        font-weight: 900 !important;
    }

    /* ─── 4. صفوف وخلايا الجداول (Rows & Cells) ─── */
    .fi-ta-row,
    table.table-custom tbody tr {
        transition: background-color 0.15s ease !important;
    }
    .fi-ta-row:hover,
    table.table-custom tbody tr:hover {
        background-color: rgba(241, 245, 249, 0.75) !important;
    }
    .dark .fi-ta-row:hover,
    .dark table.table-custom tbody tr:hover {
        background-color: rgba(31, 41, 55, 0.6) !important;
    }

    .fi-ta-cell,
    .fi-ta-table td,
    table.table-custom td {
        padding: 0.9rem 1.1rem !important;
        font-weight: 700 !important;
        font-size: 0.84rem !important;
        color: #1e293b !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }
    .dark .fi-ta-cell,
    .dark .fi-ta-table td,
    .dark table.table-custom td {
        color: #e2e8f0 !important;
        border-bottom-color: #1f2937 !important;
    }

    /* ─── 5. شريط الترقيم والتنقل (Pagination) ─── */
    .fi-ta-pagination {
        background-color: #f8fafc !important;
        border-top: 1.5px solid #e2e8f0 !important;
        padding: 0.8rem 1.25rem !important;
    }
    .dark .fi-ta-pagination {
        background-color: #1f2937 !important;
        border-top-color: #374151 !important;
    }

    /* ─── 6. شارات الجداول (Badges) وحقول الإدخال والأزرار ─── */
    .fi-badge {
        font-weight: 800 !important;
        border-radius: 0.65rem !important;
        padding: 0.25rem 0.65rem !important;
    }

    .fi-ta-search-field input,
    .fi-ta-filters input,
    .fi-ta-filters select,
    .fi-input-wrp {
        border-radius: 0.85rem !important;
        font-weight: 700 !important;
    }

    /* ─── 7. كروت أقسام Filament (Sections) ─── */
    .fi-section {
        border-radius: 1.25rem !important;
        border: 1.5px solid #e2e8f0 !important;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05) !important;
        background-color: #ffffff !important;
    }
    .dark .fi-section {
        border-color: #374151 !important;
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.3) !important;
        background-color: #111827 !important;
    }
    .fi-section-header {
        border-bottom: 1.5px solid #e2e8f0 !important;
        padding: 1rem 1.25rem !important;
        background-color: #f8fafc !important;
    }
    .dark .fi-section-header {
        border-bottom-color: #374151 !important;
        background-color: #1f2937 !important;
    }

    /* ─── 8. النوافذ المنبثقة والجداول المضمنة (Modals) ─── */
    .fi-modal-window {
        border-radius: 1.5rem !important;
        overflow: hidden !important;
    }
    .fi-modal-window table {
        border-collapse: collapse !important;
        width: 100% !important;
    }
    .fi-modal-window table th {
        background-color: #f1f5f9 !important;
        color: #334155 !important;
        font-weight: 900 !important;
        font-size: 0.78rem !important;
        padding: 0.75rem 1rem !important;
        border-bottom: 1.5px solid #e2e8f0 !important;
    }
    .dark .fi-modal-window table th {
        background-color: #182234 !important;
        color: #cbd5e1 !important;
        border-bottom-color: #374151 !important;
    }
    .fi-modal-window table td {
        padding: 0.75rem 1rem !important;
        font-weight: 700 !important;
        font-size: 0.82rem !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }
    .dark .fi-modal-window table td {
        border-bottom-color: #1f2937 !important;
    }

    /* ─── 9. كروت الإحصائيات الفخمة (Filament Stats Overview Cards) ─── */
    .fi-wi-stats-overview-stat {
        border-radius: 1.25rem !important;
        border: 1.5px solid #e2e8f0 !important;
        background-color: #ffffff !important;
        box-shadow: 0 4px 18px -2px rgba(0, 0, 0, 0.05) !important;
        padding: 1.25rem 1.35rem !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease !important;
    }
    .dark .fi-wi-stats-overview-stat {
        border-color: #374151 !important;
        background-color: #111827 !important;
        box-shadow: 0 8px 25px -3px rgba(0, 0, 0, 0.35) !important;
    }
    .fi-wi-stats-overview-stat:hover {
        transform: translateY(-3px) !important;
        box-shadow: 0 12px 28px -4px rgba(0, 0, 0, 0.1) !important;
    }
    .dark .fi-wi-stats-overview-stat:hover {
        box-shadow: 0 14px 32px -4px rgba(0, 0, 0, 0.5) !important;
    }

    /* عناوين الإحصائيات */
    .fi-wi-stats-overview-stat-label,
    .fi-wi-stats-overview-stat .fi-wi-stats-overview-stat-label span {
        font-size: 0.88rem !important;
        font-weight: 800 !important;
        color: #334155 !important;
    }
    .dark .fi-wi-stats-overview-stat-label,
    .dark .fi-wi-stats-overview-stat .fi-wi-stats-overview-stat-label span {
        color: #cbd5e1 !important;
    }

    /* الأرقام والقيم الضخمة فائقة الوضوح */
    .fi-wi-stats-overview-stat-value {
        font-size: 1.85rem !important;
        font-weight: 900 !important;
        line-height: 1.2 !important;
        letter-spacing: -0.02em !important;
        margin-top: 0.35rem !important;
        margin-bottom: 0.35rem !important;
    }

    /* الوصف التوضيحي أسفل الرقم */
    .fi-wi-stats-overview-stat-description {
        font-size: 0.78rem !important;
        font-weight: 700 !important;
        display: flex !important;
        align-items: center !important;
        gap: 0.35rem !important;
    }

    /* ─── 10. تحسينات التجاوب الشامل (Global Responsive Helpers) ─── */
    .fi-ta-content,
    .fi-ta-table-ctn {
        -webkit-overflow-scrolling: touch !important;
    }
    @media (max-width: 640px) {
        .fi-ta-header-cell,
        .fi-ta-table th,
        table.table-custom th,
        .fi-ta-cell,
        .fi-ta-table td,
        table.table-custom td {
            padding: 0.65rem 0.75rem !important;
            font-size: 0.78rem !important;
        }
        .fi-ta-header-ctn,
        .fi-ta-header {
            padding: 0.75rem 1rem !important;
        }
        .fi-wi-stats-overview-stat {
            padding: 1rem !important;
        }
        .fi-wi-stats-overview-stat-value {
            font-size: 1.5rem !important;
        }
    }

    /* ─── 11. أيقونة الملف الشخصي والشريط العلوي الفاخر (User Avatar & Topbar) ─── */
    .fi-user-avatar,
    .fi-avatar {
        border-radius: 9999px !important;
        border: 2px solid #f59e0b !important;
        box-shadow: 0 0 10px rgba(245, 158, 11, 0.3) !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease !important;
        overflow: hidden !important;
    }
    .fi-user-avatar:hover,
    .fi-avatar:hover {
        transform: scale(1.08) !important;
        box-shadow: 0 0 15px rgba(245, 158, 11, 0.5) !important;
    }
    .fi-user-menu button {
        border-radius: 9999px !important;
    }
</style>

