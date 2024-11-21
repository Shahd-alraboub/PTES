class Payment {
    private $paymentID;
    private $paymentDate;
    private $ticketID;
    private $amount;
    private $paymentType;
    private $conn; // database connection

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        $this->paymentDate = date('Y-m-d');
    }

    public function setPaymentDetails($ticketID, $amount, $paymentType) {
        $this->ticketID = $ticketID;
        $this->amount = $amount;
        $this->paymentType = $paymentType;
    }

    public function processPayment() {
        try {
            // التحقق من صحة نوع الدفع
            if (!in_array($this->paymentType, ['Sadad', 'MobiCash', 'LocalCard'])) {
                throw new Exception("طريقة دفع غير صالحة");
            }

            // إدخال بيانات الدفع في قاعدة البيانات
            $sql = "INSERT INTO Payments (paymentDate, ticketID, amount, paymentType) 
                    VALUES (?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sids", 
                $this->paymentDate, 
                $this->ticketID, 
                $this->amount, 
                $this->paymentType
            );

            if ($stmt->execute()) {
                $this->paymentID = $stmt->insert_id;
                return true;
            }
            return false;
        } catch (Exception $e) {
            throw new Exception("فشل في معالجة الدفع: " . $e->getMessage());
        }
    }

    public function refundPayment($paymentID) {
        try {
            // التحقق من وجود الدفع
            $sql = "SELECT * FROM Payments WHERE paymentID = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $paymentID);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                throw new Exception("لم يتم العثور على الدفع");
            }

            // التحقق من إمكانية استرداد التذكرة
            $payment = $result->fetch_assoc();
            $sql = "SELECT isRefundable FROM Tickets WHERE ticketID = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $payment['ticketID']);
            $stmt->execute();
            $ticketResult = $stmt->get_result();
            $ticket = $ticketResult->fetch_assoc();

            if (!$ticket['isRefundable']) {
                throw new Exception("التذكرة غير قابلة للاسترداد");
            }

            // تنفيذ عملية الاسترداد
            // هنا يمكن إضافة المنطق الخاص بإعادة المبلغ للعميل
            
            return true;
        } catch (Exception $e) {
            throw new Exception("فشل في استرداد المبلغ: " . $e->getMessage());
        }
    }

    public function getPaymentDetails($paymentID) {
        try {
            $sql = "SELECT p.*, t.seatNumber, e.title as eventTitle 
                    FROM Payments p 
                    JOIN Tickets t ON p.ticketID = t.ticketID 
                    JOIN Events e ON t.eventID = e.eventID 
                    WHERE p.paymentID = ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $paymentID);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                throw new Exception("لم يتم العثور على تفاصيل الدفع");
            }

            return $result->fetch_assoc();
        } catch (Exception $e) {
            throw new Exception("فشل في استرجاع تفاصيل الدفع: " . $e->getMessage());
        }
    }

    public function validatePaymentAmount($amount) {
        return is_numeric($amount) && $amount > 0;
    }

    public function generatePaymentReceipt($paymentID) {
        try {
            $paymentDetails = $this->getPaymentDetails($paymentID);
            
            $receipt = "=== إيصال الدفع ===\n";
            $receipt .= "رقم الدفع: " . $paymentDetails['paymentID'] . "\n";
            $receipt .= "تاريخ الدفع: " . $paymentDetails['paymentDate'] . "\n";
            $receipt .= "المبلغ: " . $paymentDetails['amount'] . " ريال\n";
            $receipt .= "طريقة الدفع: " . $paymentDetails['paymentType'] . "\n";
            $receipt .= "الفعالية: " . $paymentDetails['eventTitle'] . "\n";
            $receipt .= "رقم المقعد: " . $paymentDetails['seatNumber'] . "\n";
            $receipt .= "==================\n";

            return $receipt;
        } catch (Exception $e) {
            throw new Exception("فشل في إنشاء الإيصال: " . $e->getMessage());
        }
    }
}