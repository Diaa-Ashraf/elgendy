# Developer & System Architectural Handbook (دليل المطور الشامل للمشروع)

## 📌 Project Overview
- **System Type:** Multi-Tenant SaaS Platform for Tutors, Teachers, and Educational Academies.
- **Tech Stack:** Laravel 12 (PHP 8.3+) | Filament v3/v5 | Tailwind CSS | MySQL | Alpine.js | Redis & Queues.
- **Primary Goal:** Enable tutors to manage their students, attendance, homework, exams (online & paper), study materials, financial ledger, and WhatsApp alerts through a tenant-isolated subdomain/route system with separate portals for students and parents.

---

## 1. Multi-Tenancy Architecture (كيف تعمل المنظومة السحابية وعزل البيانات)

### 1.1 Architecture Pattern: Single Database with Row-Level Tenancy
- Every tutor or educational center is stored as a record in the `tenants` table.
- Identification happens via a unique `slug` (e.g., `mr-diaa`).
- Every tenant-owned database table contains a indexed foreign key column: `tenant_id`.

### 1.2 Request Life Cycle & Context Resolution (دورة حياة الطلب من الدخول حتى التنفيذ)
1. **Request Hits URL:** User requests a path under `/t/{tenant}/...` (e.g., `/t/mr-diaa/student/dashboard`).
2. **Middleware Interception:** `ResolveTenant` middleware (`app/Http/Middleware/ResolveTenant.php`) intercepts the request:
   - Reads the `{tenant}` parameter from the route.
   - Finds the matching record in `Tenant` model. If not found, aborts with 404.
   - Binds the found tenant into the `TenantContext` singleton service (`app/Services/TenantContext.php`).
   - Shares `$currentTenant` variable with all Blade views.
3. **Data Scoping (Automatic Isolation):**
   - Any model using `BelongsToTenant` trait (`app/Traits/BelongsToTenant.php`) attaches an Eloquent Global Scope `tenant`.
   - All `SELECT`, `UPDATE`, `DELETE` queries are automatically scoped with: `WHERE tenant_id = currentTenant->id`.
   - On `creating` model event, `tenant_id` is automatically set to `TenantContext::id()`.
4. **Filament Admin Panel Tenancy:**
   - In `app/Providers/Filament/AdminPanelProvider.php`, `.tenant(Tenant::class, slugAttribute: 'slug')` handles isolation for teacher dashboards (`/admin/{tenant}`).
   - Middleware `SyncFilamentTenant` (`app/Http/Middleware/SyncFilamentTenant.php`) syncs the Filament active tenant with `TenantContext`.

---

## 2. Exhaustive Services Directory (دليل طبقة الخدمات الـ 19 بالتفصيل)

All heavy business logic, calculations, database transactions, and external integrations MUST reside in `app/Services/`. Below is the complete directory of every service:

### 1. `TenantContext` (`app/Services/TenantContext.php`)
- **Purpose:** Request-scoped singleton holding the active `Tenant` model.
- **Key Methods:** `set(Tenant $tenant)`, `get(): ?Tenant`, `id(): ?int`.
- **Used By:** `ResolveTenant`, `BelongsToTenant`, `SyncFilamentTenant`.

### 2. `HomeworkService` (`app/Services/HomeworkService.php`)
- **Purpose:** Handles homework distribution, student submissions, and auto-grading of MCQ questions.
- **Key Methods:**
  - `getStudentHomeworks(Student $student)`: Retrieves published homework matching the student's educational stage and enrolled groups.
  - `submitHomework(Student $student, Homework $homework, array $data)`: Saves files or answers and triggers grading.
  - `autoGradeQuestions(HomeworkSubmission $submission)`: Compares student answers against question keys and calculates score.
  - `getSubmissionStats(Homework $homework)`: Aggregates counts of graded, pending, and late submissions.
- **Used By:** `StudentPortalController`, `ParentPortalController`, `HomeworkResource`.

### 3. `OnlineExamService` (`app/Services/OnlineExamService.php`)
- **Purpose:** Full online quiz engine, countdown timer validation, anti-cheat tracking, auto-correction, and AI-powered weakness diagnosis.
- **Key Methods:**
  - `startAttempt(Student $student, Exam $exam)`: Initializes an exam session and starts timer.
  - `submitAttempt(OnlineExamAttempt $attempt, array $answers)`: Auto-corrects questions, generates strengths/weaknesses topics analysis.
  - `getStudentExamHistory(Student $student)`: Lists past attempts and scores.
- **Used By:** `OnlineExamController`, `ExamResource`.

### 4. `AttendanceService` (`app/Services/AttendanceService.php`)
- **Purpose:** Processing student check-in/check-out in group sessions, calculating consecutive absences, and triggering warnings.
- **Key Methods:**
  - `recordAttendance(GroupSession $session, Student $student, string $status, ?string $notes)`
  - `bulkRecord(GroupSession $session, array $records)`
  - `getConsecutiveAbsenceCount(Student $student)`
- **Used By:** `GroupSessionResource`, `QrScanner` (Page), `Attendance`.

### 5. `WhatsAppNotificationService` (`app/Services/WhatsAppNotificationService.php`)
- **Purpose:** Sending automated real-time WhatsApp alerts to parents upon student attendance, absence, exam results, and payment receipts.
- **Key Methods:**
  - `sendAttendanceAlert(Attendance $attendance)`
  - `sendExamResultAlert(Student $student, Exam $exam, float $score, float $total)`
  - `sendPaymentReceiptAlert(StudentPayment $payment)`
- **Used By:** `AttendanceService`, `OnlineExamService`, `StudentPaymentResource`.

### 6. `StudentLedgerService` (`app/Services/StudentLedgerService.php`)
- **Purpose:** Comprehensive financial balance calculation for students (Total Required Charges - Total Paid Amounts = Net Balance Due).
- **Key Methods:**
  - `getFullLedger(Student $student)`: Returns itemized statements of monthly session fees, materials received, and payments made.
  - `getBalance(Student $student)`: Returns exact numeric balance due.
- **Used By:** `StudentResource`, `ParentPortalController`, `StudentPdfController`.

### 7. `PaymentService` (`app/Services/PaymentService.php`)
- **Purpose:** Creating official student payment records, managing partial payments, applying discounts, and generating receipts.
- **Key Methods:**
  - `createPayment(Student $student, array $data)`
  - `applyDiscount(Student $student, Discount $discount)`
- **Used By:** `StudentPaymentResource`, `OnlinePaymentRequestResource`.

### 8. `QuestionImportService` (`app/Services/QuestionImportService.php`)
- **Purpose:** Parsing Excel spreadsheets, Word (.docx) documents, and unstructured raw question text into structured MCQ and True/False database questions.
- **Key Methods:**
  - `importFromExcel($filePath, $stageId, $subjectId)`
  - `parseRawTextWithAi(string $rawText)`
- **Used By:** `QuestionResource`, `ExamResource`.

### 9. `StudentImportService` (`app/Services/StudentImportService.php`)
- **Purpose:** Bulk student roster onboarding from Excel/CSV files, deduplicating phone numbers, and auto-enrolling into assigned groups.
- **Key Methods:**
  - `processImport(StudentImport $importRecord)`
  - `validateRows(array $rows)`
- **Used By:** `StudentImports` (Filament Page), `ProcessStudentImportJob`.

### 10. `ExamService` (`app/Services/ExamService.php`)
- **Purpose:** Managing traditional offline/paper exams, recording marks, computing class average, rank percentiles, and top student lists.
- **Key Methods:**
  - `recordResults(Exam $exam, array $results)`
  - `getTopPerformers(Exam $exam, int $limit = 10)`
- **Used By:** `ExamResource`, `ExamResult`.

### 11. `ExpenseService` (`app/Services/ExpenseService.php`)
- **Purpose:** Accounting module for tracking center operating expenses categorized under customizable categories (Rent, Utilities, Print, etc.).
- **Key Methods:**
  - `recordExpense(array $data)`
  - `getExpensesSummaryByPeriod($startDate, $endDate)`
- **Used By:** `ExpenseResource`, `ExpenseCategoryResource`.

### 12. `SalaryService` (`app/Services/SalaryService.php`)
- **Purpose:** Payroll calculations for assistant teachers, staff, hourly session rates, bonuses, and deductions.
- **Key Methods:**
  - `calculateAssistantSalary(User $assistant, $month, $year)`
- **Used By:** `SalaryResource`.

### 13. `ReportService` (`app/Services/ReportService.php`)
- **Purpose:** Generating comprehensive analytics (monthly net profits, attendance ratios, group profitability, cash flow analysis).
- **Key Methods:**
  - `getExecutiveOverview()`
  - `getMonthlyProfitabilityReport($year, $month)`
- **Used By:** `Reports` (Page), `AdvancedAnalytics` (Page), `ExecutiveStatsWidget`.

### 14. `SettingService` (`app/Services/SettingService.php`)
- **Purpose:** Cached key-value settings storage for tenant customization (center name, logo, Vodafone Cash numbers, InstaPay QR, WhatsApp API keys).
- **Key Methods:**
  - `get(string $key, $default = null)`
  - `set(string $key, $value)`
  - `url(string $key)`: Returns full URL for uploaded assets.
- **Used By:** `ManageSettings` (Page), `ParentPortalController`, and portal templates.

### 15. `SubscriptionService` (`app/Services/SubscriptionService.php`)
- **Purpose:** SaaS tenant subscription lifecycle management, checking trial expiration dates, plan upgrades, and invoice generation.
- **Key Methods:**
  - `isSubscriptionActive(Tenant $tenant): bool`
  - `renewSubscription(Tenant $tenant, Plan $plan, int $months)`
  - `recordPayment(Subscription $subscription, array $data)`
- **Used By:** `CheckSubscription` (Middleware), `SubscriptionPaymentController`, `SuperAdmin Panel`.

### 16. `TenantRegistrationService` (`app/Services/TenantRegistrationService.php`)
- **Purpose:** New teacher signup workflow: creating Tenant record, registering Admin User, setting permissions, assigning default Free Trial.
- **Key Methods:**
  - `registerTenant(array $data): Tenant`
- **Used By:** `PlatformController@register`.

### 17. `TenantExportService` (`app/Services/TenantExportService.php`)
- **Purpose:** Complete data backup and export for a specific tenant into zip archives containing CSV/JSON exports.
- **Key Methods:**
  - `exportTenantData(Tenant $tenant): string`
- **Used By:** `TenantResource` in SuperAdmin.

### 18. `BackupService` (`app/Services/BackupService.php`)
- **Purpose:** Generating direct MySQL database dumps and allowing admins to download or restore center backups.
- **Key Methods:**
  - `createDatabaseBackup(): string`
  - `getAvailableBackups(): array`
- **Used By:** `Backups` (Filament Page).

### 19. `NotificationService` (`app/Services/NotificationService.php`)
- **Purpose:** Internal bell notifications in Filament dashboard and SMS alert triggers.
- **Used By:** Filament notifications and event listeners.

---

## 3. Module-by-Module Code Map & Modification Guide (خريطة الموديولات الكاملة لتعديل الكود)

If you need to edit, debug, or add features to any specific module, here are all the connected files for each:

---

### Module 1: Student Management & Bulk Import (إدارة الطلاب والاستيراد)
- **Database Tables:** `students`, `student_imports`
- **Models:** `app/Models/Student.php`, `app/Models/StudentImport.php`
- **Service Layer:** `app/Services/StudentImportService.php`, `app/Services/StudentLedgerService.php`
- **Background Jobs:** `app/Jobs/ProcessStudentImportJob.php`
- **Filament Admin Panel:**
  - Resource: `app/Filament/Resources/StudentResource.php`
  - Bulk Import Page: `app/Filament/Pages/StudentImports.php`
- **PDF & Document Generation:** `app/Http/Controllers/StudentPdfController.php` (`printCard`, `printLedger`)
- **Student Authentication:** `StudentPortalController.php` authenticates students by `id` (student code) and matching `parent_phone`.

---

### Module 2: Groups, Timetables & Class Sessions (المجموعات والحصص)
- **Database Tables:** `groups`, `group_schedules`, `group_sessions`, `group_student_pivot`
- **Models:** `app/Models/Group.php`, `app/Models/GroupSchedule.php`, `app/Models/GroupSession.php`
- **Filament Admin Panel:**
  - `app/Filament/Resources/GroupResource.php` (Group details, subject, stage, pricing)
  - `app/Filament/Resources/GroupSessionResource.php` (Live sessions, attendance sheet, topic covered)
  - `app/Filament/Pages/WeeklyTimetable.php` (Visual weekly schedule calendar)

---

### Module 3: Attendance Tracking & Smart QR Scanner (الحضور والغياب والباركود)
- **Database Tables:** `attendances`
- **Models:** `app/Models/Attendance.php`
- **Service Layer:** `app/Services/AttendanceService.php`, `app/Services/WhatsAppNotificationService.php`
- **Filament Admin Panel:**
  - Smart Scanner: `app/Filament/Pages/QrScanner.php` (Instant QR scanning via camera or barcode gun)
  - Session Sheet: `app/Filament/Resources/GroupSessionResource.php`

---

### Module 4: Question Bank & AI Importer (بنك الأسئلة والذكاء الاصطناعي)
- **Database Tables:** `questions`
- **Models:** `app/Models/Question.php`
- **Service Layer:** `app/Services/QuestionImportService.php`
- **Filament Admin Panel:** `app/Filament/Resources/QuestionResource.php`
- **Features:** Supports `single_choice`, `multiple_choice`, `true_false`, image attachments, topic tags, difficulty levels (`easy`, `medium`, `hard`), and scientific explanations.

---

### Module 5: Online & Paper Exams (الامتحانات والكويزات الإلكترونية والورقية)
- **Database Tables:** `exams`, `exam_questions`, `online_exam_attempts`, `exam_results`
- **Models:** `app/Models/Exam.php`, `app/Models/OnlineExamAttempt.php`, `app/Models/ExamResult.php`
- **Service Layer:** `app/Services/OnlineExamService.php`, `app/Services/ExamService.php`
- **Filament Admin Panel:**
  - `app/Filament/Resources/ExamResource.php`
  - Questions Relation Manager: `app/Filament/Resources/ExamResource/RelationManagers/QuestionsRelationManager.php`
- **Public & Student Controllers:** `app/Http/Controllers/OnlineExamController.php`
- **Blade Views:**
  - Exam Lobby & Instructions: `resources/views/parent-portal/exams/show.blade.php`
  - Interactive Quiz Runner (Alpine.js + Countdown Timer): `resources/views/parent-portal/exams/take.blade.php`
  - Detailed Report & Strengths/Weaknesses Breakdown: `resources/views/parent-portal/exams/result.blade.php`

---

### Module 6: Homework & Assignments (الواجبات والمهام المنزلية)
- **Database Tables:** `homeworks`, `homework_questions`, `homework_submissions`
- **Models:** `app/Models/Homework.php`, `app/Models/HomeworkSubmission.php`
- **Service Layer:** `app/Services/HomeworkService.php`
- **Filament Admin Panel:**
  - Main Resource: `app/Filament/Resources/HomeworkResource.php`
  - Questions Manager: `app/Filament/Resources/HomeworkResource/RelationManagers/QuestionsRelationManager.php`
  - Submissions & Grading Manager: `app/Filament/Resources/HomeworkResource/RelationManagers/SubmissionsRelationManager.php`
- **Controllers:** `app/Http/Controllers/StudentPortalController.php` (`showHomework`, `submitHomework`)
- **Blade Views:**
  - Student Homework Solver: `resources/views/student-portal/homework-show.blade.php`
  - Parent Homework Monitor: `resources/views/parent-portal/dashboard.blade.php`

---

### Module 7: Accounting, Billing & Online Payments (الحسابات وسداد الرسوم)
- **Database Tables:** `student_payments`, `expenses`, `expense_categories`, `salaries`, `discounts`, `online_payment_requests`
- **Models:** `app/Models/StudentPayment.php`, `app/Models/Expense.php`, `app/Models/Salary.php`, `app/Models/OnlinePaymentRequest.php`, `app/Models/Discount.php`
- **Service Layer:** `app/Services/StudentLedgerService.php`, `app/Services/PaymentService.php`, `app/Services/ExpenseService.php`, `app/Services/SalaryService.php`
- **Filament Admin Panel:**
  - `app/Filament/Resources/StudentPaymentResource.php` (Cashier receipts)
  - `app/Filament/Resources/OnlinePaymentRequestResource.php` (Parent InstaPay/Vodafone Cash review & approval)
  - `app/Filament/Resources/ExpenseResource.php`
  - `app/Filament/Resources/SalaryResource.php`

---

### Module 8: Study Materials & Inventory (الملازم والمطبوعات والمخزن)
- **Database Tables:** `study_materials`, `student_material_deliveries`, `inventory_items`, `inventory_movements`
- **Models:** `app/Models/StudyMaterial.php`, `app/Models/StudentMaterialDelivery.php`, `app/Models/InventoryItem.php`
- **Filament Admin Panel:**
  - `app/Filament/Resources/StudyMaterialResource.php`
  - `app/Filament/Resources/StudentMaterialDeliveryResource.php`

---

### Module 9: Portals & Landing Pages (بوابات المتابعة والمواقع)
- **Teacher Profile & Online Student Enrollment:**
  - Route: `/t/{tenant}`
  - Controller: `app/Http/Controllers/HomeController.php`
  - View: `resources/views/tenant-landing/index.blade.php`
  - Admin Approval: `app/Filament/Resources/StudentApplicationResource.php`
- **Student Portal:**
  - Routes: `/t/{tenant}/student/*`
  - Controller: `app/Http/Controllers/StudentPortalController.php`
  - Views: `resources/views/student-portal/login.blade.php`, `resources/views/student-portal/dashboard.blade.php`
- **Parent Portal:**
  - Routes: `/t/{tenant}/parent/*`
  - Controller: `app/Http/Controllers/ParentPortalController.php`
  - Views: `resources/views/parent-portal/login.blade.php`, `resources/views/parent-portal/dashboard.blade.php`

---

### Module 10: SaaS Tenant Subscriptions & SuperAdmin (اشتراكات المدرسين والمنصة)
- **Database Tables:** `tenants`, `plans`, `subscriptions`, `subscription_payments`
- **Models:** `app/Models/Tenant.php`, `app/Models/Plan.php`, `app/Models/Subscription.php`, `app/Models/SubscriptionPayment.php`
- **Middleware:** `app/Http/Middleware/CheckSubscription.php` (Restricts expired tenants)
- **Service Layer:** `app/Services/SubscriptionService.php`
- **SuperAdmin Panel (`/super-admin`):**
  - Panel Config: `app/Providers/Filament/SuperAdminPanelProvider.php`
  - Resources: `TenantResource.php`, `PlanResource.php`, `SubscriptionResource.php`, `SubscriptionPaymentResource.php`
- **Teacher Subscription Management UI:**
  - Controller: `app/Http/Controllers/SubscriptionPaymentController.php`
  - Views: `resources/views/subscription/status.blade.php`, `resources/views/subscription/pay.blade.php`

---

### Module 11: Settings, Customization & Backups (الإعدادات والنسخ الاحتياطي)
- **Models:** `app/Models/TenantSetting.php`
- **Service Layer:** `app/Services/SettingService.php`, `app/Services/BackupService.php`
- **Filament Pages:**
  - `app/Filament/Pages/ManageSettings.php` (Logo, contact numbers, payment accounts, WhatsApp API config)
  - `app/Filament/Pages/Backups.php` (MySQL database export and restore)

---

## 4. Routing Architecture & Route Tree (شجرة المسارات)

All routes are registered in `routes/web.php` with distinct prefixes and named route groups:

```
Platform / Marketing Routes:
  GET  /                              --> platform.home
  GET  /pricing                       --> platform.pricing
  GET  /register                      --> platform.register
  POST /register                      --> platform.register.submit

Tenant Scoped Routes (/t/{tenant}/ - Middleware: tenant.resolve):
  Teacher Landing & Enrollment:
    GET  /t/{tenant}/                 --> tenant.home
    POST /t/{tenant}/enroll           --> tenant.enroll.submit

  Student Portal:
    GET  /t/{tenant}/student/login          --> tenant.student.login
    POST /t/{tenant}/student/login          --> tenant.student.login.submit
    GET  /t/{tenant}/student/dashboard      --> tenant.student.dashboard
    GET  /t/{tenant}/student/logout         --> tenant.student.logout
    GET  /t/{tenant}/student/exams/{id}     --> tenant.student.exams.show
    GET  /t/{tenant}/student/exams/{id}/start --> tenant.student.exams.start
    POST /t/{tenant}/student/exams/{id}/submit --> tenant.student.exams.submit
    GET  /t/{tenant}/student/exams/{id}/result --> tenant.student.exams.result
    GET  /t/{tenant}/student/homeworks/{id}  --> tenant.student.homeworks.show
    POST /t/{tenant}/student/homeworks/{id}/submit --> tenant.student.homeworks.submit

  Parent Portal:
    GET  /t/{tenant}/parent/login           --> tenant.parent.login
    POST /t/{tenant}/parent/login           --> tenant.parent.login.submit
    GET  /t/{tenant}/parent/dashboard       --> tenant.parent.dashboard
    POST /t/{tenant}/parent/payment         --> tenant.parent.payment.submit
    GET  /t/{tenant}/parent/logout          --> tenant.parent.logout
    GET  /t/{tenant}/parent/exams/{id}      --> tenant.parent.exams.show
    GET  /t/{tenant}/parent/exams/{id}/result --> tenant.parent.exams.result

  Teacher SaaS Subscription Renewal:
    GET  /t/{tenant}/subscription/status    --> tenant.subscription.status
    GET  /t/{tenant}/subscription/pay       --> tenant.subscription.pay
    POST /t/{tenant}/subscription/pay       --> tenant.subscription.pay.submit
    GET  /t/{tenant}/subscription/history   --> tenant.subscription.history

Filament Dashboards:
  /admin/{tenant}                     --> Teacher Control Panel
  /super-admin                        --> Platform SuperAdmin Dashboard
```

---

## 5. Troubleshooting & Debugging Guide (دليل حل المشاكل البرمجية)

### Issue 1: `Route [xxx] not defined`
- **Cause:** Calling a route name without passing the mandatory `tenant` route parameter.
- **Fix:** Always provide `tenant` slug:
  `route('tenant.student.dashboard', ['tenant' => $currentTenant->slug])`

### Issue 2: `The model [Tenant] does not have a relationship named [xxx]`
- **Cause:** Filament expects an Eloquent relationship on `Tenant.php` matching the resource model.
- **Fix:**
  1. Add the `HasMany` relation in `app/Models/Tenant.php`:
     ```php
     public function myModels(): HasMany {
         return $this->hasMany(MyModel::class);
     }
     ```
  2. Set `$tenantRelationshipName = 'myModels';` in the Filament Resource class.

### Issue 3: Newly created record not visible to students
- **Checklist:**
  - Is `status` set to `'published'`?
  - Does the record's `stage_id` match the student's `stage_id`?
  - If `group_id` is specified, is the student an active member of that group?
  - Is `tenant_id` correctly assigned?

### Issue 4: Changes to views, config, or routes not reflecting
- **Fix:** Clear all compiled caches:
  ```bash
  php artisan optimize:clear
  ```
