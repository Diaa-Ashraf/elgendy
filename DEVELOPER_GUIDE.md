# الدليل الهندسي الشامل لتطوير وصيانة المنظومة (Developer Guide)

## نظرة عامة على النظام
- **نوع النظام:** منصة سحابية متعددة المستأجرين (Multi-Tenant SaaS) لإدارة المعلمين والمراكز التعليمية والدروس الخصوصية.
- **التقنيات المستخدمة:** Laravel 12 (PHP 8.3+) | Filament v3/v5 | Tailwind CSS | MySQL | Alpine.js | Redis & Queues.
- **الهدف من النظام:** تمكين المدرس من إدارة طلابه، الحضور والغياب، الواجبات، الامتحانات (الإلكترونية والورقية)، الملازم، الحسابات المالية، وإشعارات الواتساب التلقائية من خلال نطاق/مسار خاص بكل مدرس مع بوابات منفصلة للطلاب وأولياء الأمور.

---

## 1. معمارية الـ Multi-Tenancy وعزل البيانات

### 1.1 نمط المعمارية: قاعدة بيانات واحدة مع عزل الصفوف (Single DB - Row Level Tenancy)
- كل مدرس أو مركز تعليمي مسجل كصف في جدول `tenants`.
- يتم التعرف على المدرس من خلال المعرف النصي الفريد `slug` (مثال: `mr-diaa`).
- جميع الجداول التابعة للمدرسين تحتوي على عمود مفهرس: `tenant_id`.

### 1.2 دورة حياة الطلب (Request Lifecycle) وتحديد المدرس
1. **دخول الرابط:** يدخل المستخدم رابط يبدأ بـ `/t/{tenant}/...` (مثال: `/t/mr-diaa/student/dashboard`).
2. **اعتراض الوسيط (Middleware):** وسيط `ResolveTenant` (`app/Http/Middleware/ResolveTenant.php`) يعترض الطلب:
   - يقرأ قيمة `{tenant}` من الرابط.
   - يبحث عن المدرس في جدول `tenants`؛ وإذا لم يجده يرجع خطأ 404.
   - يحقن المدرس داخل كلاس `TenantContext` كـ Singleton (`app/Services/TenantContext.php`).
   - يشارك المتغير `$currentTenant` مع جميع واجهات Blade.
3. **العزل التلقائي للبيانات (Global Scope):**
   - أي موديل يستخدم Trait `BelongsToTenant` (`app/Traits/BelongsToTenant.php`) يطبق Global Scope باسم `tenant`.
   - جميع استعلامات القراءة والتعديل والحذف يتم تصفيتها تلقائياً بشرط: `WHERE tenant_id = currentTenant->id`.
   - عند إنشاء أي سجل جديد، يتم ملء `tenant_id` تلقائياً من `TenantContext::id()`.
4. **عزل لوحة تحكم المعلم (Filament Admin):**
   - في `app/Providers/Filament/AdminPanelProvider.php`، يتم تفعيل العزل عبر `.tenant(Tenant::class, slugAttribute: 'slug')` للوحة `/admin/{tenant}`.
   - وسيط `SyncFilamentTenant` (`app/Http/Middleware/SyncFilamentTenant.php`) يضمن مزامنة المدرس النشط في Filament مع `TenantContext`.

---

## 2. دليل الخدمات الشامل (Services Matrix - 19 خدمة)

جميع العمليات المعقدة والمنطق البرمجي والحسابات موجودة حصراً في مجلد `app/Services/`:

### 1. سياق المدرس الحالي (`app/Services/TenantContext.php`)
- **الوظيفة:** Singleton يحتفظ ببيانات المدرس الحالي طوال مدة تنفيذ الطلب (Request).
- **الدوال الأساسية:** `set(Tenant $tenant)`, `get(): ?Tenant`, `id(): ?int`.
- **المستخدمون:** `ResolveTenant`, `BelongsToTenant`, `SyncFilamentTenant`.

### 2. خدمة الواجبات المنزلية (`app/Services/HomeworkService.php`)
- **الوظيفة:** توزيع الواجبات على الطلاب، استقبال الحلول والمرفقات، والتصحيح التلقائي لأسئلة الاختيار من متعدد.
- **الدوال الأساسية:**
  - `getStudentHomeworks(Student $student)`: جلب الواجبات المنشورة المطابقة لمرحلة ومجموعة الطالب.
  - `submitHomework(Student $student, Homework $homework, array $data)`: حفظ الإجابات أو المرفقات وتصحيحها.
  - `autoGradeQuestions(HomeworkSubmission $submission)`: مطابقة إجابات الطالب بالإجابات النموذجية واحتساب الدرجة.
  - `getSubmissionStats(Homework $homework)`: إحصائيات التسليمات (المصححة، المتأخرة، قيد المراجعة).
- **المستخدمون:** `StudentPortalController`, `ParentPortalController`, `HomeworkResource`.

### 3. خدمة الامتحانات الإلكترونية (`app/Services/OnlineExamService.php`)
- **الوظيفة:** إدارة محرك الامتحانات الأونلاين، المؤقت التنازلي، منع الغش، التصحيح التلقائي الفوري، وتشخيص نقاط القوة والضعف للطالب.
- **الدوال الأساسية:**
  - `startAttempt(Student $student, Exam $exam)`: بدء جلسة الامتحان وتفعيل المؤقت.
  - `submitAttempt(OnlineExamAttempt $attempt, array $answers)`: التصحيح الفوري واستخراج تقرير الدرجات والدروس التي يحتاج الطالب التركيز عليها.
  - `getStudentExamHistory(Student $student)`: سجل امتحانات الطالب السابقة.
- **المستخدمون:** `OnlineExamController`, `ExamResource`.

### 4. خدمة الحضور والغياب (`app/Services/AttendanceService.php`)
- **الوظيفة:** تسجيل حضور وغياب الطلاب في الحصص، تتبع مرات الغياب المتتالية، وإطلاق التنبيهات.
- **الدوال الأساسية:**
  - `recordAttendance(GroupSession $session, Student $student, string $status, ?string $notes)`
  - `bulkRecord(GroupSession $session, array $records)`
  - `getConsecutiveAbsenceCount(Student $student)`
- **المستخدمون:** `GroupSessionResource`, `QrScanner` (Page), `Attendance`.

### 5. خدمة إشعارات الواتساب (`app/Services/WhatsAppNotificationService.php`)
- **الوظيفة:** إرسال رسائل واتساب آلية وفورية لأولياء الأمور فور تسجيل الحضور أو الغياب أو رصد الدرجات أو سداد الرسوم.
- **الدوال الأساسية:**
  - `sendAttendanceAlert(Attendance $attendance)`
  - `sendExamResultAlert(Student $student, Exam $exam, float $score, float $total)`
  - `sendPaymentReceiptAlert(StudentPayment $payment)`
- **المستخدمون:** `AttendanceService`, `OnlineExamService`, `StudentPaymentResource`.

### 6. خدمة كشف الحساب الأكاديمي (`app/Services/StudentLedgerService.php`)
- **الوظيفة:** حساب الذمة المالية الشاملة للطالب (إجمالي المطلوب - إجمالي المسدد = المتبقي).
- **الدوال الأساسية:**
  - `getFullLedger(Student $student)`: كشف حساب تفصيلي بالحضور، الملازم المستلمة، والمدفوعات.
  - `getBalance(Student $student)`: الرصيد المتبقي كرقم.
- **المستخدمون:** `StudentResource`, `ParentPortalController`, `StudentPdfController`.

### 7. خدمة المدفوعات والخصومات (`app/Services/PaymentService.php`)
- **الوظيفة:** إنشاء سندات القبض، معالجة الدفعات الجزئية، تطبيق الخصومات، وتوليد الإيصالات.
- **الدوال الأساسية:**
  - `createPayment(Student $student, array $data)`
  - `applyDiscount(Student $student, Discount $discount)`
- **المستخدمون:** `StudentPaymentResource`, `OnlinePaymentRequestResource`.

### 8. خدمة استيراد الأسئلة (`app/Services/QuestionImportService.php`)
- **الوظيفة:** استيراد الأسئلة من ملفات Excel و Word وتحليل النصوص بالذكاء الاصطناعي إلى بنك الأسئلة.
- **الدوال الأساسية:**
  - `importFromExcel($filePath, $stageId, $subjectId)`
  - `parseRawTextWithAi(string $rawText)`
- **المستخدمون:** `QuestionResource`, `ExamResource`.

### 9. خدمة استيراد الطلاب الجماعي (`app/Services/StudentImportService.php`)
- **الوظيفة:** استيراد قوائم الطلاب من Excel/CSV، فحص تكرار الهواتف، وتسكينهم في المجموعات تلقائياً.
- **الدوال الأساسية:**
  - `processImport(StudentImport $importRecord)`
  - `validateRows(array $rows)`
- **المستخدمون:** `StudentImports` (Page), `ProcessStudentImportJob`.

### 10. خدمة الامتحانات الورقية (`app/Services/ExamService.php`)
- **الوظيفة:** رصد وتوثيق نتائج الامتحانات الورقية، حساب المتوسطات، وقائمة الأوائل.
- **الدوال الأساسية:**
  - `recordResults(Exam $exam, array $results)`
  - `getTopPerformers(Exam $exam, int $limit = 10)`
- **المستخدمون:** `ExamResource`, `ExamResult`.

### 11. خدمة المصروفات العامة (`app/Services/ExpenseService.php`)
- **الوظيفة:** قيد المصروفات وتبويبها ومتابعة بنود الصرف بالسنتر.
- **الدوال الأساسية:**
  - `recordExpense(array $data)`
  - `getExpensesSummaryByPeriod($startDate, $endDate)`
- **المستخدمون:** `ExpenseResource`, `ExpenseCategoryResource`.

### 12. خدمة الرواتب والاستحقاقات (`app/Services/SalaryService.php`)
- **الوظيفة:** حساب مرتبات وساعات عمل المساعدين والخصومات والمكافآت.
- **الدوال الأساسية:**
  - `calculateAssistantSalary(User $assistant, $month, $year)`
- **المستخدمون:** `SalaryResource`.

### 13. خدمة التقارير الإدارية والمالية (`app/Services/ReportService.php`)
- **الوظيفة:** إعداد تقارير الأرباح الشهرية، التدفقات النقدية، نسب الحضور، وربحية المجموعات.
- **الدوال الأساسية:**
  - `getExecutiveOverview()`
  - `getMonthlyProfitabilityReport($year, $month)`
- **المستخدمون:** `Reports` (Page), `AdvancedAnalytics` (Page), `ExecutiveStatsWidget`.

### 14. خدمة الإعدادات وهوية السنتر (`app/Services/SettingService.php`)
- **الوظيفة:** قراءة وتعديل إعدادات السنتر (اللوجو، أرقام الكاش، بيانات انستاباي، إعدادات الواتساب) مع دعم الكاش (Cache).
- **الدوال الأساسية:**
  - `get(string $key, $default = null)`
  - `set(string $key, $value)`
  - `url(string $key)`: رابط الملفات المرفوعة.
- **المستخدمون:** `ManageSettings` (Page), `ParentPortalController`, وجميع الواجهات.

### 15. خدمة اشتراكات المنصة السحابية (`app/Services/SubscriptionService.php`)
- **الوظيفة:** إدارة اشتراكات المعلمين بالمنصة، فحص صلاحية التجربة المجانية، وتجديد الباقات.
- **الدوال الأساسية:**
  - `isSubscriptionActive(Tenant $tenant): bool`
  - `renewSubscription(Tenant $tenant, Plan $plan, int $months)`
  - `recordPayment(Subscription $subscription, array $data)`
- **المستخدمون:** `CheckSubscription` (Middleware), `SubscriptionPaymentController`, لوحة SuperAdmin.

### 16. خدمة تسجيل معلم جديد (`app/Services/TenantRegistrationService.php`)
- **الوظيفة:** إجراءات تسجيل سنتر جديد: إنشاء الـ Tenant، حساب المدير، وتعيين الفترة التجريبية.
- **الدوال الأساسية:**
  - `registerTenant(array $data): Tenant`
- **المستخدمون:** `PlatformController@register`.

### 17. خدمة تصدير بيانات السنتر (`app/Services/TenantExportService.php`)
- **الوظيفة:** تجميع وتصدير كافة بيانات السنتر (الطلاب، الحسابات، الدرجات) في ملف مضغوط.
- **الدوال الأساسية:**
  - `exportTenantData(Tenant $tenant): string`
- **المستخدمون:** `TenantResource` في السوبر أدمن.

### 18. خدمة النسخ الاحتياطي (`app/Services/BackupService.php`)
- **الوظيفة:** أخذ نسخ احتياطية لقاعدة البيانات وتحميلها واستعادتها.
- **الدوال الأساسية:**
  - `createDatabaseBackup(): string`
  - `getAvailableBackups(): array`
- **المستخدمون:** `Backups` (Page).

### 19. خدمة التنبيهات الداخلية (`app/Services/NotificationService.php`)
- **الوظيفة:** التنبيهات الداخلية في لوحة التحكم ورسائل الـ SMS.
- **المستخدمون:** لوحات Filament وواجهات البوابات.

---

## 3. دليل الموديولات وملفات كل موديول (Code Map)

إذا أردت تعديل أو تطوير أي موديول، إليك قائمة بجميع الملفات المرتبطة به:

---

### الموديول 1: إدارة الطلاب والاستيراد الجماعي
- **الجداول في قاعدة البيانات:** `students`, `student_imports`
- **النماذج (Models):** `app/Models/Student.php`, `app/Models/StudentImport.php`
- **الخدمات (Services):** `app/Services/StudentImportService.php`, `app/Services/StudentLedgerService.php`
- **المهام في الخلفية (Jobs):** `app/Jobs/ProcessStudentImportJob.php`
- **لوحة التحكم (Filament):**
  - المورد: `app/Filament/Resources/StudentResource.php`
  - صفحة الاستيراد: `app/Filament/Pages/StudentImports.php`
- **طباعة المستندات (PDF):** `app/Http/Controllers/StudentPdfController.php` (`printCard`, `printLedger`)
- **دخول الطالب:** `StudentPortalController.php` (تسجيل الدخول بكود الطالب وهاتف ولي الأمر).

---

### الموديول 2: المجموعات الدراسية والجداول والحصص
- **الجداول في قاعدة البيانات:** `groups`, `group_schedules`, `group_sessions`, `group_student_pivot`
- **النماذج (Models):** `app/Models/Group.php`, `app/Models/GroupSchedule.php`, `app/Models/GroupSession.php`
- **لوحة التحكم (Filament):**
  - `app/Filament/Resources/GroupResource.php` (بيانات المجموعة، المادة، المرحلة، السعر)
  - `app/Filament/Resources/GroupSessionResource.php` (تسجيل الحصص والحضور والدرس المشروح)
  - `app/Filament/Pages/WeeklyTimetable.php` (جدول المواعيد الأسبوعي)

---

### الموديول 3: الحضور والغياب والباركود الذكي
- **الجداول في قاعدة البيانات:** `attendances`
- **النماذج (Models):** `app/Models/Attendance.php`
- **الخدمات (Services):** `app/Services/AttendanceService.php`, `app/Services/WhatsAppNotificationService.php`
- **لوحة التحكم (Filament):**
  - ماسح الـ QR السريع: `app/Filament/Pages/QrScanner.php` (مسح بالكاميرا أو قارئ الباركود)
  - شيت الحصة: `app/Filament/Resources/GroupSessionResource.php`

---

### الموديول 4: بنك الأسئلة والذكاء الاصطناعي
- **الجداول في قاعدة البيانات:** `questions`
- **النماذج (Models):** `app/Models/Question.php`
- **الخدمات (Services):** `app/Services/QuestionImportService.php`
- **لوحة التحكم (Filament):** `app/Filament/Resources/QuestionResource.php`
- **الأنواع المدعومة:** اختيار من متعدد فردي وجماعي، صح وخطأ، صور الأسئلة، التصنيف حسب الموضوع، ومستوى الصعوبة والتفسير العلمي.

---

### الموديول 5: الامتحانات الورقية والإلكترونية والكويزات
- **الجداول في قاعدة البيانات:** `exams`, `exam_questions`, `online_exam_attempts`, `exam_results`
- **النماذج (Models):** `app/Models/Exam.php`, `app/Models/OnlineExamAttempt.php`, `app/Models/ExamResult.php`
- **الخدمات (Services):** `app/Services/OnlineExamService.php`, `app/Services/ExamService.php`
- **لوحة التحكم (Filament):**
  - `app/Filament/Resources/ExamResource.php`
  - إدارة الأسئلة: `app/Filament/Resources/ExamResource/RelationManagers/QuestionsRelationManager.php`
- **الكنترولر:** `app/Http/Controllers/OnlineExamController.php`
- **واجهات Blade:**
  - تفاصيل الامتحان: `resources/views/parent-portal/exams/show.blade.php`
  - شاشة الامتحان التفاعلية والمؤقت: `resources/views/parent-portal/exams/take.blade.php`
  - تقرير النتيجة ونقاط الضعف: `resources/views/parent-portal/exams/result.blade.php`

---

### الموديول 6: الواجبات المنزلية والمهام
- **الجداول في قاعدة البيانات:** `homeworks`, `homework_questions`, `homework_submissions`
- **النماذج (Models):** `app/Models/Homework.php`, `app/Models/HomeworkSubmission.php`
- **الخدمات (Services):** `app/Services/HomeworkService.php`
- **لوحة التحكم (Filament):**
  - المورد الأساسي: `app/Filament/Resources/HomeworkResource.php`
  - إدارة أسئلة الواجب: `app/Filament/Resources/HomeworkResource/RelationManagers/QuestionsRelationManager.php`
  - تسليمات الطلاب والتصحيح: `app/Filament/Resources/HomeworkResource/RelationManagers/SubmissionsRelationManager.php`
- **الكنترولر:** `app/Http/Controllers/StudentPortalController.php` (`showHomework`, `submitHomework`)
- **واجهات Blade:**
  - حل وتسليم الواجب للطالب: `resources/views/student-portal/homework-show.blade.php`
  - متابعة الواجبات لولي الأمر: `resources/views/parent-portal/dashboard.blade.php`

---

### الموديول 7: الحسابات وسداد الرسوم والتحصيل
- **الجداول في قاعدة البيانات:** `student_payments`, `expenses`, `expense_categories`, `salaries`, `discounts`, `online_payment_requests`
- **النماذج (Models):** `app/Models/StudentPayment.php`, `app/Models/Expense.php`, `app/Models/Salary.php`, `app/Models/OnlinePaymentRequest.php`, `app/Models/Discount.php`
- **الخدمات (Services):** `app/Services/StudentLedgerService.php`, `app/Services/PaymentService.php`, `app/Services/ExpenseService.php`, `app/Services/SalaryService.php`
- **لوحة التحكم (Filament):**
  - إيصالات القبض: `app/Filament/Resources/StudentPaymentResource.php`
  - مراجعة واعتماد التحويلات الإلكترونية (InstaPay/Cash): `app/Filament/Resources/OnlinePaymentRequestResource.php`
  - المصروفات والرواتب: `ExpenseResource.php`, `SalaryResource.php`

---

### الموديول 8: الملازم والمطبوعات والمخازن
- **الجداول في قاعدة البيانات:** `study_materials`, `student_material_deliveries`, `inventory_items`, `inventory_movements`
- **النماذج (Models):** `app/Models/StudyMaterial.php`, `app/Models/StudentMaterialDelivery.php`, `app/Models/InventoryItem.php`
- **لوحة التحكم (Filament):**
  - `app/Filament/Resources/StudyMaterialResource.php`
  - `app/Filament/Resources/StudentMaterialDeliveryResource.php`

---

### الموديول 9: البوابات والمواقع التعريفية
- **موقع المعلم وحجز الطلاب الجدد:**
  - الرابط: `/t/{tenant}`
  - الكنترولر: `app/Http/Controllers/HomeController.php`
  - الواجهة: `resources/views/tenant-landing/index.blade.php`
  - قبول طلبات الحجز: `app/Filament/Resources/StudentApplicationResource.php`
- **بوابة الطالب:**
  - الروابط: `/t/{tenant}/student/*`
  - الكنترولر: `app/Http/Controllers/StudentPortalController.php`
  - الواجهات: `resources/views/student-portal/login.blade.php`, `resources/views/student-portal/dashboard.blade.php`
- **بوابة ولي الأمر:**
  - الروابط: `/t/{tenant}/parent/*`
  - الكنترولر: `app/Http/Controllers/ParentPortalController.php`
  - الواجهات: `resources/views/parent-portal/login.blade.php`, `resources/views/parent-portal/dashboard.blade.php`

---

### الموديول 10: اشتراكات المعلمين بالسحابة ولوحة السوبر أدمن
- **الجداول في قاعدة البيانات:** `tenants`, `plans`, `subscriptions`, `subscription_payments`
- **النماذج (Models):** `app/Models/Tenant.php`, `app/Models/Plan.php`, `app/Models/Subscription.php`, `app/Models/SubscriptionPayment.php`
- **الوسيط (Middleware):** `app/Http/Middleware/CheckSubscription.php`
- **الخدمات (Services):** `app/Services/SubscriptionService.php`
- **لوحة السوبر أدمن (`/super-admin`):**
  - مزود اللوحة: `app/Providers/Filament/SuperAdminPanelProvider.php`
  - الموارد: `TenantResource.php`, `PlanResource.php`, `SubscriptionResource.php`, `SubscriptionPaymentResource.php`
- **شاشة سداد وتجديد اشتراك المعلم:**
  - الكنترولر: `app/Http/Controllers/SubscriptionPaymentController.php`
  - الواجهات: `resources/views/subscription/status.blade.php`, `resources/views/subscription/pay.blade.php`

---

### الموديول 11: الإعدادات والنسخ الاحتياطي
- **النماذج (Models):** `app/Models/TenantSetting.php`
- **الخدمات (Services):** `app/Services/SettingService.php`, `app/Services/BackupService.php`
- **صفحات Filament:**
  - إعدادات السنتر: `app/Filament/Pages/ManageSettings.php` (اللوجو، أرقام الكاش، حسابات انستاباي، إعدادات الواتساب)
  - النسخ الاحتياطي: `app/Filament/Pages/Backups.php`

---

## 4. شجرة المسارات المعتمدة (Routing Architecture)

جميع المسارات معرفة ومجمعة داخل `routes/web.php`:

### مسارات المنصة العامة والتسويق (Platform Marketing):
- `GET  /` ⬅️ `platform.home`
- `GET  /pricing` ⬅️ `platform.pricing`
- `GET  /register` ⬅️ `platform.register`
- `POST /register` ⬅️ `platform.register.submit`

### مسارات المدرسين المعزولة (`/t/{tenant}/` مع وسيط `tenant.resolve`):
- **موقع المدرس وحجز الطلاب:**
  - `GET  /t/{tenant}/` ⬅️ `tenant.home`
  - `POST /t/{tenant}/enroll` ⬅️ `tenant.enroll.submit`
- **بوابة الطالب:**
  - `GET  /t/{tenant}/student/login` ⬅️ `tenant.student.login`
  - `POST /t/{tenant}/student/login` ⬅️ `tenant.student.login.submit`
  - `GET  /t/{tenant}/student/dashboard` ⬅️ `tenant.student.dashboard`
  - `GET  /t/{tenant}/student/logout` ⬅️ `tenant.student.logout`
  - `GET  /t/{tenant}/student/exams/{id}` ⬅️ `tenant.student.exams.show`
  - `GET  /t/{tenant}/student/exams/{id}/start` ⬅️ `tenant.student.exams.start`
  - `POST /t/{tenant}/student/exams/{id}/submit` ⬅️ `tenant.student.exams.submit`
  - `GET  /t/{tenant}/student/exams/{id}/result` ⬅️ `tenant.student.exams.result`
  - `GET  /t/{tenant}/student/homeworks/{id}` ⬅️ `tenant.student.homeworks.show`
  - `POST /t/{tenant}/student/homeworks/{id}/submit` ⬅️ `tenant.student.homeworks.submit`
- **بوابة ولي الأمر:**
  - `GET  /t/{tenant}/parent/login` ⬅️ `tenant.parent.login`
  - `POST /t/{tenant}/parent/login` ⬅️ `tenant.parent.login.submit`
  - `GET  /t/{tenant}/parent/dashboard` ⬅️ `tenant.parent.dashboard`
  - `POST /t/{tenant}/parent/payment` ⬅️ `tenant.parent.payment.submit`
  - `GET  /t/{tenant}/parent/logout` ⬅️ `tenant.parent.logout`
  - `GET  /t/{tenant}/parent/exams/{id}` ⬅️ `tenant.parent.exams.show`
  - `GET  /t/{tenant}/parent/exams/{id}/result` ⬅️ `tenant.parent.exams.result`
- **إدارة اشتراك المعلم:**
  - `GET  /t/{tenant}/subscription/status` ⬅️ `tenant.subscription.status`
  - `GET  /t/{tenant}/subscription/pay` ⬅️ `tenant.subscription.pay`
  - `POST /t/{tenant}/subscription/pay` ⬅️ `tenant.subscription.pay.submit`

### لوحات تحكم Filament:
- `/admin/{tenant}` ⬅️ لوحة تحكم المعلم والسنتر
- `/super-admin` ⬅️ لوحة إدارة المنظومة السحابية

---

## 5. لوحات تحكم Filament وتكوين الـ Tenancy

1. **لوحة تحكم المعلم والسنتر (Admin Panel):**
   - **المزود:** `app/Providers/Filament/AdminPanelProvider.php`
   - **الرابط:** `/admin/{tenant}`
   - **العزل:** مفعل عبر `.tenant(Tenant::class, slugAttribute: 'slug')`
   - **الموارد:** مجلد `app/Filament/Resources/`
   - **الصفحات:** مجلد `app/Filament/Pages/`
   - **الودجات:** مجلد `app/Filament/Widgets/`

2. **لوحة السوبر أدمن (SuperAdmin Panel):**
   - **المزود:** `app/Providers/Filament/SuperAdminPanelProvider.php`
   - **الرابط:** `/super-admin`
   - **العزل:** غير مقيدة بـ Tenant لإدارة ومراقبة جميع المشتركين والمدفوعات.
   - **الموارد:** مجلد `app/Filament/SuperAdmin/Resources/`

---

## 6. دليل حل المشاكل البرمجية الشائعة (Troubleshooting)

### المشكلة 1: خطأ `Route [xxx] not defined`
- **السبب:** استدعاء مسار معزول بدون تمرير متغير الـ `tenant`.
- **الحل:** مرر اسم المدرس دائماً:
  ```php
  route('tenant.student.dashboard', ['tenant' => $currentTenant->slug])
  ```

### المشكلة 2: خطأ `The model [Tenant] does not have a relationship named [xxx]` في Filament
- **السبب:** Filament يبحث عن علاقة Eloquent داخل `Tenant.php` تربطه بجدول الموديل الجديد.
- **الحل:**
  1. أضف العلاقة داخل `app/Models/Tenant.php`:
     ```php
     public function myModels(): HasMany {
         return $this->hasMany(MyModel::class);
     }
     ```
  2. حدد اسم العلاقة في ملف الـ Resource:
     ```php
     protected static ?string $tenantRelationshipName = 'myModels';
     ```

### المشكلة 3: بيانات مدرس تظهر لمدرس آخر (Data Leak)
- **السبب:** نسيان استخدام Trait `BelongsToTenant`.
- **الحل:** أضف `use BelongsToTenant;` داخل الـ Model وتأكد من وجود عمود `tenant_id` في جدول قاعدة البيانات.

### المشكلة 4: عنصر تم حفظه في اللوحة ولا يظهر للطالب أو ولي الأمر
- **قائمة الفحص:**
  - هل حالة العنصر `published` وليست `draft`؟
  - هل `stage_id` تطابق مرحلة الطالب؟
  - إذا كان مرتبطاً بمجموعة `group_id`، هل الطالب منضم لتلك المجموعة؟
  - هل `tenant_id` متطابق؟

### المشكلة 5: تعديلات الكود أو الواجهات لا تنعكس في المتصفح
- **الحل الفوري:** تشغيل أمر تنظيف الكاش الشامل:
  ```bash
  php artisan optimize:clear
  ```
