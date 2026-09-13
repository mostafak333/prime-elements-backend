<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::firstOrCreate(
            [],
            [
                'delivery_fee' => 50,
                'vat_percentage' => 14,
                'vat_enabled' => true,
                'privacy_policy' => 'This privacy policy explains how Prime Elements collects, uses, and protects your personal information.',
                'terms_conditions' => '<div>
    <p><span dir="rtl">الشروط والأحكام</span></p>
    <p><span dir="rtl"><strong><span>آخر تحديث</span></strong><strong>: 25&nbsp;</strong><strong><span>أغسطس </span></strong><strong>2026</strong></span></p>
    <p><span dir="rtl"><span>مرحبًا بكم في متجر </span><strong><span>برايم إليمنتس</span></strong><span> الإلكتروني</span></span><span dir="ltr">.</span></p>
    <p><span dir="rtl">تمثل هذه الشروط والأحكام الإطار المنظم لاستخدام متجر برايم إليمنتس وإتمام عمليات الشراء والاستفادة من المنتجات والخدمات المتاحة من خلاله</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">يُقصد بعبارات&nbsp;</span>&laquo;<span dir="rtl">برايم إليمنتس</span><strong>&raquo;&nbsp;</strong><strong><span>أو </span></strong><strong>&laquo;</strong><strong><span>الشركة</span></strong><strong>&raquo;&nbsp;</strong><strong><span>أو </span></strong><strong>&laquo;</strong><strong><span>نحن</span></strong>&raquo; <span dir="rtl">شركة برايم إليمنتس، ويُقصد بعبارة&nbsp;</span>&laquo;<span dir="rtl">العميل</span><strong>&raquo;&nbsp;</strong><strong><span>أو </span></strong><strong>&laquo;</strong><strong><span>المستخدم</span></strong>&raquo; <span dir="rtl">كل شخص يقوم بزيارة المتجر أو استخدامه أو إنشاء حساب أو شراء أي منتج أو خدمة من خلاله</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">باستخدام المتجر أو إتمام أي عملية شراء، يقر العميل بأنه اطلع على هذه الشروط والأحكام ووافق عليها، وذلك بالقدر الذي لا يتعارض مع أي حقوق أو أحكام إلزامية مقررة بموجب الأنظمة المعمول بها في المملكة العربية السعودية</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>1.&nbsp;</strong><span dir="rtl">نطاق المتجر والخدمات</span></p>
    <p><span dir="rtl">يختص متجر برايم إليمنتس بتوفير المنتجات والخدمات التعليمية، والتي قد تشمل</span><span dir="ltr">:</span></p>
    <ul type="disc">
        <li><span dir="rtl">الكتب والمواد التعليمية المطبوعة</span><span>.</span></li>
        <li><span dir="rtl">الكتب والمواد التعليمية الرقمية</span><span>.</span></li>
        <li><span dir="rtl">أكواد الوصول إلى المحتوى والمنصات التعليمية</span><span>.</span></li>
        <li><span dir="rtl">الاشتراكات والخدمات الإلكترونية التعليمية</span><span>.</span></li>
        <li><span dir="rtl">المنتجات التعليمية الأخرى التي يتم عرضها في المتجر</span><span>.</span></li>
    </ul>
    <p><span dir="rtl"><span>وقد تخضع بعض المنتجات أو الخدمات لشروط إضافية تتعلق بطريقة الاستخدام أو مدة الترخيص أو عدد المستخدمين</span>. <span>ويتم توضيح هذه الشروط في صفحة المنتج أو أثناء عملية الشراء متى كان ذلك مناسبًا</span></span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>2.&nbsp;</strong><span dir="rtl">قبول الشروط</span></p>
    <p><span dir="rtl">يُعد استخدام المتجر أو إتمام عملية شراء من خلاله موافقة من العميل على هذه الشروط والأحكام</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">إذا كان العميل لا يوافق على أي من هذه الشروط، فعليه الامتناع عن استخدام الخدمات أو إجراء عمليات شراء من المتجر</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">ويجوز للشركة تحديث هذه الشروط من وقت لآخر وفقًا لما هو موضح في قسم التعديلات</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>3.&nbsp;</strong><span dir="rtl">أهلية المستخدم</span></p>
    <p><span dir="rtl">يقر العميل بأن لديه الأهلية النظامية لإجراء عملية الشراء واستخدام الخدمات</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">وفي حال كان المستخدم قاصرًا، فيجب أن يتم استخدام المتجر وإتمام عمليات الشراء بموافقة وإشراف ولي الأمر أو المسؤول النظامي عنه</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>4.&nbsp;</strong><span dir="rtl">إنشاء الحساب</span></p>
    <p><span dir="rtl">قد يتطلب الحصول على بعض المنتجات أو الخدمات إنشاء حساب لدى المتجر</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">يلتزم العميل عند التسجيل بتقديم معلومات صحيحة وكاملة ومحدثة، والمحافظة على تحديث بياناته عند الحاجة</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">يتحمل العميل مسؤولية المحافظة على سرية بيانات الدخول الخاصة به وعدم مشاركتها مع أي شخص غير مصرح له</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">وفي حال اكتشاف أو الاشتباه في استخدام غير مصرح به للحساب، يجب إبلاغ الشركة فورًا</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">ويجوز للشركة تعليق أو إغلاق أي حساب يستخدم بطريقة مخالفة لهذه الشروط أو للأنظمة، مع مراعاة الحقوق النظامية للعميل</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>5.&nbsp;</strong><span dir="rtl">المنتجات والمواصفات</span></p>
    <p><span dir="rtl">تحرص الشركة على عرض معلومات دقيقة وواضحة عن المنتجات والخدمات، بما في ذلك الوصف والمواصفات والصور والأسعار وأي معلومات أخرى ذات صلة</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">قد تختلف بعض الألوان أو التفاصيل المرئية للمنتجات المطبوعة عن الصور المعروضة على الشاشة بسبب اختلاف الأجهزة وإعدادات العرض</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">يجوز للشركة تحديث مواصفات المنتجات أو محتواها أو تعديل طريقة تقديم الخدمات أو إيقاف عرض بعض المنتجات مستقبلًا، على ألا يؤثر ذلك على الطلبات التي تم قبولها وتأكيدها، إلا في الحالات التي يسمح بها النظام</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>6.&nbsp;</strong><span dir="rtl">الأسعار</span></p>
    <p><span dir="rtl">تُعرض جميع الأسعار بالريال السعودي</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">ويتم توضيح السعر المستحق للعميل وأي رسوم أو ضرائب أو تكاليف إضافية واجبة التطبيق قبل إتمام عملية الشراء، وفقًا للأنظمة المعمول بها</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">يجوز للشركة تعديل أسعار المنتجات والخدمات في أي وقت، ولا يؤثر تعديل السعر على الطلبات التي تم تأكيدها قبل التعديل، ما لم يقتضِ النظام خلاف ذلك</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">وفي حال ظهور سعر غير صحيح بسبب خطأ تقني أو إدخال غير مقصود، يحق للشركة مراجعة الطلب والتواصل مع العميل لمعالجة الحالة وفقًا للأنظمة المعمول بها</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>7.&nbsp;</strong><span dir="rtl">الطلبات وتأكيد الشراء</span></p>
    <p><span dir="rtl">يمكن للعميل اختيار المنتجات وإضافتها إلى سلة الشراء وإتمام الطلب من خلال الخطوات الإلكترونية المتاحة في المتجر</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">بعد إرسال الطلب، قد يتم إرسال إشعار أو رسالة تؤكد استلام الطلب</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">ويخضع قبول الطلب لتوفر المنتج وصحة بيانات الطلب ونجاح عملية الدفع وأي إجراءات تحقق لازمة</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">في الحالات الاستثنائية، قد يتم رفض أو إلغاء الطلب، بما في ذلك حالات عدم توفر المنتج، أو وجود خطأ جوهري في السعر أو الوصف، أو تعذر إتمام الدفع، أو وجود مؤشرات على استخدام غير مشروع للمتجر</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">إذا تم إلغاء طلب سبق للعميل دفع قيمته، يتم رد المبلغ المستحق وفقًا لطريقة الدفع والإجراءات المعتمدة، وبما يتوافق مع الأنظمة والسياسات ذات الصلة</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>8.&nbsp;</strong><span dir="rtl">وسائل الدفع</span></p>
    <p><span dir="rtl">يوفر المتجر وسائل الدفع المتاحة في صفحة إتمام الطلب</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">قد تتم معالجة المدفوعات من خلال مزودي خدمات دفع إلكتروني مستقلين، وتخضع عمليات الدفع للمتطلبات الأمنية وشروط مزود الخدمة</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">لا تطلب الشركة من العملاء إرسال بيانات البطاقات البنكية أو الائتمانية عبر البريد الإلكتروني أو وسائل التواصل غير المخصصة للدفع</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>9.&nbsp;</strong><span dir="rtl">الكتب والمنتجات الورقية</span></p>
    <p><span dir="rtl">يتم تجهيز وشحن الكتب والمنتجات الورقية إلى العنوان الذي يحدده العميل أثناء عملية الشراء</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">يتحمل العميل مسؤولية التأكد من صحة بيانات عنوان التسليم ورقم التواصل</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">توضح صفحة إتمام الطلب مدة التوصيل المتوقعة ورسوم الشحن، إن وجدت</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">وفي حال حدوث مشكلة في عملية التسليم، تعمل الشركة على متابعة الطلب مع شركة الشحن واتخاذ الإجراءات المناسبة وفقًا للسياسات والأنظمة المعمول بها</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>10.&nbsp;</strong><span dir="rtl">المنتجات الرقمية</span></p>
    <p><span dir="rtl">تشمل المنتجات الرقمية الكتب الإلكترونية والمحتوى التعليمي الرقمي والملفات الإلكترونية والاشتراكات وأي منتجات يتم تسليمها إلكترونيًا</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">يتم توفير المنتج الرقمي بالطريقة الموضحة في صفحة المنتج، والتي قد تشمل إرساله إلى البريد الإلكتروني أو إتاحته من خلال حساب العميل أو توفير رابط أو بيانات وصول إلى منصة إلكترونية</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">يُمنح العميل حق استخدام المنتج الرقمي وفقًا لطبيعته وشروط الترخيص المرتبطة به</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">ولا يجوز نسخ أو إعادة بيع أو نشر أو توزيع أو مشاركة المحتوى الرقمي أو توفيره لأشخاص آخرين دون الحصول على تصريح من صاحب الحقوق</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>11.&nbsp;</strong><span dir="rtl">أكواد الوصول</span></p>
    <p><span dir="rtl">عند شراء كود وصول إلى منتج أو منصة أو محتوى تعليمي، يتم إرسال الكود أو بيانات التفعيل بالطريقة المحددة في المتجر</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">يكون الكود مخصصًا للاستخدام وفقًا للغرض الموضح في وصف المنتج</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">ولا يجوز للعميل نسخ الكود أو مشاركته أو إعادة بيعه أو نقله إلى شخص آخر إذا كانت شروط المنتج تقصر استخدامه على مستخدم واحد أو حساب محدد</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">تكون لكل أكواد الوصول مدة صلاحية محددة إذا تم النص عليها في صفحة المنتج أو أثناء عملية الشراء</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">ويتحمل العميل مسؤولية المحافظة على الكود بعد استلامه</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">في حال فقدان الكود أو وجود مشكلة في تفعيله، يجب التواصل مع خدمة العملاء وتقديم بيانات الطلب للتحقق منه</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">بعد تفعيل الكود أو بدء استخدام المحتوى، قد تخضع إمكانية الإلغاء أو الاسترداد لشروط المنتج وطبيعته وحالة الاستخدام، مع عدم الإخلال بأي حقوق نظامية مقررة للعميل</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>12.&nbsp;</strong><span dir="rtl">أكواد الوصول الخاصة بالناشرين والمنصات التعليمية</span></p>
    <p><span dir="rtl">قد تتضمن بعض المنتجات المعروضة في متجر برايم إليمنتس أكواد وصول أو تراخيص استخدام لمحتوى أو منصات إلكترونية مملوكة أو مُدارة من قبل ناشرين أو مزودي خدمات تعليمية من أطراف أخرى</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">قد يخضع استخدام هذه الأكواد أو المحتوى المرتبط بها، بالإضافة إلى هذه الشروط والأحكام، لشروط الاستخدام وسياسة الخصوصية وأحكام الترخيص الخاصة بالناشر أو مزود المنصة المعنية</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">تقتصر مسؤولية شركة برايم إليمنتس على توفير المنتج أو كود الوصول وفقًا لما تم الاتفاق عليه في عملية الشراء</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">ولا تكون الشركة مسؤولة عن التغييرات التي يجريها الناشر أو مزود المنصة على محتوى المنصة أو خصائصها أو سياسات تشغيلها أو الخدمات التي تقع تحت إدارته المباشرة، وذلك في حدود ما يسمح به النظام</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">في حال وجود مشكلة في تفعيل الكود أو عدم عمله بالشكل الموضح في بيانات المنتج، يجب على العميل التواصل مع برايم إليمنتس عبر قنوات خدمة العملاء الرسمية، وستعمل الشركة على التحقق من المشكلة والتنسيق مع الناشر أو مزود الخدمة عند الحاجة</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">ولا يجوز للعميل تقديم نفسه باعتباره ممثلًا عن شركة برايم إليمنتس أو تقديم مطالبات أو تعهدات نيابة عنها أمام الناشر أو مزود المنصة دون تفويض مسبق</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">وتظل جميع الحقوق النظامية المقررة للعميل محفوظة، ولا تؤثر شروط الناشر أو مزود المنصة على أي حق إلزامي مقرر له بموجب الأنظمة المعمول بها في المملكة العربية السعودية</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>13.&nbsp;</strong><span dir="rtl">الاشتراكات التعليمية</span></p>
    <p><span dir="rtl">إذا كان المنتج عبارة عن اشتراك في منصة أو خدمة تعليمية، يتم توضيح مدة الاشتراك ونطاق الخدمة وعدد المستخدمين وأي قيود أخرى في صفحة المنتج</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">ينتهي الاشتراك بانتهاء المدة المحددة، ما لم تنص صفحة المنتج أو الاتفاقية الخاصة بالخدمة على خلاف ذلك</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">ولا يجوز استخدام الاشتراك بطريقة تتجاوز نطاق الترخيص أو عدد المستخدمين المسموح به</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>14.&nbsp;</strong><span dir="rtl">الشحن والتوصيل</span></p>
    <p><span dir="rtl">يتم توضيح خيارات الشحن المتاحة والرسوم ومدة التوصيل المتوقعة قبل إتمام الطلب</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">قد تختلف مدة التسليم بحسب موقع العميل وتوفر المنتج وشركة الشحن والظروف التشغيلية</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">يلتزم العميل بتقديم عنوان صحيح وكامل وبيانات اتصال فعالة</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">وفي حال تعذر التسليم بسبب عدم صحة البيانات أو عدم تجاوب العميل مع شركة الشحن، فقد يلزم إعادة جدولة التسليم وفقًا للإجراءات المعمول بها</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">وفي جميع الأحوال، تظل حقوق العميل المقررة بموجب الأنظمة المعمول بها محفوظة</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>15.&nbsp;</strong><span dir="rtl">الاسترجاع والاستبدال والاسترداد</span></p>
    <p><span dir="rtl"><span>تخضع طلبات الاسترجاع والاستبدال واسترداد المبالغ إلى </span><strong><span>سياسة الاسترجاع والاسترداد</span></strong><span> المنشورة في المتجر</span></span><span dir="ltr">.</span></p>
    <p><span dir="rtl">وتوضح تلك السياسة الحالات والإجراءات والمدة المطلوبة لتقديم طلب الاسترجاع أو الاستبدال</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">وبالنسبة للمنتجات الرقمية وأكواد الوصول، تتم مراعاة طبيعة المنتج وما إذا كان قد تم تفعيله أو استخدامه، بالإضافة إلى الأحكام النظامية ذات العلاقة</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">ولا يجوز تفسير هذه الشروط أو سياسة الاسترجاع بما يؤدي إلى إسقاط أو تقييد أي حق إلزامي مقرر للمستهلك بموجب الأنظمة السعودية</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>16.&nbsp;</strong><span dir="rtl">الملكية الفكرية</span></p>
    <p><span dir="rtl">جميع الحقوق المتعلقة بمحتوى المتجر، بما في ذلك النصوص والصور والتصاميم والشعارات والعلامات التجارية والمواد التعليمية والكتب والمحتوى الرقمي والبرمجيات، محفوظة للشركة أو للناشرين وأصحاب الحقوق المرخص لهم</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">وبالنسبة للمنتجات التي تتضمن محتوى مملوكًا لناشرين أو جهات تعليمية أخرى، تبقى حقوق الملكية الفكرية لذلك المحتوى محفوظة لأصحابها، ويقتصر استخدام العميل له على الترخيص الممنوح له وفق شروط المنتج والجهة المالكة</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">لا يمنح شراء أي منتج العميل ملكية حقوق النشر أو العلامات التجارية أو المحتوى</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">ويحظر نسخ أو تصوير أو إعادة إنتاج أو توزيع أو نشر أو بيع المحتوى المحمي بحقوق الملكية الفكرية دون تصريح من صاحب الحق</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>17.&nbsp;</strong><span dir="rtl">التقييمات والتعليقات</span></p>
    <p><span dir="rtl">قد يتيح المتجر للعملاء إضافة تقييمات أو تعليقات على المنتجات</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">يلتزم العميل بأن يكون المحتوى الذي يقدمه صحيحًا وألا يتضمن مواد مخالفة للأنظمة أو مسيئة أو تشهيرية أو تنتهك حقوق الآخرين</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">ويحق للشركة إزالة أي محتوى يخالف هذه الشروط أو الأنظمة ذات العلاقة</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>18.&nbsp;</strong><span dir="rtl">الاستخدامات المحظورة</span></p>
    <p><span dir="rtl">يحظر استخدام المتجر أو خدماته</span><span dir="ltr">:</span></p>
    <ul type="disc">
        <li><span dir="rtl">لأغراض غير مشروعة</span><span>.</span></li>
        <li><span dir="rtl">لمحاولة اختراق أو تعطيل أنظمة المتجر</span><span>.</span></li>
        <li><span dir="rtl">لإدخال فيروسات أو برمجيات ضارة</span><span>.</span></li>
        <li><span dir="rtl">للوصول غير المصرح به إلى حسابات أو بيانات الآخرين</span><span>.</span></li>
        <li><span dir="rtl">لنسخ أو توزيع المحتوى التعليمي دون تصريح</span><span>.</span></li>
        <li><span dir="rtl">لإعادة بيع أو مشاركة أكواد الوصول بطريقة غير مصرح بها</span><span>.</span></li>
        <li><span dir="rtl">لتقديم معلومات مضللة أو انتحال هوية شخص آخر</span><span>.</span></li>
        <li><span dir="rtl">بأي طريقة تنتهك حقوق الشركة أو حقوق الغير</span><span>.</span></li>
    </ul>
    <p><span dir="rtl">ويجوز للشركة اتخاذ الإجراءات المناسبة عند وجود مخالفة، بما في ذلك تعليق الحساب أو إيقاف الوصول إلى بعض الخدمات، وفقًا للأنظمة المعمول بها</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>19.&nbsp;</strong><span dir="rtl">الخدمات والروابط الخارجية</span></p>
    <p><span dir="rtl">قد يستخدم المتجر خدمات مقدمة من أطراف أخرى، مثل مزودي الدفع وشركات الشحن وخدمات الخرائط والرسائل والمنصات التعليمية</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">وقد تخضع هذه الخدمات لشروط وسياسات مستقلة يضعها مقدموها</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">وتتعامل الشركة مع هذه الخدمات بالقدر اللازم لتقديم منتجاتها وخدماتها للعملاء، ولا تتحمل مسؤولية الأعطال الناتجة مباشرة عن خدمات تقع خارج نطاق سيطرتها، مع بذل الجهود المعقولة لمعالجة أي مشكلة تؤثر على العميل</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>20.&nbsp;</strong><span dir="rtl">حماية البيانات الشخصية</span></p>
    <p><span dir="rtl">تلتزم شركة برايم إليمنتس بالتعامل مع البيانات الشخصية وفقًا للأنظمة واللوائح المعمول بها في المملكة العربية السعودية</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">وقد تشمل البيانات التي يتم جمعها، بحسب طبيعة الخدمة، اسم العميل وبيانات الاتصال وعنوان التسليم وبيانات الطلب ومعلومات الحساب</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">تستخدم البيانات للأغراض اللازمة لتقديم المنتجات والخدمات ومعالجة الطلبات والدفع والتوصيل وخدمة العملاء والامتثال للمتطلبات النظامية</span><span dir="ltr">.</span></p>
    <p><span dir="rtl"><span>وتوضح </span><strong><span>سياسة الخصوصية</span></strong><span> المنشورة في المتجر بصورة تفصيلية كيفية جمع البيانات الشخصية واستخدامها وحمايتها والاحتفاظ بها، بالإضافة إلى حقوق أصحاب البيانات وطرق التواصل مع الشركة</span></span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>21.&nbsp;</strong><span dir="rtl">أمن المعلومات</span></p>
    <p><span dir="rtl">تعمل الشركة على تطبيق إجراءات تقنية وتنظيمية مناسبة للمساعدة في حماية المتجر والبيانات من الوصول أو الاستخدام غير المصرح به</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">ومع ذلك، لا يمكن ضمان خلو أي نظام إلكتروني من جميع المخاطر التقنية بصورة مطلقة</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">ويلتزم العميل باتخاذ الإجراءات المناسبة لحماية جهازه وحساباته وبيانات الدخول الخاصة به</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>22.&nbsp;</strong><span dir="rtl">توفر الموقع والخدمات</span></p>
    <p><span dir="rtl">تسعى الشركة إلى توفير المتجر والخدمات بصورة مستمرة، إلا أن الموقع أو بعض الخدمات قد تتوقف مؤقتًا بسبب أعمال الصيانة أو التحديثات أو الأعطال الفنية أو الظروف الخارجة عن السيطرة المعقولة للشركة</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">ولا يؤثر ذلك على الحقوق النظامية للعميل المتعلقة بالطلبات التي تم قبولها أو المدفوعات التي تمت</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>23.&nbsp;</strong><span dir="rtl">مسؤولية العميل</span></p>
    <p><span dir="rtl">يتحمل العميل مسؤولية صحة المعلومات والبيانات التي يقدمها للشركة، ومسؤولية استخدام المتجر والمنتجات والخدمات بطريقة نظامية</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">كما يتحمل مسؤولية المحافظة على بيانات حسابه وأكواد الوصول الخاصة به وعدم تمكين الغير من استخدامها بصورة مخالفة لشروط المنتج</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>24.&nbsp;</strong><span dir="rtl">حدود المسؤولية</span></p>
    <p><span dir="rtl">تبذل الشركة جهودًا معقولة لضمان دقة المعلومات واستمرارية الخدمات</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">ولا تكون الشركة مسؤولة عن الانقطاعات أو الأعطال الناتجة عن أسباب خارجة عن سيطرتها المعقولة، مثل أعطال شبكات الاتصالات أو مزودي الخدمات التقنية أو أنظمة الدفع أو شركات الشحن</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">ولا يفسر أي بند في هذه الشروط على أنه إعفاء للشركة من مسؤولية لا يجوز الإعفاء منها بموجب الأنظمة المعمول بها في المملكة العربية السعودية</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>25.&nbsp;</strong><span dir="rtl">تعديل المنتجات والخدمات</span></p>
    <p><span dir="rtl">يجوز للشركة تطوير أو تعديل أو إضافة أو إيقاف بعض المنتجات أو الخدمات المتاحة في المتجر</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">ولا يؤثر أي تعديل مستقبلي على المنتجات أو الخدمات التي تم شراؤها وقبولها، ما لم يكن التعديل متعلقًا بمتطلبات نظامية أو تقنية ضرورية لتقديم الخدمة</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>26.&nbsp;</strong><span dir="rtl">تعديل الشروط والأحكام</span></p>
    <p><span dir="rtl">يجوز للشركة تعديل هذه الشروط والأحكام من وقت لآخر بما يتناسب مع تطوير المتجر أو الخدمات أو التغيرات النظامية</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">يتم نشر النسخة الجديدة في هذه الصفحة مع توضيح تاريخ آخر تحديث</span><span dir="ltr">.</span></p>
    <p><span dir="rtl">وتطبق النسخة المحدثة على الاستخدام والطلبات التي تتم بعد تاريخ سريانها، ما لم يتطلب النظام خلاف ذلك</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>27.&nbsp;</strong><span dir="rtl">قابلية فصل الأحكام</span></p>
    <p><span dir="rtl">إذا تبين أن أي بند من هذه الشروط غير صالح أو غير قابل للتنفيذ بموجب النظام، فلا يؤثر ذلك على صلاحية بقية البنود، وتظل الأحكام الأخرى نافذة بالقدر الذي يسمح به النظام</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>28.&nbsp;</strong><span dir="rtl">القانون والاختصاص</span></p>
    <p><span dir="rtl"><span>تخضع هذه الشروط والأحكام وجميع عمليات الشراء التي تتم من خلال متجر برايم إليمنتس للأنظمة المعمول بها في </span><strong><span>المملكة العربية السعودية</span></strong></span><span dir="ltr">.</span></p>
    <p><span dir="rtl">وفي حال نشوء أي نزاع يتعلق باستخدام المتجر أو المنتجات أو الخدمات، تتم معالجته وفق الإجراءات والجهات المختصة نظامًا في المملكة العربية السعودية</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><strong>29.&nbsp;</strong><span dir="rtl">التواصل والشكاوى</span></p>
    <p><span dir="rtl">يمكن للعملاء التواصل مع شركة برايم إليمنتس للاستفسارات أو الشكاوى أو متابعة الطلبات من خلال بيانات التواصل الرسمية التالية</span><span dir="ltr">:</span></p>
    <p><span dir="rtl">اسم المنشأة</span>: <span dir="rtl">شركة برايم إليمنتس</span><br><span dir="rtl">الدولة</span>: <span dir="rtl">المملكة العربية السعودية</span><br><span dir="rtl">المدينة</span>: <span dir="rtl">الرياض</span><br><span dir="rtl">العنوان</span>: <span dir="rtl">الرياض، المملكة العربية السعودية</span><br><span dir="rtl">رقم السجل التجاري</span>: 7054698936<br><span dir="rtl">البريد الإلكتروني</span>: <a href="mailto:support@pmelements.com"><u><span>support@pmelements.com</span></u></a><br><span dir="rtl">رقم الهاتف</span>: +966 50 880 7708</p>
    <p><span dir="rtl">وتعمل الشركة على معالجة الاستفسارات والشكاوى وفق الإجراءات المعتمدة لديها وبما يتوافق مع الأنظمة المعمول بها</span><span dir="ltr">.</span></p>
    <div align="right">
        <hr size="2" align="right">
    </div>
    <p><span dir="rtl"><strong><span>آخر تحديث</span></strong><strong>: 25&nbsp;</strong><strong><span>أغسطس </span></strong><strong>2026</strong></span></p>
    <p>&nbsp;</p>
</div>',
                'return_exchange_policy' => '<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial; font-weight:bold;" dir="rtl">سياسة الاسترجاع والاستبدال والاسترداد</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span dir="rtl"><strong><span style="font-family:Arial;">آخر تحديث</span></strong><strong>: 26&nbsp;</strong><strong><span style="font-family:Arial;">أغسطس </span></strong><strong>2026</strong></span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span dir="rtl"><span style="font-family:Arial;">حرصًا من </span><strong><span style="font-family:Arial;">شركة برايم إليمنتس</span></strong><span style="font-family:Arial;"> على حماية حقوق عملائها وتقديم تجربة شراء واضحة وموثوقة، توضح هذه السياسة آلية الاسترجاع والاستبدال واسترداد المبالغ للمنتجات التي يتم شراؤها من خلال متجر برايم إليمنتس، وذلك وفقًا للأنظمة واللوائح المعمول بها في المملكة العربية السعودية</span></span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span dir="rtl"><strong><span style="font-family:Arial;">أولًا</span></strong><strong>:&nbsp;</strong><strong><span style="font-family:Arial;">الاسترجاع</span></strong></span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">يحق للعميل طلب استرجاع المنتج وفقًا للأحكام النظامية المعمول بها، شريطة أن يكون المنتج في الحالة التي تسمح بالاسترجاع، وألا يكون قد تم استخدامه أو الاستفادة منه، وذلك وفق طبيعة المنتج والاستثناءات المحددة نظامًا</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span dir="rtl"><span style="font-family:Arial;">ويكون طلب الاسترجاع خلال </span><strong><span style="font-family:Arial;">سبعة </span></strong><strong>(7)&nbsp;</strong><strong><span style="font-family:Arial;">أيام</span></strong><span style="font-family:Arial;"> من تاريخ استلام المنتج، ما لم ينطبق على المنتج استثناء نظامي أو تنص صفحة المنتج على شروط خاصة لا تتعارض مع الأنظمة</span></span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">وبالنسبة للمنتجات التي تم فتحها أو استخدامها أو تفعيلها إلكترونيًا، مثل الكتب الرقمية أو أكواد الوصول أو الاشتراكات التعليمية، فقد لا يكون الاسترجاع ممكنًا بعد بدء الاستخدام أو التفعيل، وذلك بحسب طبيعة المنتج وشروط الترخيص المرتبطة به، مع عدم الإخلال بأي حق نظامي للعميل</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span dir="rtl"><strong><span style="font-family:Arial;">ثانيًا</span></strong><strong>:&nbsp;</strong><strong><span style="font-family:Arial;">الاستبدال</span></strong></span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span dir="rtl"><span style="font-family:Arial;">يمكن للعميل طلب استبدال المنتج خلال </span><strong><span style="font-family:Arial;">سبعة </span></strong><strong>(7)&nbsp;</strong><strong><span style="font-family:Arial;">أيام من تاريخ استلامه</span></strong><span style="font-family:Arial;">، متى كان المنتج مؤهلًا للاستبدال وفق حالته وطبيعته</span></span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">ويشترط، بالنسبة للمنتجات التي تقبل الاستبدال، أن تكون بحالتها الأصلية وغير مستخدمة أو متضررة بسبب العميل، وأن تكون جميع الملحقات والمكونات المرتبطة بها متوفرة، متى كان ذلك منطبقًا</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">ولا يشمل الاستبدال المنتجات الرقمية أو أكواد الوصول التي تم تفعيلها أو استخدامها، إلا في حالات وجود عيب أو مشكلة فنية أو عدم مطابقة المنتج لما تم عرضه، وذلك وفقًا للحالة والأحكام النظامية ذات العلاقة</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span dir="rtl"><strong><span style="font-family:Arial;">ثالثًا</span></strong><strong>:&nbsp;</strong><strong><span style="font-family:Arial;">المنتجات التالفة أو المعيبة أو غير المطابقة</span></strong></span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">إذا استلم العميل منتجًا تالفًا أو معيبًا أو غير مطابق للمنتج المطلوب، فيجب عليه التواصل مع خدمة العملاء في أقرب وقت ممكن من تاريخ الاستلام، ويفضل إرفاق صور واضحة للمنتج والتغليف وبيانات الطلب</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">تقوم الشركة بدراسة الحالة واتخاذ الإجراء المناسب، والذي قد يشمل الاستبدال أو الاسترجاع أو رد المبلغ، بحسب طبيعة الحالة والأنظمة المعمول بها</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">ولا يتحمل العميل تكلفة الإرجاع في الحالات التي يكون فيها سبب الإرجاع راجعًا إلى عيب في المنتج أو عدم مطابقته للطلب، وفقًا لما تقرره الشركة والأنظمة ذات العلاقة</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span dir="rtl"><strong><span style="font-family:Arial;">رابعًا</span></strong><strong>:&nbsp;</strong><strong><span style="font-family:Arial;">الكتب والمنتجات الورقية</span></strong></span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">تخضع الكتب والمنتجات الورقية لحق الاسترجاع والاستبدال وفقًا للأحكام النظامية وبحسب حالة المنتج</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">وفي حال كان الكتاب أو المنتج الورقي قد تم استخدامه أو إتلافه أو فقد حالته الأصلية نتيجة استخدام العميل، فقد لا يكون مؤهلًا للاسترجاع، ما لم يكن هناك عيب أو عدم مطابقة أو حق آخر مقرر للعميل بموجب النظام</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span dir="rtl"><strong><span style="font-family:Arial;">خامسًا</span></strong><strong>:&nbsp;</strong><strong><span style="font-family:Arial;">الكتب الرقمية</span></strong></span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">نظرًا للطبيعة الخاصة للمنتجات الرقمية، فإن الكتاب أو المحتوى الرقمي الذي تم تنزيله أو الوصول إليه أو استخدامه قد لا يكون قابلًا للاسترجاع بعد بدء الانتفاع به، وذلك بحسب طبيعة المنتج وشروط الترخيص الخاصة به</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">أما في حال وجود مشكلة فنية تمنع العميل من الوصول إلى المنتج الذي تم شراؤه، فيجب التواصل مع خدمة العملاء للتحقق من المشكلة والعمل على معالجتها أو توفير الحل المناسب</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span dir="rtl"><strong><span style="font-family:Arial;">سادسًا</span></strong><strong>:&nbsp;</strong><strong><span style="font-family:Arial;">أكواد الوصول والمنصات التعليمية</span></strong></span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">تخضع أكواد الوصول إلى المنصات والمحتوى التعليمي للشروط الخاصة بكل منتج أو ناشر أو مزود خدمة</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">إذا لم يتم تفعيل الكود أو استخدامه، فيمكن للعميل تقديم طلب استرجاعه خلال المدة النظامية، ويخضع الطلب للتحقق من حالة الكود</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">أما بعد تفعيل الكود أو استخدامه أو بدء الاستفادة من المحتوى أو الاشتراك، فقد لا يكون قابلًا للاسترجاع، نظرًا لطبيعة المنتج الرقمي، وذلك مع مراعاة الحقوق النظامية للعميل وأي حالة يكون فيها الكود معيبًا أو غير صالح للتفعيل</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">وفي حال كان الكود غير صالح للتفعيل أو لا يعمل بالشكل الموضح في بيانات المنتج، يجب على العميل التواصل مع برايم إليمنتس، وستعمل الشركة على التحقق من المشكلة والتنسيق مع الناشر أو مزود الخدمة عند الحاجة</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span dir="rtl"><strong><span style="font-family:Arial;">سابعًا</span></strong><strong>:&nbsp;</strong><strong><span style="font-family:Arial;">المنتجات المقدمة من أطراف أخرى</span></strong></span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">قد تتضمن بعض المنتجات أو الخدمات أكوادًا أو اشتراكات أو خدمات يتم توفيرها من خلال ناشرين أو مزودي منصات تعليمية من أطراف أخرى</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">وفي هذه الحالات، قد تخضع عملية الاسترجاع أو الإلغاء لشروط الناشر أو مزود الخدمة، بالإضافة إلى الأنظمة السعودية ذات العلاقة</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">ولا يؤثر ذلك على أي حق إلزامي مقرر للعميل بموجب الأنظمة المعمول بها في المملكة العربية السعودية</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span dir="rtl"><strong><span style="font-family:Arial;">ثامنًا</span></strong><strong>:&nbsp;</strong><strong><span style="font-family:Arial;">طريقة تقديم طلب الاسترجاع أو الاستبدال</span></strong></span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">يمكن للعميل تقديم طلب الاسترجاع أو الاستبدال من خلال التواصل مع خدمة العملاء عبر</span><span dir="ltr">:</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial; font-weight:bold;" dir="rtl">البريد الإلكتروني</span>: <a href="mailto:support@pmelements.com" style="text-decoration:none;"><u><span style="color:#467886;">support@pmelements.com</span></u></a><br><span style="font-family:Arial; font-weight:bold;" dir="rtl">رقم الهاتف</span>: +966 50 880 7708</p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">ويجب على العميل عند تقديم الطلب تزويد الشركة بالبيانات اللازمة للتحقق من عملية الشراء، ومنها</span><span dir="ltr">:</span></p>
<ul type="disc" style="margin:0pt; padding-left:0pt;">
    <li style="margin-left:27.6pt; margin-bottom:8pt; text-align:right; line-height:116%; padding-left:8.4pt; font-family:serif; font-size:10pt;"><span style="line-height:116%; font-family:Arial; font-size:12pt;" dir="rtl">رقم الطلب</span><span style="line-height:116%; font-family:Aptos; font-size:12pt;">.</span></li>
    <li style="margin-left:27.6pt; margin-bottom:8pt; text-align:right; line-height:116%; padding-left:8.4pt; font-family:serif; font-size:10pt;"><span style="line-height:116%; font-family:Arial; font-size:12pt;" dir="rtl">فاتورة الشراء، إن وجدت</span><span style="line-height:116%; font-family:Aptos; font-size:12pt;">.</span></li>
    <li style="margin-left:27.6pt; margin-bottom:8pt; text-align:right; line-height:116%; padding-left:8.4pt; font-family:serif; font-size:10pt;"><span style="line-height:116%; font-family:Arial; font-size:12pt;" dir="rtl">بيانات العميل</span><span style="line-height:116%; font-family:Aptos; font-size:12pt;">.</span></li>
    <li style="margin-left:27.6pt; margin-bottom:8pt; text-align:right; line-height:116%; padding-left:8.4pt; font-family:serif; font-size:10pt;"><span style="line-height:116%; font-family:Arial; font-size:12pt;" dir="rtl">وصف سبب الاسترجاع أو الاستبدال</span><span style="line-height:116%; font-family:Aptos; font-size:12pt;">.</span></li>
    <li style="margin-left:27.6pt; margin-bottom:8pt; text-align:right; line-height:116%; padding-left:8.4pt; font-family:serif; font-size:10pt;"><span style="line-height:116%; font-family:Arial; font-size:12pt;" dir="rtl">صور المنتج، عند الحاجة، خصوصًا في حالات التلف أو العيب</span><span style="line-height:116%; font-family:Aptos; font-size:12pt;">.</span></li>
</ul>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">بعد استلام الطلب، تقوم الشركة بمراجعته والتواصل مع العميل بشأن الإجراء المناسب</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span dir="rtl"><strong><span style="font-family:Arial;">تاسعًا</span></strong><strong>:&nbsp;</strong><strong><span style="font-family:Arial;">استلام المنتجات المرتجعة</span></strong></span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">في حال الموافقة على الاسترجاع أو الاستبدال، قد يتم ترتيب استلام المنتج من خلال شركة شحن أو مندوب توصيل معتمد</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">يجب على العميل تجهيز المنتج وإعادته بالحالة المطلوبة، مع المحافظة على التغليف والمكونات والملحقات المرتبطة به متى كان ذلك ممكنًا</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">ويُفضل أن يحتفظ العميل بإثبات تسليم المنتج لشركة الشحن حتى اكتمال عملية الاسترجاع أو الاستبدال</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span dir="rtl"><strong><span style="font-family:Arial;">عاشرًا</span></strong><strong>:&nbsp;</strong><strong><span style="font-family:Arial;">استرداد المبالغ</span></strong></span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">عند الموافقة على استرداد قيمة الطلب، يتم رد المبلغ بالطريقة المناسبة وفق وسيلة الدفع المستخدمة في عملية الشراء، وبما يتوافق مع إجراءات مزود خدمة الدفع والأنظمة المعمول بها</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">قد تستغرق عملية ظهور المبلغ في حساب العميل مدة إضافية تعتمد على البنك أو مزود خدمة الدفع</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">ولا تلتزم الشركة بإعادة المبلغ نقدًا إذا كانت عملية الدفع قد تمت إلكترونيًا، وإنما تتم إعادة المبلغ من خلال وسيلة الدفع أو الآلية المسموح بها نظامًا</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">وفي حال وجود رسوم أو مبالغ تم تحصيلها نيابة عن طرف ثالث، فقد يخضع استرداد تلك المبالغ لشروط الطرف الثالث، مع مراعاة الحقوق النظامية للعميل</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span dir="rtl"><strong><span style="font-family:Arial;">الحادي عشر</span></strong><strong>:&nbsp;</strong><strong><span style="font-family:Arial;">رسوم الشحن والإرجاع</span></strong></span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">في الحالات التي يكون فيها الاسترجاع بسبب رغبة العميل في إلغاء الشراء، فقد تطبق رسوم الشحن أو الإرجاع بحسب طبيعة الطلب والسياسة المعمول بها، وذلك في الحدود التي يسمح بها النظام</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">أما في حال كان سبب الاسترجاع عيبًا في المنتج أو عدم مطابقته للطلب أو خطأ من الشركة، فتتحمل الشركة تكاليف الإرجاع والاستبدال التي تكون واجبة عليها نظامًا</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span dir="rtl"><strong><span style="font-family:Arial;">الثاني عشر</span></strong><strong>:&nbsp;</strong><strong><span style="font-family:Arial;">استلام الشحنة</span></strong></span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">عند استلام العميل للشحنة، يفضل التأكد من سلامة العبوة الخارجية وعدم وجود علامات واضحة على التلف</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">وفي حال وجود تلف ظاهر في الشحنة، ينصح العميل بتوثيق الحالة بالصور وإبلاغ شركة الشحن وبرايم إليمنتس في أقرب وقت ممكن</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">ولا يعني توقيع العميل على استلام الشحنة أو استلامها بأي حال التنازل عن أي حقوق نظامية تتعلق بعيب أو عدم مطابقة غير ظاهر عند الاستلام</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span dir="rtl"><strong><span style="font-family:Arial;">الثالث عشر</span></strong><strong>:&nbsp;</strong><strong><span style="font-family:Arial;">الفواتير وإثبات الشراء</span></strong></span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">قد تطلب الشركة من العميل تقديم رقم الطلب أو الفاتورة أو أي بيانات أخرى تساعد في التحقق من عملية الشراء</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">ولا يمنع عدم توفر الفاتورة الورقية من دراسة طلب العميل إذا كان بالإمكان التحقق من عملية الشراء من خلال سجلات المتجر أو وسائل أخرى مناسبة</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span dir="rtl"><strong><span style="font-family:Arial;">الرابع عشر</span></strong><strong>:&nbsp;</strong><strong><span style="font-family:Arial;">التأخر في التسليم</span></strong></span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">في حال تأخر المتجر في تسليم المنتج أو تنفيذ الخدمة وفق المدة النظامية أو المدة المتفق عليها، تكون للعميل الحقوق المقررة له بموجب نظام التجارة الإلكترونية والأنظمة ذات العلاقة</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">ويحق للمستهلك، وفق ما توضحه وزارة التجارة، إلغاء العملية واسترداد ما دفعه في الحالات التي يتأخر فيها موفر الخدمة عن التسليم أو التنفيذ لأكثر من&nbsp;</span>15 <span dir="rtl">يومًا</span><span style="font-family:Arial;">&nbsp;من تاريخ التعاقد أو الموعد المتفق عليه، ما لم يكن التأخير بسبب قوة قاهرة</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span dir="rtl"><strong><span style="font-family:Arial;">الخامس عشر</span></strong><strong>:&nbsp;</strong><strong><span style="font-family:Arial;">حقوق العميل</span></strong></span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">لا تهدف هذه السياسة إلى تقييد أو إسقاط أي حق من الحقوق المقررة للمستهلك بموجب الأنظمة واللوائح المعمول بها في المملكة العربية السعودية</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial;" dir="rtl">وفي حال وجود تعارض بين أي بند في هذه السياسة وحكم نظامي إلزامي، فيُعمل بالحكم النظامي في حدود ذلك التعارض</span><span dir="ltr">.</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial; font-weight:bold;" dir="rtl">بيانات التواصل</span></p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span style="font-family:Arial; font-weight:bold;" dir="rtl">شركة برايم إليمنتس</span><br><span dir="rtl"><strong><span style="font-family:Arial;">المملكة العربية السعودية </span></strong><strong>&ndash;&nbsp;</strong><strong><span style="font-family:Arial;">الرياض</span></strong></span><br><span style="font-family:Arial; font-weight:bold;" dir="rtl">رقم السجل التجاري</span>: 7054698936<br><span style="font-family:Arial; font-weight:bold;" dir="rtl">البريد الإلكتروني</span>: <a href="mailto:support@pmelements.com" style="text-decoration:none;"><u><span style="color:#467886;">support@pmelements.com</span></u></a><br><span style="font-family:Arial; font-weight:bold;" dir="rtl">الهاتف</span>: +966 50 880 7708</p>
<p style="margin-top:0pt; margin-bottom:8pt; text-align:right;"><span dir="rtl"><strong><span style="font-family:Arial;">آخر تحديث</span></strong><strong>: 26&nbsp;</strong><strong><span style="font-family:Arial;">أغسطس </span></strong><strong>2026</strong></span>&nbsp;</p>
<p style="bottom: 10px; right: 10px; position: absolute;"><br></p>',
                'updated_by' => 1,
            ]
        );
    }
}
