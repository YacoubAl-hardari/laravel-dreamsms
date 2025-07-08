<?php

namespace DreamSms\LaravelDreamSms\Exceptions;

use Exception;

class DreamSmsException extends Exception
{
    // Common API error codes
    public const MISSING_PARAMETERS            = 100;
    public const USERNAME_ALREADY_USED         = 110;
    public const MOBILE_ALREADY_USED           = 111;
    public const EMAIL_ALREADY_USED            = 112;
    public const USERNAME_INVALID_CHARACTERS   = 120;
    public const PASSWORD_TOO_SHORT            = 121;
    public const INVALID_USERNAME              = 122;
    public const INVALID_PASSWORD              = 123;

    public const ACTIVATE_WRONG_CREDENTIALS    = 110;
    public const ACTIVATE_WRONG_CODE           = 111;

    public const CHK_USER_NOT_EXIST            = -110;
    public const CHK_USER_NOT_ACTIVATED        = -111;
    public const CHK_USER_BLOCKED              = -112;

    public const BALANCE_INVALID_CREDENTIALS   = -110;

    public const SENDER_DUPLICATE              = -113;
    public const SENDER_INVALID                = -114;

    public const SENDSMS_INSUFFICIENT_BALANCE  = -113;
    public const SENDSMS_SERVICE_UNAVAILABLE   = -114;
    public const SENDSMS_INVALID_SENDER        = -116;
    public const SENDSMS_INVALID_NUMBER        = -117;
    public const SENDSMS_LATER_DATETIME        = -119;
    public const SENDSMS_NOT_ALLOWED_NUMBER    = -122;
    public const SENDMULTI_LIMIT_EXCEEDED      = -123;
    public const SENDMULTI_IP_NOT_ALLOWED      = -124;

    /**
     * Map error codes to Arabic messages
     *
     * @var array<int,string>
     */
    protected static array $messagesAr = [
        self::MISSING_PARAMETERS          => 'المعلمات مفقودة',
        self::USERNAME_ALREADY_USED       => 'اسم المستخدم مستخدم مسبقاً',
        self::MOBILE_ALREADY_USED         => 'رقم الجوال مستخدم مسبقاً',
        self::EMAIL_ALREADY_USED          => 'البريد الإلكتروني مستخدم مسبقاً',
        self::USERNAME_INVALID_CHARACTERS => 'اسم المستخدم يحتوي على أحرف غير مسموحة',
        self::PASSWORD_TOO_SHORT          => 'كلمة المرور قصيرة جداً',
        self::INVALID_USERNAME            => 'اسم المستخدم غير صالح',
        self::INVALID_PASSWORD            => 'كلمة المرور غير صالحة',
        self::ACTIVATE_WRONG_CREDENTIALS  => 'اسم المستخدم أو المفتاح السري غير صحيح',
        self::ACTIVATE_WRONG_CODE         => 'رمز التفعيل غير صحيح',
        self::CHK_USER_NOT_EXIST          => 'الحساب غير موجود',
        self::CHK_USER_NOT_ACTIVATED      => 'الحساب غير مفعل',
        self::CHK_USER_BLOCKED            => 'الحساب محظور',
        self::BALANCE_INVALID_CREDENTIALS => 'بيانات الحساب غير صحيحة',
        self::SENDER_DUPLICATE            => 'اسم المرسل مكرر',
        self::SENDER_INVALID              => 'اسم المرسل غير صالح',
        self::SENDSMS_INSUFFICIENT_BALANCE => 'رصيد غير كافٍ',
        self::SENDSMS_SERVICE_UNAVAILABLE => 'الخدمة غير متاحة حالياً',
        self::SENDSMS_INVALID_SENDER      => 'اسم المرسل غير متاح',
        self::SENDSMS_INVALID_NUMBER      => 'رقم المرسل إليه غير صالح',
        self::SENDSMS_LATER_DATETIME      => 'الوقت أو التاريخ غير صحيح',
        self::SENDSMS_NOT_ALLOWED_NUMBER  => 'الرقم غير مسموح به',
        self::SENDMULTI_LIMIT_EXCEEDED    => 'تم تجاوز الحد اليومي للمرسل',
        self::SENDMULTI_IP_NOT_ALLOWED    => 'IP غير مسموح به',
    ];

    /**
     * The full API response data
     *
     * @var array|null
     */
    protected ?array $responseData = null;

    /**
     * Create exception from API JSON response
     *
     * @param string $body    Raw response body
     * @param int    $status  HTTP status code
     * @return static
     */
    public static function fromResponse(string $body, int $status): ?self
    {
        $data = json_decode($body, true);

        if (!is_array($data)) {
            $data = ['code' => $status, 'message' => $body];
        }

        // ✅ تحقق إن كانت الاستجابة فعلاً تشير إلى نجاح
        $message = strtolower($data['message'] ?? '');
        $code = $data['code'] ?? $data['Code'] ?? $status;
        $code = (int)$code;

        // ✅ تجاهل الخطأ إذا الرسالة "Success" والكود 200
        if ($code === 200 && $message === 'success') {
            return null;
        }

        if (isset(self::$messagesAr[$code])) {
            $message = self::$messagesAr[$code];
        } else {
            $message = $data['message'] ?? $data['Description'] ?? 'خطأ غير معروف';
        }

        $exception = new static($message, $code);
        $exception->responseData = $data;

        return $exception;
    }


    /**
     * Retrieve decoded response data
     *
     * @return array|null
     */
    public function getResponseData(): ?array
    {
        return $this->responseData;
    }
}
