<?php

return [
    'success' => 'نجاح',
    'error' => 'خطأ',
    'created' => 'تم الإنشاء',

    'auth' => [
        'login_successful' => 'تم تسجيل الدخول بنجاح',
        'logout_successful' => 'تم تسجيل الخروج بنجاح',
        'admin_login_successful' => 'تم تسجيل دخول المسؤول بنجاح',
        'admin_logout_successful' => 'تم تسجيل خروج المسؤول بنجاح',
        'registration_successful' => 'تم التسجيل بنجاح. يرجى التحقق من بريدك الإلكتروني.',
        'email_verified' => 'تم التحقق من البريد الإلكتروني بنجاح',
        'password_set' => 'تم تعيين كلمة المرور بنجاح',
        'password_reset' => 'تم إعادة تعيين كلمة المرور بنجاح',
        'password_changed' => 'تم تغيير كلمة المرور بنجاح',
        'password_reset_link_sent' => 'إذا كان البريد الإلكتروني موجودًا، فقد تم إرسال رابط إعادة تعيين كلمة المرور',
        'credentials_invalid' => 'بيانات الاعتماد المقدمة لا تتطابق مع سجلاتنا.',
        'account_blocked' => 'تم حظر حسابك. يرجى التواصل مع الدعم.',
        'account_inactive' => 'حسابك غير نشط. يرجى التواصل مع المسؤول.',
        'email_not_verified' => 'البريد الإلكتروني غير موثق، يرجى التحقق من البريد الإلكتروني قبل تسجيل الدخول.',
        'unauthenticated' => 'غير مصرح.',
        'session_ended' => 'انتهت جلستك. يرجى تسجيل الدخول مرة أخرى.',
        'current_password_incorrect' => 'كلمة المرور الحالية غير صحيحة.',
        'invitation_token_invalid' => 'رمز الدعوة غير صالح أو منتهي الصلاحية.',
        'password_reset_token_invalid' => 'رمز إعادة تعيين كلمة المرور غير صالح أو منتهي الصلاحية.',
        'verification_token_invalid' => 'رمز التحقق غير صالح أو منتهي الصلاحية.',
    ],

    'admin' => [
        'invitation_sent' => 'تم إرسال دعوة المسؤول بنجاح',
        'list_retrieved' => 'تم استرجاع المستخدمين بنجاح.',
        'retrieved' => 'تم استرجاع المسؤول بنجاح.',
        'updated' => 'تم تحديث المسؤول بنجاح.',
        'status_updated' => 'تم تحديث حالة المسؤول بنجاح.',
        'not_found' => 'المسؤول غير موجود.',
        'update_failed' => 'فشل تحديث المسؤول: :message',
        'only_super_admin_can_create' => 'يمكن للمديرين المتميزين فقط إنشاء مسؤولين جدد.',
        'only_super_admin_can_update' => 'يمكن للمديرين المتميزين فقط تحديث المسؤولين.',
        'cannot_remove_own_super_privileges' => 'لا يمكنك إزالة امتيازات المسؤول المتميز من حسابك.',
        'cannot_deactivate_own_account' => 'لا يمكنك تعطيل حسابك.',
        'role_not_exists' => 'الدور ":role" غير موجود.',
    ],

    'admin_role' => [
        'retrieved' => 'تم استرجاع أدوار المسؤول بنجاح.',
        'updated' => 'تم تحديث أدوار المسؤول بنجاح.',
        'role_not_exists' => 'الدور المحدد غير موجود.',
        'role_not_assigned' => 'المسؤول ليس لديه هذا الدور.',
        'role_removed' => 'تم إزالة الدور من المسؤول بنجاح.',
    ],

    'category' => [
        'retrieved' => 'تم استرجاع الفئات بنجاح.',
        'retrieved_single' => 'تم استرجاع الفئة بنجاح.',
        'created' => 'تم إنشاء الفئة بنجاح.',
        'updated' => 'تم تحديث الفئة بنجاح.',
        'deleted' => 'تم حذف الفئة بنجاح.',
        'cannot_delete' => 'لا يمكن حذف هذه الفئة.',
        'has_children' => 'لا يمكن حذف فئة تحتوي على فئات فرعية.',
        'has_products' => 'لا يمكن حذف فئة تحتوي على منتجات. يرجى نقل أو إلغاء تنشيط منتجاتها أولاً.',
    ],

    'title' => [
        'created' => 'تم إنشاء العنوان بنجاح',
        'updated' => 'تم تحديث العنوان بنجاح',
        'deleted' => 'تم حذف العنوان بنجاح',
        'cannot_delete' => 'لا يمكن حذف العنوان لأنه مرتبط بالفئات.',
    ],

    'product' => [
        'retrieved' => 'تم استرجاع المنتجات بنجاح.',
        'retrieved_single' => 'تم استرجاع المنتج بنجاح.',
        'created' => 'تم إنشاء المنتج بنجاح.',
        'updated' => 'تم تحديث المنتج بنجاح.',
        'deleted' => 'تم حذف المنتج بنجاح.',
        'cannot_delete' => 'لا يمكن حذف هذا المنتج.',
        'referenced_by_orders' => 'لا يمكن حذف منتج مرتبط بالطلبات. يرجى إلغاء تنشيطه بدلاً من ذلك.',
    ],

    'product_import' => [
        'completed' => 'اكتمل استيراد الكتب.',
        'invalid_file' => 'الملف المرفوع ليس مصنف XLSX صالحاً للقراءة.',
        'missing_books_sheet' => 'يجب أن يحتوي المصنف على ورقة باسم Books.',
        'invalid_headers' => 'عناوين أعمدة ورقة Books لا تطابق القالب القابل للتنزيل.',
        'too_many_rows' => 'يتجاوز المصنف الحد الأقصى البالغ :max كتاباً.',
        'invalid_category' => 'الفئة ":category" غير موجودة.',
        'invalid_boolean' => 'اختر مفعّل أو غير مفعّل.',
        'invalid_discount_start_date' => 'يجب أن يكون تاريخ بدء الخصم صالحاً وبصيغة YYYY-MM-DD.',
        'invalid_discount_end_date' => 'يجب أن يكون تاريخ انتهاء الخصم صالحاً وبصيغة YYYY-MM-DD.',
        'discount_end_before_start' => 'يجب أن يكون تاريخ انتهاء الخصم في نفس تاريخ البدء أو بعده.',
        'duplicate_isbn' => 'يوجد كتاب برقم ISBN ":isbn" بالفعل أو تم استيراده سابقاً في هذا الملف.',
        'create_failed' => 'تعذر إنشاء الكتاب.',
    ],

    'delivery_method' => [
        'retrieved' => 'تم استرجاع طرق التوصيل بنجاح.',
        'retrieved_single' => 'تم استرجاع طريقة التوصيل بنجاح.',
        'created' => 'تم إنشاء طريقة التوصيل بنجاح.',
        'updated' => 'تم تحديث طريقة التوصيل بنجاح.',
        'deleted' => 'تم حذف طريقة التوصيل بنجاح.',
    ],

    'payment_method' => [
        'retrieved' => 'تم استرجاع طرق الدفع بنجاح.',
        'retrieved_single' => 'تم استرجاع طريقة الدفع بنجاح.',
        'created' => 'تم إنشاء طريقة الدفع بنجاح.',
        'updated' => 'تم تحديث طريقة الدفع بنجاح.',
        'deleted' => 'تم حذف طريقة الدفع بنجاح.',
    ],

    'faq' => [
        'retrieved' => 'تم استرجاع الأسئلة الشائعة بنجاح.',
        'retrieved_single' => 'تم استرجاع السؤال بنجاح.',
        'created' => 'تم إنشاء السؤال بنجاح.',
        'updated' => 'تم تحديث السؤال بنجاح.',
        'deleted' => 'تم حذف السؤال بنجاح.',
        'status_updated' => 'تم تحديث حالة السؤال بنجاح.',
    ],

    'banner' => [
        'retrieved' => 'تم استرجاع البانرات بنجاح.',
        'retrieved_single' => 'تم استرجاع البانر بنجاح.',
        'created' => 'تم إنشاء البانر بنجاح.',
        'updated' => 'تم تحديث البانر بنجاح.',
        'deleted' => 'تم حذف البانر بنجاح.',
    ],

    'order' => [
        'retrieved' => 'تم استرجاع الطلبات بنجاح.',
        'retrieved_single' => 'تم استرجاع الطلب بنجاح.',
        'placed' => 'تم إنشاء الطلب بنجاح.',
        'cancelled' => 'تم إلغاء الطلب بنجاح.',
        'status_updated' => 'تم تحديث حالة الطلب بنجاح.',
        'not_found' => 'الطلب غير موجود.',
        'cannot_cancel' => 'لا يمكن إلغاء هذا الطلب.',
        'cannot_cancel_status' => 'لا يمكن إلغاء هذا الطلب لأن حالته ":status". يمكن إلغاء الطلبات المعلقة فقط.',
        'cannot_cancel_after_confirmation' => 'لا يمكن إلغاء هذا الطلب بعد التأكيد.',
        'invalid_status_transition' => 'لا يمكن تغيير الحالة من ":from" إلى ":to".',
        'cart_empty' => 'سلة التسوق فارغة. يرجى إضافة منتجات قبل إنشاء الطلب.',
        'account_blocked' => 'تم حظر حسابك. لا يمكنك تنفيذ إجراءات الطلب.',
        'insufficient_stock' => 'المخزون غير كافي ل ":product". المتوفر: :available، المطلوب: :requested.',
    ],

    'invoice' => [
        'title' => 'فاتورة',
        'payment_confirmed' => 'تم تأكيد الدفع',
        'order_number' => 'رقم الطلب',
        'order_date' => 'تاريخ الطلب',
        'payment_method' => 'طريقة الدفع',
        'shipping_address' => 'عنوان الشحن',
        'shipping_information' => 'معلومات الشحن',
        'method' => 'الطريقة',
        'shipping_fee' => 'رسوم الشحن',
        'tracking_number' => 'رقم التتبع',
        'estimated_delivery' => 'موعد التسليم المتوقع',
        'product' => 'المنتج',
        'author_note' => 'المؤلف / ملاحظة',
        'qty' => 'الكمية',
        'price' => 'السعر',
        'total' => 'الإجمالي',
        'subtotal' => 'المجموع الفرعي',
        'discount' => 'الخصم',
        'tax' => 'ضريبة القيمة المضافة',
        'total_amount' => 'المبلغ الإجمالي',
        'phone' => 'الهاتف',
        'email' => 'البريد الإلكتروني',
        'free' => 'مجاني',
        'bookstore' => 'مكتبة تعليمية للكتب',
        'footer' => 'مكتبتك الإلكترونية الموثوقة للكتب التعليمية والعامة.',
        'rights' => '© :year PrimeElements. جميع الحقوق محفوظة.',
    ],

    'user' => [
        'retrieved' => 'تم استرجاع المستخدمين بنجاح.',
        'retrieved_single' => 'تم استرجاع المستخدم بنجاح.',
        'status_updated' => 'تم تحديث حالة المستخدم بنجاح.',
        'not_found' => 'المستخدم غير موجود.',
    ],

    'role' => [
        'retrieved' => 'تم استرجاع الأدوار بنجاح.',
        'retrieved_single' => 'تم استرجاع الدور بنجاح.',
        'created' => 'تم إنشاء الدور بنجاح.',
        'updated' => 'تم تحديث الدور بنجاح.',
        'deleted' => 'تم حذف الدور بنجاح.',
        'super_admin_immutable' => 'لا يمكن تعديل دور SuperAdmin.',
        'super_admin_not_deletable' => 'لا يمكن حذف دور SuperAdmin.',
    ],

    'permission' => [
        'retrieved' => 'تم استرجاع الصلاحيات بنجاح.',
        'created' => 'تم إنشاء الصلاحية بنجاح.',
        'deleted' => 'تم حذف الصلاحية بنجاح.',
        'assigned_to_roles' => 'لا يمكن حذف صلاحية مخصصة للأدوار.',
    ],

    'review' => [
        'retrieved' => 'تم استرجاع التقييمات بنجاح.',
        'retrieved_single' => 'تم استرجاع التقييم بنجاح.',
        'submitted' => 'تم إرسال التقييم بنجاح.',
        'status_updated' => 'تم تحديث حالة التقييم بنجاح.',
        'not_eligible' => 'يمكنك تقييم هذا المنتج بعد إتمام طلبك.',
    ],

    'settings' => [
        'retrieved' => 'تم استرجاع الإعدادات بنجاح.',
        'updated' => 'تم تحديث الإعدادات بنجاح.',
    ],

    'policy' => [
        'retrieved' => 'تم استرجاع السياسات بنجاح.',
    ],

    'cart' => [
        'retrieved' => 'تم استرجاع سلة التسوق بنجاح.',
        'item_added' => 'تم إضافة المنتج إلى سلة التسوق بنجاح.',
        'item_updated' => 'تم تحديث عنصر سلة التسوق بنجاح.',
        'item_removed' => 'تم إزالة عنصر سلة التسوق بنجاح.',
        'cleared' => 'تم إفراغ سلة التسوق بنجاح.',
        'item_not_owned' => 'هذا العنصر لا ينتمي لسلة التسوق الخاصة بك.',
    ],

    'bundle' => [
        'retrieved' => 'تم استرجاع الحزمة بنجاح.',
        'retrieved_single' => 'تم استرجاع الحزمة بنجاح.',
        'created' => 'تم إنشاء الحزمة بنجاح.',
        'updated' => 'تم تحديث الحزمة بنجاح.',
        'deleted' => 'تم حذف الحزمة بنجاح.',
        'status_updated' => 'تم تحديث حالة الحزمة بنجاح.',
        'added_to_cart' => 'تمت إضافة منتجات الحزمة إلى سلة التسوق بنجاح.',
        'not_found' => 'رابط الحزمة غير صالح أو لم يعد متاحًا.',
        'empty' => 'هذه الحزمة لا تحتوي على أي منتجات.',
        'product_unavailable' => 'المنتج ":product" غير متاح.',
        'insufficient_stock' => 'المخزون غير كافٍ للمنتج ":product". المتاح: :available، المطلوب: :requested.',
        'insufficient_codes' => 'المنتج ":product" غير متاح حاليًا.',
    ],

    'wishlist' => [
        'retrieved' => 'تم استرجاع قائمة الأمنيات بنجاح.',
        'item_added' => 'تم إضافة المنتج إلى قائمة الأمنيات بنجاح.',
        'item_removed' => 'تم إزالة المنتج من قائمة الأمنيات بنجاح.',
        'item_not_owned' => 'هذا العنصر لا ينتمي لقائمة الأمنيات الخاصة بك.',
    ],

    'profile' => [
        'retrieved' => 'تم استرجاع الملف الشخصي بنجاح.',
        'updated' => 'تم تحديث الملف الشخصي بنجاح.',
    ],

    'newsletter' => [
        'subscribed' => 'تم الاشتراك بنجاح.',
        'unsubscribed' => 'تم تحديث تفضيل الاشتراك.',
    ],

    'contact' => [
        'message_sent' => 'تم إرسال رسالتك بنجاح.',
    ],

    'social' => [
        'unsupported_provider' => "المزود ':provider' غير مدعوم.",
        'email_unavailable' => 'تعذر استرداد البريد الإلكتروني من مزود التواصل الاجتماعي.',
    ],

    'ebook' => [
        'invalid_download_link' => 'رابط التحميل غير صالح أو منتهي الصلاحية.',
        'codes_retrieved' => 'تم استرجاع رموز الوصول بنجاح.',
        'codes_added' => 'تم إضافة رموز الوصول بنجاح.',
        'codes_imported' => 'اكتمل استيراد رموز الوصول.',
        'code_deleted' => 'تم حذف رمز الوصول بنجاح.',
        'code_exists' => 'رمز وصول أو أكثر موجود بالفعل.',
        'invalid_import_file' => 'يرجى رفع ملف XLSX أو CSV صالح للقراءة.',
        'invalid_import_header' => 'يجب أن يحتوي الصف الأول على عمود واحد فقط باسم access_code.',
        'one_code_per_row' => 'يجب أن يحتوي كل صف بيانات على رمز وصول واحد فقط.',
        'code_required' => 'رمز الوصول مطلوب.',
        'code_must_be_text' => 'يجب أن يكون رمز الوصول نصاً.',
        'code_too_long' => 'يجب ألا يتجاوز رمز الوصول 255 حرفاً.',
        'duplicate_code_in_file' => 'ظهر رمز الوصول هذا سابقاً في الملف المرفوع.',
        'import_row_error' => 'الصف :row: :reason',
        'cannot_delete_used' => 'لا يمكن حذف رمز وصول مستخدم.',
        'not_ebook_product' => 'هذا المنتج ليس كتابًا إلكترونيًا.',
        'insufficient_codes' => 'هذا المنتج غير متوفر حاليًا.',
    ],
];
