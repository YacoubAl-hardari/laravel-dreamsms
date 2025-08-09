<?php

namespace DreamSms\LaravelDreamSms\Exceptions;

use Exception;

class DreamSmsException extends Exception
{

    // Register
    public const MISSING_PARAMETERS          = -100;
    public const USERNAME_ALREADY_USED       = -110;
    public const MOBILE_ALREADY_USED         = -111;
    public const EMAIL_ALREADY_USED          = -112;
    public const USERNAME_INVALID_CHARACTERS = -120;
    public const PASSWORD_TOO_SHORT          = -121;

    // Activate
    public const ACTIVATE_WRONG_CREDENTIALS  = -110;
    public const ACTIVATE_WRONG_CODE         = -111;

    // chk_user
    public const CHK_USER_NOT_EXIST          = -110;
    public const CHK_USER_NOT_ACTIVATED      = -111;
    public const CHK_USER_BLOCKED            = -112;

    // Balance
    public const BALANCE_INVALID_CREDENTIALS = -110;

    // Sender
    public const SENDER_DUPLICATE            = -113;
    public const SENDER_INVALID              = -114;

    // Send SMS
    public const SENDSMS_INSUFFICIENT_BALANCE = -113;
    public const SENDSMS_SERVICE_UNAVAILABLE  = -114;
    public const SENDSMS_INVALID_SENDER       = -116;
    public const SENDSMS_INVALID_NUMBER       = -117;
    public const SENDSMS_LATER_DATETIME       = -119;
    public const SENDSMS_NOT_ALLOWED_NUMBER   = -122;
    public const SENDMULTI_LIMIT_EXCEEDED     = -123;
    public const SENDMULTI_IP_NOT_ALLOWED     = -124;

    // Success (حسب وثائق بعض المسارات)
    public const SUCCESS_CODE = 999;

    /**
     * @var array<int,string>
     */
    protected static array $messagesAr = [
        self::MISSING_PARAMETERS            => 'المعلمات مفقودة',
        self::USERNAME_ALREADY_USED         => 'اسم المستخدم مستخدم مسبقاً',
        self::MOBILE_ALREADY_USED           => 'رقم الجوال مستخدم مسبقاً',
        self::EMAIL_ALREADY_USED            => 'البريد الإلكتروني مستخدم مسبقاً',
        self::USERNAME_INVALID_CHARACTERS   => 'اسم المستخدم يحتوي على أحرف غير مسموحة',
        self::PASSWORD_TOO_SHORT            => 'كلمة المرور قصيرة جداً',
        self::ACTIVATE_WRONG_CREDENTIALS    => 'اسم المستخدم أو المفتاح السري غير صحيح',
        self::ACTIVATE_WRONG_CODE           => 'رمز التفعيل غير صحيح',
        self::CHK_USER_NOT_EXIST            => 'الحساب غير موجود',
        self::CHK_USER_NOT_ACTIVATED        => 'الحساب غير مفعل',
        self::CHK_USER_BLOCKED              => 'الحساب محظور',
        self::BALANCE_INVALID_CREDENTIALS   => 'بيانات الحساب غير صحيحة',
        self::SENDER_DUPLICATE              => 'اسم المرسل مكرر',
        self::SENDER_INVALID                => 'اسم المرسل غير صالح',
        self::SENDSMS_INSUFFICIENT_BALANCE  => 'رصيد غير كافٍ',
        self::SENDSMS_SERVICE_UNAVAILABLE   => 'الخدمة غير متاحة حالياً',
        self::SENDSMS_INVALID_SENDER        => 'اسم المرسل غير متاح',
        self::SENDSMS_INVALID_NUMBER        => 'رقم المرسل إليه غير صالح',
        self::SENDSMS_LATER_DATETIME        => 'الوقت أو التاريخ غير صحيح',
        self::SENDSMS_NOT_ALLOWED_NUMBER    => 'الرقم غير مسموح به',
        self::SENDMULTI_LIMIT_EXCEEDED      => 'تم تجاوز الحد اليومي للمرسل',
        self::SENDMULTI_IP_NOT_ALLOWED      => 'IP غير مسموح به',
    ];

    /**
     * الاستجابة المفككة (إن وُجدت)
     *
     * @var array|null
     */
    protected ?array $responseData = null;

    /**
     * @param string $body   قد يكون JSON أو نص عادي أو XML
     * @param int    $status كود HTTP
     * @return static|null
     */
    public static function fromResponse(string $body, int $status): ?self
    {
        $body = trim($body);

        if ($body === '') {
            return new static(
                '🚫 عذرًا، خدمة الرسائل غير متاحة حاليًا. الرجاء المحاولة لاحقًا أو التواصل مع الدعم الفني.',
                $status
            );
        }

        $lower = mb_strtolower($body, 'UTF-8');
        if ($lower === 'success' || (str_contains($lower, 'result') && str_contains($lower, 'success'))) {
            return null;
        }

        // --- نجاح XML usersender (إرجاع قائمة بالمرسلين) ---
        if (str_starts_with($body, '<')) {
            if (stripos($body, '<usersender') !== false) {
                return null;
            }
            // XML غير متوقع
            return new static('فشل الطلب: استجابة XML غير متوقعة من الخادم.', $status);
        }

        $data = json_decode($body, true);

        if (!is_array($data)) {
            return new static($body, $status);
        }

        // استخراج الحقول الشائعة
        $message = (string) ($data['message'] ?? $data['Message'] ?? '');
        $code    = (int)   ($data['code']    ?? $data['Code']    ?? $status);

        // ✅ حالات النجاح المعروفة:
        // - code = 999 (نجاح حسب بعض المسارات)
        // - code = 200 و message = success (غير حساس لحالة الأحرف)
        if (
            $code === self::SUCCESS_CODE ||
            ($code === 200 && mb_strtolower($message, 'UTF-8') === 'success')
        ) {
            return null;
        }

        if (isset(self::$messagesAr[$code])) {
            $message = self::$messagesAr[$code];
        } else {
            $message = $message !== '' ? $message : ($data['Description'] ?? 'حدث خطأ غير متوقع أثناء الاتصال بالخدمة.');
        }

        $exception = new static($message, $code);
        $exception->responseData = $data;

        return $exception;
    }

    public function getResponseData(): ?array
    {
        return $this->responseData;
    }
}
