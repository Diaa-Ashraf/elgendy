<div dir="rtl" style="text-align: right; font-family: 'Cairo', 'Segoe UI', Tahoma, sans-serif;">

# 🏛️ الموسوعة الهندسية الشاملة لتطوير وصيانة النظام (Complete Developer Architectural Reference)

> **اسم المشروع:** منصة إدارة المراكز التعليمية والدروس الخصوصية الذكية (Smart Academy Multi-Tenant SaaS)  
> **البيئة والتقنيات الأساسية:** Laravel 12 | PHP 8.3+ | Filament v3/v5 | Tailwind CSS | MySQL | Alpine.js | Redis/Queue  
> **الهدف من هذا الدليل:** مرجع تشغيلي وهندسي كامل لأي مبرمج يدخل المشروع؛ يشرح الترابط الدقيق بين (قاعدة البيانات -> النماذج Models -> طبقة الخدمات Services -> لوحات تحكم Filament -> وحدات التحكم Controllers -> واجهات المستخدم Blade) بحيث إذا أردت تعديل أو إصلاح أي شيء في أي موديول، تعرف بدقة كل الملفات المرتبطة به وسلسلة تدفق البيانات.

---

## 📑 الفهرس العام
1. [معمارية الـ Multi-Tenant SaaS وكيفية عزل البيانات وتدفق الطلبات](#1-معمارية-الـ-multi-tenant-saas)
2. [خريطة طبقة الخدمات الـ 19 (All 19 Services Detailed Matrix)](#2-خريطة-طبقة-الخدمات-الـ-19)
3. [دليل الموديولات الشامل ومصفوفة الترابط (Module-by-Module Full Map)](#3-دليل-الموديولات-الشامل-ومصفوفة-الترابط)
   - [موديول 1: الطلاب وإدارة التسجيل والاستيراد الذكي (Students & Import)](#موديول-1-الطلاب-وإدارة-التسجيل-والاستيراد)
   - [موديول 2: المجموعات الدراسية والمواعيد والحصص (Groups & Sessions)](#موديول-2-المجموعات-الدراسية-والمواعيد-والحصص)
   - [موديول 3: الحضور والغياب والباركود الذكي (Attendance & Smart QR)](#موديول-3-الحضور-والغياب-والباركود-الذكي)
   - [موديول 4: بنك الأسئلة والاستيراد بالذكاء الاصطناعي (Question Bank & AI)](#موديول-4-بنك-الأسئلة-والاستيراد-بالذكاء-الاصطناعي)
   - [موديول 5: الامتحانات الورقية والإلكترونية والكويزات (Exams & Quiz Engine)](#موديول-5-الامتحانات-الورقية-والإلكترونية)
   - [موديول 6: الواجبات المنزلية والتسليم الأونلاين (Homeworks & Submissions)](#موديول-6-الواجبات-المنزلية-والتسليم-الأونلاين)
   - [موديول 7: الحسابات والماليات وكشف حساب الطالب (Finance & Student Ledger)](#موديول-7-الحسابات-والماليات-وكشف-الحساب)
   - [موديول 8: الملازم والمطبوعات والمخازن (Study Materials & Inventory)](#موديول-8-الملازم-والمطبوعات-والمخازن)
   - [موديول 9: بوابات المتابعة (الطالب، ولي الأمر، موقع المدرس التعريفي)](#موديول-9-بوابات-المتابعة-والمواقع-التعريفية)
   - [موديول 10: إدارة اشتراك المعلم ولوحة السوبر أدمن (SaaS Platform & Subscriptions)](#موديول-10-إدارة-اشتراك-المعلم-والسوبر-أدمن)
   - [موديول 11: الإعدادات، الواتساب، والنسخ الاحتياطي (Settings, WhatsApp & Backup)](#موديول-11-الإعدادات-والواتساب-والنسخ-الاحتياطي)
4. [شجرة المسارات المعتمدة وقواعد التوجيه (Routing Architecture)](#4-شجرة-المسارات-المعتمدة)
5. [لوحات تحكم Filament وتكوين الـ Multi-Tenancy بها](#5-لوحات-تحكم-filament)
6. [دليل كشف وإصلاح الأخطاء خطوة بخطوة (Master Troubleshooting & Debugging Flow)](#6-دليل-كشف-وإصلاح-الأخطاء)

---

## 1. معمارية الـ Multi-Tenant SaaS

### أ. فكرة النظام الأساسية
النظام عبارة عن منصة سحابية تدير مئات المدرسين والمراكز التعليمية من قاعدة بيانات واحدة (`Single Database Multi-Tenancy` عبر `Row-Level Isolation`).
* كل مدرس أو سنتر يسمى **`Tenant`** ولديه معرف فريد `id` ورابط مخصص `slug` (مثال: `mr-diaa`).
* جميع جداول النظام الأكاديمية والمالية تحتوي على عمود `tenant_id` مفهرس (Indexed Foreign Key).

### ب. دورة حياة الطلب (Request Lifecycle) وعزل البيانات:

```
                  ┌─────────────────────────────────────────────────────────┐
                  │                 طلب المستخدم (HTTP Request)             │
                  └────────────────────────────┬────────────────────────────┘
                                               │
             ┌─────────────────────────────────┴─────────────────────────────────┐
             ▼                                                                   ▼
    [المنصة العامة / السوبر أدمن]                                     [مسارات المدرسين والسناتر]
       - النطاق: / أو /register                                            - النطاق: /t/{tenant}/...
       - لوحة: /super-admin                                              - لوحة: /admin/{tenant}
                                                                                 │
                                                                                 ▼
                                                                     [ResolveTenant Middleware]
                                                                     1. يستخرج الـ slug من الرابط.
                                                                     2. يبحث عن المدرس في جدول tenants.
                                                                     3. يحقن المدرس في Singleton: TenantContext.
                                                                     4. يشارك المتغير $currentTenant مع الواجهات.
                                                                                 │
                                                                                 ▼
                                                                        [BelongsToTenant Trait]
                                                                     - يُطبّق Global Scope تلقائياً على كل Query:
                                                                       `WHERE tenant_id = currentTenant->id`
                                                                     - يحقن tenant_id أوتوماتيكياً عند إنشاء أي سجل.
```

---

## 2. خريطة طبقة الخدمات الـ 19 (All 19 Services Detailed Matrix)

طبقة الـ **`Services`** هي قلب النظام ومكان تنفيذ كل منطق الأعمال والعمليات المعقدة. لا نكتب منطق عمل داخل الكنترولر أو داخل الـ Resource مباشرة؛ الكنترولر فقط يستدعي الـ Service.

| اسم الـ Service | المسار المباشر | الوظيفة الهندسية الأساسية | الكنترولرز / الـ Resources المرتبطة بها |
| :--- | :--- | :--- | :--- |
| **`TenantContext`** | `app/Services/TenantContext.php` | Singleton يحمل كائن المدرس الحالي طوال مدة الـ Request. | `ResolveTenant`, `SyncFilamentTenant`, `BelongsToTenant` |
| **`HomeworkService`** | `app/Services/HomeworkService.php` | جلب واجبات الطالب، تسليم الواجب، التصحيح التلقائي لأسئلة الـ MCQ، إحصائيات التسليم. | `StudentPortalController`, `ParentPortalController`, `HomeworkResource` |
| **`OnlineExamService`** | `app/Services/OnlineExamService.php` | بدء محاولة الامتحان، إدارة المؤقت، التصحيح التلقائي الفوري، تشخيص نقاط القوة والضعف (AI Analytics). | `OnlineExamController`, `ExamResource` |
| **`AttendanceService`** | `app/Services/AttendanceService.php` | تسجيل حضور وغياب الطلاب، فحص مرات الغياب المتتالية، تشغيل التنبيهات. | `GroupSessionResource`, `QrScanner`, `Attendance` |
| **`WhatsAppNotificationService`** | `app/Services/WhatsAppNotificationService.php` | إرسال رسائل واتساب آلية لأولياء الأمور (تأكيد الحضور، إشعار الغياب، درجات الامتحانات). | `AttendanceService`, `OnlineExamService`, `StudentPaymentResource` |
| **`NotificationService`** | `app/Services/NotificationService.php` | إشعارات النظام الداخلية والتنبيهات المنبثقة ورسائل الـ SMS. | Filament Panels, Student & Parent Portals |
| **`StudentLedgerService`** | `app/Services/StudentLedgerService.php` | حساب الذمة المالية للطالب (إجمالي المطلوب - إجمالي المسدد = المتبقي)، بناء كشف الحساب التراكمي. | `StudentResource`, `ParentPortalController`, `StudentPdfController` |
| **`PaymentService`** | `app/Services/PaymentService.php` | تسجيل الدفعات النقدية، تطبيق الخصومات، إنشاء السند المالي، تحديث حالة المجموعات. | `StudentPaymentResource`, `OnlinePaymentRequestResource` |
| **`QuestionImportService`** | `app/Services/QuestionImportService.php` | معالجة واستيراد الأسئلة من ملفات Excel و Word وتحليل النصوص بواسطة الذكاء الاصطناعي إلى بنك الأسئلة. | `QuestionResource`, `ExamResource` |
| **`StudentImportService`** | `app/Services/StudentImportService.php` | استيراد قوائم الطلاب الجماعية من Excel/CSV، فحص تكرار الهواتف، وتعيين المجموعات تلقائياً. | `StudentImports` (Filament Page), `ProcessStudentImportJob` |
| **`ExamService`** | `app/Services/ExamService.php` | رصد نتائج الامتحانات الورقية، حساب ترتيب الأوائل، وتوزيع التقديرات. | `ExamResource`, `ExamResult` |
| **`ExpenseService`** | `app/Services/ExpenseService.php` | تقييد المصروفات وتصنيفها وربطها ببند المصروف المناسب. | `ExpenseResource`, `ExpenseCategoryResource` |
| **`SalaryService`** | `app/Services/SalaryService.php` | حساب مستحقات المساعدين والموظفين وساعات العمل والخصومات والمكافآت. | `SalaryResource` |
| **`ReportService`** | `app/Services/ReportService.php` | توليد التقارير الإدارية والمالية الشاملة (الأرباح، التدفقات النقدية، نسب الحضور والغياب). | `Reports` (Filament Page), `AdvancedAnalytics` |
| **`SettingService`** | `app/Services/SettingService.php` | قراءة وكتابة إعدادات السنتر (اللوجو، أرقام الكاش، النصوص، مفاتيح الـ API) مع التخزين المؤقت (Cache). | `ManageSettings`, `ParentPortalController`, All Views |
| **`SubscriptionService`** | `app/Services/SubscriptionService.php` | إدارة اشتراكات المدرسين في المنصة، فحص صلاحية التجربة المجانية، وتجديد الباقات. | `CheckSubscription`, `SubscriptionPaymentController`, `SuperAdmin Panel` |
| **`TenantRegistrationService`**| `app/Services/TenantRegistrationService.php` | تسجيل معلم جديد، إنشاء الـ Tenant، تعيين المدير، وتجهيز الإعدادات الافتراضية. | `PlatformController@register` |
| **`TenantExportService`** | `app/Services/TenantExportService.php` | تصدير كامل بيانات السنتر (طلاب، درجات، ماليات) في ملفات مضغوطة. | `TenantResource` (SuperAdmin) |
| **`BackupService`** | `app/Services/BackupService.php` | أخذ نسخ احتياطية لقاعدة البيانات وتحميلها واستعادتها. | `Backups` (Filament Page) |

---

## 3. دليل الموديولات الشامل ومصفوفة الترابط

---

### موديول 1: الطلاب وإدارة التسجيل والاستيراد
* **الوظيفة:** إدارة ملف الطالب، كود الطالب الفريد، كود الـ QR، مرحلته الدراسية، أرقام هواتفه، واستيراد الطلاب الجماعي.
* **سلسلة تدفق البيانات والملفات:**
  1. **قاعدة البيانات:** جدول `students`, جدول `student_imports`
  2. **النماذج (Models):** [`Student.php`](file:///d:/project/mohammed_elgandy/app/Models/Student.php), [`StudentImport.php`](file:///d:/project/mohammed_elgandy/app/Models/StudentImport.php)
  3. **الخدمات (Services):** [`StudentImportService.php`](file:///d:/project/mohammed_elgandy/app/Services/StudentImportService.php), [`StudentLedgerService.php`](file:///d:/project/mohammed_elgandy/app/Services/StudentLedgerService.php)
  4. **طابور المعالجة (Job):** [`ProcessStudentImportJob.php`](file:///d:/project/mohammed_elgandy/app/Jobs/ProcessStudentImportJob.php) (لمعالجة ملفات Excel الكبيرة في الخلفية).
  5. **لوحة التحكم (Filament):**
     - [`StudentResource.php`](file:///d:/project/mohammed_elgandy/app/Filament/Resources/StudentResource.php)
     - صفحة الاستيراد: [`StudentImports.php`](file:///d:/project/mohammed_elgandy/app/Filament/Pages/StudentImports.php)
  6. **الطباعة و PDF:** [`StudentPdfController.php`](file:///d:/project/mohammed_elgandy/app/Http/Controllers/StudentPdfController.php) (لطباعة كارت الطالب وكشف الحساب).

---

### موديول 2: المجموعات الدراسية والمواعيد والحصص
* **الوظيفة:** تنظيم مواعيد السنتر، ربط الطلاب بالمجموعات، وحساب أسعار الاشتراكات الشهرية أو الحصة.
* **سلسلة الملفات:**
  1. **قاعدة البيانات:** `groups`, `group_schedules`, `group_sessions`, `group_student_pivot`
  2. **النماذج:** [`Group.php`](file:///d:/project/mohammed_elgandy/app/Models/Group.php), [`GroupSchedule.php`](file:///d:/project/mohammed_elgandy/app/Models/GroupSchedule.php), [`GroupSession.php`](file:///d:/project/mohammed_elgandy/app/Models/GroupSession.php)
  3. **لوحة التحكم (Filament):**
     - [`GroupResource.php`](file:///d:/project/mohammed_elgandy/app/Filament/Resources/GroupResource.php)
     - [`GroupSessionResource.php`](file:///d:/project/mohammed_elgandy/app/Filament/Resources/GroupSessionResource.php)
     - صفحة الجدول الأسبوعي: [`WeeklyTimetable.php`](file:///d:/project/mohammed_elgandy/app/Filament/Pages/WeeklyTimetable.php)

---

### موديول 3: الحضور والغياب والباركود الذكي
* **الوظيفة:** تسجيل حضور وغياب الطلاب في كل حصة سواء يدوياً أو بمسح الـ QR Code بكاميرا الهاتف مع إرسال رسائل واتساب فورية لولي الأمر.
* **سلسلة الملفات:**
  1. **قاعدة البيانات:** `attendances`
  2. **النماذج:** [`Attendance.php`](file:///d:/project/mohammed_elgandy/app/Models/Attendance.php)
  3. **الخدمات:** [`AttendanceService.php`](file:///d:/project/mohammed_elgandy/app/Services/AttendanceService.php), [`WhatsAppNotificationService.php`](file:///d:/project/mohammed_elgandy/app/Services/WhatsAppNotificationService.php)
  4. **لوحة التحكم (Filament):**
     - صفحة مسح الـ QR: [`QrScanner.php`](file:///d:/project/mohammed_elgandy/app/Filament/Pages/QrScanner.php)
     - شاشة تسجيل حضور الحصة داخل: [`GroupSessionResource.php`](file:///d:/project/mohammed_elgandy/app/Filament/Resources/GroupSessionResource.php)

---

### موديول 4: بنك الأسئلة والاستيراد بالذكاء الاصطناعي
* **الوظيفة:** مستودع مركزي للأسئلة مصنفة حسب المرحلة، المادة، الموضوع، ومستوى الصعوبة مع خيارات الحل والتفسير العلمي.
* **سلسلة الملفات:**
  1. **قاعدة البيانات:** `questions`
  2. **النماذج:** [`Question.php`](file:///d:/project/mohammed_elgandy/app/Models/Question.php)
  3. **الخدمات:** [`QuestionImportService.php`](file:///d:/project/mohammed_elgandy/app/Services/QuestionImportService.php)
  4. **لوحة التحكم:** [`QuestionResource.php`](file:///d:/project/mohammed_elgandy/app/Filament/Resources/QuestionResource.php)

---

### موديول 5: الامتحانات الورقية والإلكترونية والكويزات
* **الوظيفة:** إنشاء امتحانات أونلاين بمؤقت زمني وتصحيح فوري أو رصد امتحانات ورقية عادية، مع تحليل نقاط الضعف للطالب.
* **سلسلة الملفات:**
  1. **قاعدة البيانات:** `exams`, `exam_questions`, `online_exam_attempts`, `exam_results`
  2. **النماذج:** [`Exam.php`](file:///d:/project/mohammed_elgandy/app/Models/Exam.php), [`OnlineExamAttempt.php`](file:///d:/project/mohammed_elgandy/app/Models/OnlineExamAttempt.php), [`ExamResult.php`](file:///d:/project/mohammed_elgandy/app/Models/ExamResult.php)
  3. **الخدمات:** [`OnlineExamService.php`](file:///d:/project/mohammed_elgandy/app/Services/OnlineExamService.php), [`ExamService.php`](file:///d:/project/mohammed_elgandy/app/Services/ExamService.php)
  4. **لوحة التحكم:** [`ExamResource.php`](file:///d:/project/mohammed_elgandy/app/Filament/Resources/ExamResource.php) + `QuestionsRelationManager.php`
  5. **الكنترولر:** [`OnlineExamController.php`](file:///d:/project/mohammed_elgandy/app/Http/Controllers/OnlineExamController.php)
  6. **واجهات Blade:**
     - تعليمات الامتحان: `resources/views/parent-portal/exams/show.blade.php`
     - شاشة الحل التفاعلية والمؤقت: `resources/views/parent-portal/exams/take.blade.php`
     - بطاقة النتيجة والتحليل: `resources/views/parent-portal/exams/result.blade.php`

---

### موديول 6: الواجبات المنزلية والتسليم الأونلاين
* **الوظيفة:** نشر واجبات لطلاب المرحلة أو مجموعة معينة، دعم حل الأسئلة أو رفع ملفات PDF وصور الإجابة، التصحيح التلقائي ورصد الملاحظات.
* **سلسلة الملفات:**
  1. **قاعدة البيانات:** `homeworks`, `homework_questions`, `homework_submissions`
  2. **النماذج:** [`Homework.php`](file:///d:/project/mohammed_elgandy/app/Models/Homework.php), [`HomeworkSubmission.php`](file:///d:/project/mohammed_elgandy/app/Models/HomeworkSubmission.php)
  3. **الخدمات:** [`HomeworkService.php`](file:///d:/project/mohammed_elgandy/app/Services/HomeworkService.php)
  4. **لوحة المعلم:** [`HomeworkResource.php`](file:///d:/project/mohammed_elgandy/app/Filament/Resources/HomeworkResource.php) + `QuestionsRelationManager.php` + `SubmissionsRelationManager.php`
  5. **الكنترولر والبوابات:**
     - [`StudentPortalController.php`](file:///d:/project/mohammed_elgandy/app/Http/Controllers/StudentPortalController.php)
     - واجهة حل الواجب: `resources/views/student-portal/homework-show.blade.php`
     - عرض الواجبات لولي الأمر: `resources/views/parent-portal/dashboard.blade.php`

---

### موديول 7: الحسابات والماليات وكشف الحساب
* **الوظيفة:** تحصيل رسوم الحصص والاشتراكات، الخصومات والمنح، المصروفات العامة، مرتبات المساعدين، والدفع الإلكتروني (InstaPay / Vodafone Cash).
* **سلسلة الملفات:**
  1. **قاعدة البيانات:** `student_payments`, `expenses`, `expense_categories`, `salaries`, `discounts`, `online_payment_requests`
  2. **النماذج:** [`StudentPayment.php`](file:///d:/project/mohammed_elgandy/app/Models/StudentPayment.php), [`Expense.php`](file:///d:/project/mohammed_elgandy/app/Models/Expense.php), [`Salary.php`](file:///d:/project/mohammed_elgandy/app/Models/Salary.php), [`OnlinePaymentRequest.php`](file:///d:/project/mohammed_elgandy/app/Models/OnlinePaymentRequest.php)
  3. **الخدمات:** [`StudentLedgerService.php`](file:///d:/project/mohammed_elgandy/app/Services/StudentLedgerService.php), [`PaymentService.php`](file:///d:/project/mohammed_elgandy/app/Services/PaymentService.php), [`ExpenseService.php`](file:///d:/project/mohammed_elgandy/app/Services/ExpenseService.php), [`SalaryService.php`](file:///d:/project/mohammed_elgandy/app/Services/SalaryService.php)
  4. **لوحة التحكم:**
     - [`StudentPaymentResource.php`](file:///d:/project/mohammed_elgandy/app/Filament/Resources/StudentPaymentResource.php)
     - [`OnlinePaymentRequestResource.php`](file:///d:/project/mohammed_elgandy/app/Filament/Resources/OnlinePaymentRequestResource.php) (لاعتماد إيصالات سداد أولياء الأمور).
     - [`ExpenseResource.php`](file:///d:/project/mohammed_elgandy/app/Filament/Resources/ExpenseResource.php)
     - [`SalaryResource.php`](file:///d:/project/mohammed_elgandy/app/Filament/Resources/SalaryResource.php)

---

### موديول 8: الملازم والمطبوعات والمخازن
* **الوظيفة:** إدارة مخزون الملازم والكتب وتسليمها للطلاب ومتابعة سداد ثمنها أو ترحيله للحساب الآجل.
* **سلسلة الملفات:**
  1. **قاعدة البيانات:** `study_materials`, `student_material_deliveries`, `inventory_items`, `inventory_movements`
  2. **النماذج:** [`StudyMaterial.php`](file:///d:/project/mohammed_elgandy/app/Models/StudyMaterial.php), [`StudentMaterialDelivery.php`](file:///d:/project/mohammed_elgandy/app/Models/StudentMaterialDelivery.php)
  3. **لوحة التحكم:** [`StudyMaterialResource.php`](file:///d:/project/mohammed_elgandy/app/Filament/Resources/StudyMaterialResource.php), [`StudentMaterialDeliveryResource.php`](file:///d:/project/mohammed_elgandy/app/Filament/Resources/StudentMaterialDeliveryResource.php)

---

### موديول 9: بوابات المتابعة والمواقع التعريفية
* **الوظيفة:** الواجهات العامة التي يتفاعل معها الطالب، ولي الأمر، والزوار.
* **سلسلة الملفات:**
  1. **موقع المعلم وحجز الطلاب الجدد (`/t/{tenant}`):**
     - الكنترولر: [`HomeController.php`](file:///d:/project/mohammed_elgandy/app/Http/Controllers/HomeController.php)
     - الواجهة: `resources/views/tenant-landing/index.blade.php`
     - طلبات الالتحاق: [`StudentApplicationResource.php`](file:///d:/project/mohammed_elgandy/app/Filament/Resources/StudentApplicationResource.php)
  2. **بوابة الطالب (`/t/{tenant}/student/*`):**
     - الكنترولر: [`StudentPortalController.php`](file:///d:/project/mohammed_elgandy/app/Http/Controllers/StudentPortalController.php)
     - الواجهات: `resources/views/student-portal/login.blade.php`, `resources/views/student-portal/dashboard.blade.php`
  3. **بوابة ولي الأمر (`/t/{tenant}/parent/*`):**
     - الكنترولر: [`ParentPortalController.php`](file:///d:/project/mohammed_elgandy/app/Http/Controllers/ParentPortalController.php)
     - الواجهات: `resources/views/parent-portal/login.blade.php`, `resources/views/parent-portal/dashboard.blade.php`

---

### موديول 10: إدارة اشتراك المعلم والسوبر أدمن
* **الوظيفة:** إدارة باقات المنصة (Plans)، اشتراكات السناتر (Subscriptions)، وفترة الـ Free Trial، وإغلاق اللوحة تلقائياً عند انتهاء الاشتراك.
* **سلسلة الملفات:**
  1. **قاعدة البيانات:** `tenants`, `plans`, `subscriptions`, `subscription_payments`
  2. **النماذج:** [`Tenant.php`](file:///d:/project/mohammed_elgandy/app/Models/Tenant.php), [`Plan.php`](file:///d:/project/mohammed_elgandy/app/Models/Plan.php), [`Subscription.php`](file:///d:/project/mohammed_elgandy/app/Models/Subscription.php)
  3. **الوسيط (Middleware):** [`CheckSubscription.php`](file:///d:/project/mohammed_elgandy/app/Http/Middleware/CheckSubscription.php)
  4. **الخدمات:** [`SubscriptionService.php`](file:///d:/project/mohammed_elgandy/app/Services/SubscriptionService.php)
  5. **لوحة السوبر أدمن (`/super-admin`):**
     - مزود اللوحة: [`SuperAdminPanelProvider.php`](file:///d:/project/mohammed_elgandy/app/Providers/Filament/SuperAdminPanelProvider.php)
     - الموارد: `TenantResource.php`, `PlanResource.php`, `SubscriptionResource.php`, `SubscriptionPaymentResource.php`
  6. **سداد اشتراك المعلم:**
     - الكنترولر: [`SubscriptionPaymentController.php`](file:///d:/project/mohammed_elgandy/app/Http/Controllers/SubscriptionPaymentController.php)
     - الواجهات: `resources/views/subscription/status.blade.php`, `resources/views/subscription/pay.blade.php`

---

### موديول 11: الإعدادات والواتساب والنسخ الاحتياطي
* **الوظيفة:** التحكم في اسم وهوية السنتر، اللوجو، إعدادات بوابات الدفع، مزود رسائل الواتساب، وأخذ نسخ احتياطية لقاعدة البيانات.
* **سلسلة الملفات:**
  1. **النماذج:** [`TenantSetting.php`](file:///d:/project/mohammed_elgandy/app/Models/TenantSetting.php)
  2. **الخدمات:** [`SettingService.php`](file:///d:/project/mohammed_elgandy/app/Services/SettingService.php), [`BackupService.php`](file:///d:/project/mohammed_elgandy/app/Services/BackupService.php)
  3. **صفحات Filament:**
     - إعدادات السنتر: [`ManageSettings.php`](file:///d:/project/mohammed_elgandy/app/Filament/Pages/ManageSettings.php)
     - النسخ الاحتياطي: [`Backups.php`](file:///d:/project/mohammed_elgandy/app/Filament/Pages/Backups.php)

---

## 4. شجرة المسارات المعتمدة

كل المسارات مسجلة داخل ملف [`routes/web.php`](file:///d:/project/mohammed_elgandy/routes/web.php) ومقسمة كالتالي:

```php
// 1. مسارات المنصة العامة والتسويق
Route::get('/', [PlatformController::class, 'index'])->name('platform.home');
Route::get('/pricing', [PlatformController::class, 'pricing'])->name('platform.pricing');
Route::get('/register', [PlatformController::class, 'showRegister'])->name('platform.register');

// 2. مسارات المدرس المعزولة عبر Middleware: tenant.resolve
Route::prefix('t/{tenant}')->middleware(['tenant.resolve'])->group(function () {
    // أ. بوابة الطالب
    Route::prefix('student')->name('tenant.student.')->group(function () {
        Route::get('/login', [StudentPortalController::class, 'showLogin'])->name('login');
        Route::post('/login', [StudentPortalController::class, 'login'])->name('login.submit');
        Route::get('/dashboard', [StudentPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/logout', [StudentPortalController::class, 'logout'])->name('logout');
        Route::get('/exams/{id}', [OnlineExamController::class, 'show'])->name('exams.show');
        Route::get('/exams/{id}/start', [OnlineExamController::class, 'start'])->name('exams.start');
        Route::post('/exams/{id}/submit', [OnlineExamController::class, 'submit'])->name('exams.submit');
        Route::get('/exams/{id}/result', [OnlineExamController::class, 'result'])->name('exams.result');
        Route::get('/homeworks/{id}', [StudentPortalController::class, 'showHomework'])->name('homeworks.show');
        Route::post('/homeworks/{id}/submit', [StudentPortalController::class, 'submitHomework'])->name('homeworks.submit');
    });

    // ب. بوابة ولي الأمر
    Route::prefix('parent')->name('tenant.parent.')->group(function () {
        Route::get('/login', [ParentPortalController::class, 'showLogin'])->name('login');
        Route::post('/login', [ParentPortalController::class, 'login'])->name('login.submit');
        Route::get('/dashboard', [ParentPortalController::class, 'dashboard'])->name('dashboard');
        Route::post('/payment', [ParentPortalController::class, 'submitPayment'])->name('payment.submit');
        Route::get('/logout', [ParentPortalController::class, 'logout'])->name('logout');
        Route::get('/exams/{id}', [OnlineExamController::class, 'show'])->name('exams.show');
        Route::get('/exams/{id}/result', [OnlineExamController::class, 'result'])->name('exams.result');
    });

    // ج. سداد اشتراك المعلم
    Route::prefix('subscription')->name('tenant.subscription.')->group(function () {
        Route::get('/status', [SubscriptionPaymentController::class, 'status'])->name('status');
        Route::get('/pay', [SubscriptionPaymentController::class, 'showPay'])->name('pay');
        Route::post('/pay', [SubscriptionPaymentController::class, 'submitPay'])->name('pay.submit');
    });

    // د. موقع المدرس التعريفي
    Route::get('/', [HomeController::class, 'index'])->name('tenant.home');
    Route::post('/enroll', [HomeController::class, 'submitEnrollment'])->name('tenant.enroll.submit');
});
```

---

## 5. لوحات تحكم Filament

النظام يحتوي على **لوحتي تحكم (Panels)** مستقلتين:

### 1. لوحة المعلم والسنتر (Admin Panel):
* **الملف المسؤول:** [`app/Providers/Filament/AdminPanelProvider.php`](file:///d:/project/mohammed_elgandy/app/Providers/Filament/AdminPanelProvider.php)
* **المسار في المتصفح:** `/admin/{tenant}`
* **الميزة:** تستخدم `.tenant(Tenant::class, slugAttribute: 'slug')` لعزل كل مدرس داخل مساحته الخاصة.
* **الموارد (Resources):** موجودة في مجلد `app/Filament/Resources/`.

### 2. لوحة إدارة المنصة السحابية (SuperAdmin Panel):
* **الملف المسؤول:** [`app/Providers/Filament/SuperAdminPanelProvider.php`](file:///d:/project/mohammed_elgandy/app/Providers/Filament/SuperAdminPanelProvider.php)
* **المسار في المتصفح:** `/super-admin`
* **الميزة:** غير مقيدة بـ Tenant؛ مخصصة لمالك الـ SaaS لمراقبة جميع المعلمين والباقات والمدفوعات.
* **الموارد (Resources):** موجودة في مجلد `app/Filament/SuperAdmin/Resources/`.

---

## 6. دليل كشف وإصلاح الأخطاء (Master Troubleshooting Flow)

عند مواجهة أي مشكلة برمجية، اتبع هذا المخطط السريع:

```
                                  [نوع المشكلة أو الخطأ]
                                             │
      ┌──────────────────┬───────────────────┼───────────────────┬──────────────────┐
      ▼                  ▼                   ▼                   ▼                  ▼
[Route Error]      [Tenant Error]      [Data Visibility]    [Logic Error]     [UI / Blade Cache]
      │                  │                   │                   │                  │
      ▼                  ▼                   ▼                   ▼                  ▼
راجع:              راجع:               تأكد من:            توجه مباشرة        شغّل فوراً:
routes/web.php     app/Models/Tenant   1. وجود trait       إلى الـ Service    php artisan
وملفات الـ Blade   وخاصية:             BelongsToTenant     المعنية في         optimize:clear
للتأكد من تمرير    $tenantRelationship 2. قيمة status      app/Services/      
معامل الـ tenant   داخل الـ Resource   في الجدول           لا الكنترولر       
```

### 🔴 السيناريوهات الشائعة وحلولها المباشرة:

1. **خطأ `Route [xxx] not defined`:**
   - **السبب:** وجود رابط قديم يستدعي مساراً غير معزول.
   - **الحل:** استبدله بالمسار المعزول مع تمرير `tenant` مثل:
     `route('tenant.student.exams.show', ['tenant' => $currentTenant->slug, 'id' => $exam->id])`

2. **خطأ `The model [Tenant] does not have a relationship named [xxx]` في Filament:**
   - **السبب:** Filament يحاول ربط الموديل بجدول `tenants` لكن اسم العلاقة مختلف.
   - **الحل:**
     1. أضف العلاقة في [`app/Models/Tenant.php`](file:///d:/project/mohammed_elgandy/app/Models/Tenant.php) (مثال: `public function homeworks()`).
     2. حدد `$tenantRelationshipName = 'homeworks';` في ملف الـ Resource.

3. **سجل تم إنشاؤه في لوحة التحكم لكنه لا يظهر في بوابة الطالب أو ولي الأمر:**
   - **السبب:** التحقق من شروط الفلترة:
     - **الحالة (Status):** هل العنصر `published` أم `draft`؟
     - **المرحلة الدراسية (`stage_id`):** هل تطابق مرحلة الطالب؟
     - **المجموعة (`group_id`):** هل الطالب منضم للمجموعة المحددة؟
     - **الـ Tenant ID:** هل السجل ينتمي لنفس المدرس؟

4. **تحديث الكود لا ينعكس في المتصفح:**
   - نفذ دائماً:
     ```bash
     php artisan optimize:clear
     ```

</div>
