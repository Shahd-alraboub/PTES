<?php
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../payment.php';
// tests/PaymentTest.php
use PHPUnit\Framework\TestCase;

class PaymentTest extends TestCase {
    private $dbConnection;
    private $payment;

    // يتم تنفيذ هذه الدالة قبل كل اختبار
    protected function setUp(): void {
        // محاكاة الاتصال بقاعدة البيانات باستخدام PDO
        $this->dbConnection = $this->createMock(PDO::class);
        $this->payment = new Payment($this->dbConnection);
    }

    // اختبار إضافة دفعة جديدة
    public function testAddPaymentSuccess() {
        // تحضير البيانات
        $this->payment->setPaymentDetails(1, 100, 'Sadad');

        // إعداد التوقعات لمحاكاة الـ PDO
        $stmt = $this->createMock(PDOStatement::class);
        $this->dbConnection->method('prepare')->willReturn($stmt);
        $stmt->method('bindValue')->willReturn(true);  // استخدام bindValue بدلاً من bind_param
        $stmt->method('execute')->willReturn(true);

        // تأكيد أن إضافة الدفع ناجحة
        $this->assertTrue($this->payment->addPayment());
    }

    // اختبار إضافة دفعة مع بيانات غير صالحة
    public function testAddPaymentFailure() {
        // تحضير بيانات غير صالحة
        $this->payment->setPaymentDetails(-1, -100, 'InvalidType');

        // إعداد التوقعات لمحاكاة الـ PDO
        $stmt = $this->createMock(PDOStatement::class);
        $this->dbConnection->method('prepare')->willReturn($stmt);
        $stmt->method('bindValue')->willReturn(true);  // استخدام bindValue بدلاً من bind_param
        $stmt->method('execute')->willReturn(false);

        // تأكيد أن إضافة الدفع فشلت بسبب البيانات غير الصالحة
        $this->expectException(Exception::class);
        $this->payment->addPayment();
    }

    // اختبار جلب تفاصيل دفعة
    public function testGetPaymentDetails() {
        // تحضير البيانات
        $paymentID = 1;
        $result = [
            'paymentID' => 1,
            'paymentDate' => '2024-12-09',
            'ticketID' => 1,
            'amount' => 100,
            'paymentType' => 'Sadad'
        ];

        // محاكاة استعلام SQL لجلب الدفع
        $stmt = $this->createMock(PDOStatement::class);
        $this->dbConnection->method('prepare')->willReturn($stmt);
        $stmt->method('execute')->willReturn(true);
        // محاكاة دالة fetch لإرجاع البيانات
        $stmt->method('fetch')->willReturn($result);

        // تأكيد جلب تفاصيل الدفع
        $paymentDetails = $this->payment->getPaymentDetails($paymentID);
        $this->assertEquals($result, $paymentDetails);
    }

    // اختبار البحث عن دفعات حسب النوع
    public function testSearchPaymentsByType() {
        // تحضير البيانات
        $paymentType = 'Sadad';
        $result = [
            ['paymentID' => 1, 'paymentDate' => '2024-12-09', 'ticketID' => 1, 'amount' => 100, 'paymentType' => 'Sadad']
        ];

        // محاكاة استعلام SQL للبحث عن دفعات حسب النوع
        $stmt = $this->createMock(PDOStatement::class);
        $this->dbConnection->method('prepare')->willReturn($stmt);
        $stmt->method('execute')->willReturn(true);
        // محاكاة دالة fetchAll لإرجاع البيانات
        $stmt->method('fetchAll')->willReturn($result);

        // تأكيد البحث عن دفعات حسب النوع
        $payments = $this->payment->searchPaymentsByType($paymentType);
        $this->assertEquals($result, $payments);
    }

    // اختبار البحث عن دفعات حسب التاريخ
    public function testSearchPaymentsByDate() {
        // تحضير البيانات
        $startDate = '2024-01-01';
        $endDate = '2024-12-31';
        $result = [
            ['paymentID' => 1, 'paymentDate' => '2024-12-09', 'ticketID' => 1, 'amount' => 100, 'paymentType' => 'Sadad']
        ];

        // محاكاة استعلام SQL للبحث عن دفعات حسب التاريخ
        $stmt = $this->createMock(PDOStatement::class);
        $this->dbConnection->method('prepare')->willReturn($stmt);
        $stmt->method('execute')->willReturn(true);
        // محاكاة دالة fetchAll لإرجاع البيانات
        $stmt->method('fetchAll')->willReturn($result);

        // تأكيد البحث عن دفعات حسب التاريخ
        $payments = $this->payment->searchPaymentsByDate($startDate, $endDate);
        $this->assertEquals($result, $payments);
    }
}
?>
<!-- 
 ./vendor/bin/phpunit tests/PaymentTest.php
 -->

 