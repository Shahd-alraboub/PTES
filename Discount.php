<?php

class Discount {
    private $userType;
    private $originalPrice;
    private $baseDiscount;

    public function __construct($userType, $originalPrice, $baseDiscount) {
        $this->userType = $userType;
        $this->originalPrice = $originalPrice;
        $this->baseDiscount = $baseDiscount;
    }

    // تطبيق الخصومات الإضافية حسب نوع المستخدم
    public function calculateFinalDiscount() {
        $additionalDiscount = 0;

        switch ($this->userType) {
            case 'vip':
                $additionalDiscount = 10; // خصم إضافي 10% للمستخدمين VIP
                break;
            case 'student':
                $additionalDiscount = 5; // خصم إضافي 5% للطلاب
                break;
            default:
                $additionalDiscount = 0; // لا خصم إضافي للمستخدمين العاديين
        }

        return $this->baseDiscount + $additionalDiscount;
    }

    // حساب السعر النهائي بعد الخصم
    public function calculateFinalPrice() {
        $totalDiscount = $this->calculateFinalDiscount();
        $discountAmount = ($totalDiscount / 100) * $this->originalPrice;
        $finalPrice = $this->originalPrice - $discountAmount;

        return $finalPrice;
    }
}
?>
