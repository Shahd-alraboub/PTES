<!DOCTYPE html>
<html lang="en">
<head>
</head>
<body>
    <style>.ticket-details {
    background-color: #f9f9f9;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    margin: 20px;
    font-family: 'Arial', sans-serif;
    color: #333;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.ticket-details h3 {
    text-align: center;
    font-size: 24px;
    color: #007bff;
    margin-bottom: 20px;
}

.ticket-details p {
    font-size: 16px;
    margin: 10px 0;
}

.ticket-details strong {
    color: #333;
}

.ticket-details p {
    border-bottom: 1px solid #ddd;
    padding-bottom: 8px;
}

.ticket-details p:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.ticket-details .vip {
    color: #d9534f;
    font-weight: bold;
}

.ticket-details .normal {
    color: #5bc0de;
    font-weight: normal;
}

.ticket-details .ticket-price {
    font-size: 18px;
    font-weight: bold;
    color: #28a745;
}

.ticket-details .discount {
    font-size: 16px;
    color: #f0ad4e;
}

.ticket-details .refund {
    font-size: 16px;
    color: #5bc0de;
}

.ticket-details .last-day {
    font-size: 14px;
    color: #d9534f;
}
</style>
</body>
</html>
<?php
include_once '../includes/Database.php';

class Ticket {
    public $ticketID;
    public $userID;
    public $eventID;
    public $discountRate;
    public $isRefundable;
    public $lastDayTicket;
    public $ticketType;  // نوع التذكرة (عادية أو VIP)
    public $seatNumber;
    public $price;
    private $conn;

    // المُنشئ
    public function __construct($ticketID, $userID, $eventID, $discountRate, $isRefundable, $lastDayTicket, $seatNumber, $price, $conn) {
        // تعيين القيم
        $this->ticketID = $ticketID;
        $this->userID = $userID;
        $this->eventID = $eventID;
        $this->discountRate = $discountRate;
        $this->isRefundable = $isRefundable;
        $this->lastDayTicket = $lastDayTicket;
        $this->seatNumber = $seatNumber;
        $this->price = $price;
        $this->conn = $conn;
    }
    
  

    // دالة لاسترجاع تذاكر المستخدم
    public static function getUserTickets($userID, $conn) {
        $stmt = $conn->prepare("SELECT * FROM tickets WHERE userID = :userID");
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();
        
        $tickets = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tickets[] = new Ticket(
                $row['ticketID'], 
                $row['userID'], 
                $row['eventID'], 
                $row['discountRate'], 
                $row['isRefundable'], 
                $row['lastDayTicket'], 
                $row['ticketType'], 
                $row['seatNumber'], 
                $conn
            );
        }
        return $tickets;
    }

    // دالة لإلغاء التذكرة
    public function cancelTicket() {
        $query = "DELETE FROM tickets WHERE ticketID = :ticketID AND userID = :userID";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':ticketID', $this->ticketID);
        $stmt->bindParam(':userID', $this->userID);

        if ($stmt->execute()) {
            return "تم إلغاء التذكرة بنجاح.";
        } else {
            return "حدث خطأ أثناء إلغاء التذكرة.";
        }
    }
    public function getTicketID() {
        return $this->ticketID;
    }
    // دالة لاسترداد التذكرة
    public function refund() {
        $currentDate = date('Y-m-d');
        
        if ($this->isRefundable && $currentDate <= $this->lastDayTicket) {
            echo "تمت عملية استرداد التذكرة بنجاح.<br>";
            return true;
        } else {
            echo "عذراً، لا يمكن استرداد التذكرة بعد الموعد المحدد.<br>";
            return false;
        }
    }

    // دالة لحساب السعر النهائي للتذكرة بعد الخصم
    public function calculateFinalPrice($basePrice) {
        $discountAmount = ($this->discountRate / 100) * $basePrice;
        $finalPrice = $basePrice - $discountAmount;
        return $finalPrice;
    }

    // دالة لعرض تفاصيل التذكرة
    public function displayTicketDetails() {
        $stmt = $this->conn->prepare("SELECT title, location, date, regularPrice, vipPrice FROM events WHERE eventID = :eventID");
        $stmt->bindParam(':eventID', $this->eventID, PDO::PARAM_INT);
        $stmt->execute();
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
        $ticketPrice = ($this->ticketType == 'premium') ? $event['vipPrice'] : $event['regularPrice'];
    
        // عرض التفاصيل باستخدام HTML
        echo "<div class='ticket-details'>";
        echo "<h3>تفاصيل التذكرة</h3>";
        echo "<p><strong>رقم التذكرة:</strong> " . $this->ticketID . "</p>";
        echo "<p><strong>رقم المستخدم:</strong> " . $this->userID . "</p>";
        echo "<p><strong>رقم الحدث:</strong> " . $this->eventID . "</p>";
        echo "<p><strong>عنوان الحدث:</strong> " . $event['title'] . "</p>";
        echo "<p><strong>الموقع:</strong> " . $event['location'] . "</p>";
        echo "<p><strong>تاريخ الحدث:</strong> " . $event['date'] . "</p>";
        echo "<p><strong>نوع التذكرة:</strong> " . ($this->ticketType == 'premium' ? 'VIP' : 'عادية') . "</p>";
        echo "<p><strong>السعر:</strong> " . $ticketPrice . " دينار</p>";
        echo "<p><strong>الخصم:</strong> " . $this->discountRate . "%</p>";
        echo "<p><strong>قابل للاسترداد:</strong> " . ($this->isRefundable ? "نعم" : "لا") . "</p>";
        echo "<p><strong>آخر يوم لاسترداد التذكرة:</strong> " . $this->lastDayTicket . "</p>";
        echo "</div>";
    }
    
}
?>
