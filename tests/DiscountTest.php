<?php
//require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../Discount.php';
use PHPUnit\Framework\TestCase;

class DiscountTest extends TestCase
{
    private $conn; // محاكي للاتصال بقاعدة البيانات
    private $discount;

    // إعداد الاتصال بقاعدة البيانات أو محاكي لها
    protected function setUp(): void
    {
        // هنا يمكنك استخدام محاكي لقاعدة البيانات أو الاتصال الفعلي إذا أردت
        $this->conn = $this->createMock(PDO::class);
        
        // إنشاء كائن Discount لاختبار الوظائف
        $this->discount = new Discount($this->conn, 'طالب', 100, 10, 5);
    }

    // اختبار حساب الخصم النهائي
    public function testCalculateFinalDiscount()
    {
        $expectedDiscount = 15; // 10 + 5
        $this->assertEquals($expectedDiscount, $this->discount->calculateFinalDiscount());
    }

    // اختبار حساب السعر النهائي
    public function testCalculateFinalPrice()
    {
        $expectedPrice = 85; // 100 - (100 * 15 / 100)
        $this->assertEquals($expectedPrice, $this->discount->calculateFinalPrice());
    }

    // اختبار إضافة الخصم إلى قاعدة البيانات
    public function testAddDiscountToDatabase()
    {
        // إعداد محاكاة لقاعدة البيانات
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
             ->method('execute')
             ->willReturn(true);
        
        $this->conn->expects($this->once())
                   ->method('prepare')
                   ->willReturn($stmt);
        
        // التحقق مما إذا كانت عملية الإدخال ناجحة
        $this->assertTrue($this->discount->addDiscountToDatabase());
    }
}
?>
<!-- 
 vendor/bin/phpunit tests/DiscountTest.php

-->