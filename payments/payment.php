<?php
// include '../includes/Database.php';

class Payment {
    private $paymentID;
    private $paymentDate;
    private $ticketID;
    private $amount;
    private $paymentType;
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        $this->paymentDate = date('Y-m-d');
    }

    /**
     * تعيين تفاصيل الدفع
     */
    public function setPaymentDetails($ticketID, $amount, $paymentType) {
        $this->ticketID = $ticketID;
        $this->amount = $amount;
        $this->paymentType = $paymentType;
    }

    /**
     * إضافة دفعة جديدة
     */
    public function addPayment() {
        try {
            if (!$this->validatePayment()) {
                throw new Exception("بيانات الدفع غير صالحة");
            }

            $sql = "INSERT INTO payments (paymentDate, ticketID, amount, paymentType) 
                    VALUES (:paymentDate, :ticketID, :amount, :paymentType)";
            
            // تحضير الاستعلام باستخدام PDO
            $stmt = $this->conn->prepare($sql);
            
            // ربط القيم بالمعاملات باستخدام bindValue
            $stmt->bindValue(':paymentDate', $this->paymentDate, PDO::PARAM_STR);
            $stmt->bindValue(':ticketID', $this->ticketID, PDO::PARAM_INT);
            $stmt->bindValue(':amount', $this->amount, PDO::PARAM_INT);
            $stmt->bindValue(':paymentType', $this->paymentType, PDO::PARAM_STR);
            
            // تنفيذ الاستعلام
            if ($stmt->execute()) {
                $this->paymentID = $this->conn->lastInsertId(); // استخدام lastInsertId() للحصول على ID المدفوعات الجديد
                return true;
            }
            return false;
        } catch (Exception $e) {
            throw new Exception("فشل في إضافة الدفع: " . $e->getMessage());
        }
    }

    /**
     * التحقق من صحة بيانات الدفع
     */
    private function validatePayment() {
        if (!is_numeric($this->amount) || $this->amount <= 0) {
            return false;
        }

        if (!in_array($this->paymentType, ['Sadad', 'MobiCash', 'LocalCard'])) {
            return false;
        }

        if (!is_numeric($this->ticketID) || $this->ticketID <= 0) {
            return false;
        }

        return true;
    }

    /**
     * جلب تفاصيل دفعة محددة
     */
    public function getPaymentDetails($paymentID) {
        try {
            $sql = "SELECT * FROM payments WHERE paymentID = :paymentID";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':paymentID', $paymentID, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                throw new Exception("لم يتم العثور على الدفعة");
            }

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("فشل في جلب تفاصيل الدفعة: " . $e->getMessage());
        }
    }

    /**
     * جلب جميع الدفعات
     */
    public function getAllPayments() {
        try {
            $sql = "SELECT * FROM payments ORDER BY paymentDate DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // استخدام fetchAll بدلاً من fetch_all
        } catch (Exception $e) {
            throw new Exception("فشل في جلب الدفعات: " . $e->getMessage());
        }
    }

    /**
     * تحديث بيانات الدفع
     */
    public function updatePayment($paymentID, $amount, $paymentType) {
        try {
            if (!in_array($paymentType, ['Sadad', 'MobiCash', 'LocalCard']) || !is_numeric($amount) || $amount <= 0) {
                throw new Exception("بيانات غير صالحة للتحديث");
            }

            $sql = "UPDATE payments SET amount = :amount, paymentType = :paymentType WHERE paymentID = :paymentID";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':amount', $amount, PDO::PARAM_INT);
            $stmt->bindValue(':paymentType', $paymentType, PDO::PARAM_STR);
            $stmt->bindValue(':paymentID', $paymentID, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("فشل في تحديث الدفعة: " . $e->getMessage());
        }
    }

    /**
     * حذف دفعة
     */
    public function deletePayment($paymentID) {
        try {
            $sql = "DELETE FROM payments WHERE paymentID = :paymentID";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':paymentID', $paymentID, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("فشل في حذف الدفعة: " . $e->getMessage());
        }
    }

    /**
     * البحث عن دفعات حسب النوع
     */
    public function searchPaymentsByType($paymentType) {
        try {
            $sql = "SELECT * FROM payments WHERE paymentType = :paymentType ORDER BY paymentDate DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':paymentType', $paymentType, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("فشل في البحث عن الدفعات: " . $e->getMessage());
        }
    }

    /**
     * البحث عن دفعات حسب التاريخ
     */
    public function searchPaymentsByDate($startDate, $endDate) {
        try {
            $sql = "SELECT * FROM payments WHERE paymentDate BETWEEN :startDate AND :endDate ORDER BY paymentDate DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':startDate', $startDate, PDO::PARAM_STR);
            $stmt->bindValue(':endDate', $endDate, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("فشل في البحث عن الدفعات: " . $e->getMessage());
        }
    }
}
?>
