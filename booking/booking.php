<?php

class Booking {
    private $db;
    private $conn;
    private $table_name = "Bookings";

    public $bookingID;
    public $eventID;
    public $userID;
    public $bookingDate;
    public $numberOfTickets;
    public $totalAmount;
    public $ticketType;
    public $isRefundable;
    public $status; // 'pending', 'confirmed', 'cancelled'

    public function __construct($db) {
        $this->db = $db;
        $this->conn = $db;
    }

    // التحقق من حالة تسجيل الدخول
    public static function checkLogin() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['userID'])) {
            header("Location: ../auth/login.php?redirect=book_event.php&id=" . $_GET['id']);            exit();
        }
        return $_SESSION['userID'];
    }

    // إنشاء حجز جديد
    public function createBooking() {
        $query = "INSERT INTO " . $this->table_name . " 
                 (eventID, userID, bookingDate, numberOfTickets, totalAmount, status) 
                 VALUES (?, ?, NOW(), ?, ?, 'pending')";
    
        $stmt = $this->conn->prepare($query);
    
        $stmt->bindParam(1, $this->eventID);
        $stmt->bindParam(2, $this->userID);
        $stmt->bindParam(3, $this->numberOfTickets);
        $stmt->bindParam(4, $this->totalAmount);
    
        if($stmt->execute()) {
            $this->bookingID = $this->conn->lastInsertId();
    
            // إضافة التذاكر المرتبطة بالحجز
            if ($this->addTicketsToBooking()) {
                return true;
            }
        }
        return false;
    }

    // التحقق من توفر التذاكر
    public function checkAvailability($eventID, $requestedTickets) {
        $event = new Event($this->conn);
        $event->eventID = $eventID;
        $remainingSeats = $event->getRemainingSeats();
        
        return $remainingSeats >= $requestedTickets;
    }

    // جلب تفاصيل الحجز
    public function getBookingDetails($bookingID) {
        $query = "SELECT b.*, e.title, e.date, e.price 
                 FROM " . $this->table_name . " b
                 JOIN Events e ON b.eventID = e.eventID 
                 WHERE b.bookingID = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $bookingID);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // إضافة تذاكر بعد إنشاء الحجز
    public function addTicketsToBooking() {
        // إنشاء استعلام لإدخال التذاكر
        $query = "INSERT INTO Tickets (userID, eventID, isRefundable, lastDayTicket, seatNumber, discountRate, bookingID)
                  VALUES (?, ?, TRUE, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);

        // تحديد القيم المطلوبة
        $lastDayTicket = date('Y-m-d', strtotime($this->bookingDate . ' +7 days')); // تاريخ انتهاء صلاحية التذكرة (على سبيل المثال بعد 7 أيام)
        $seatNumber = 'A1';  // على سبيل المثال
        $discountRate = 0.10;  // على سبيل المثال 10% خصم

        // تنفيذ الاستعلام لكل تذكرة حسب عدد التذاكر المحجوزة
        for ($i = 1; $i <= $this->numberOfTickets; $i++) {
            $stmt->bindParam(1, $this->userID);
            $stmt->bindParam(2, $this->eventID);
            $stmt->bindParam(3, $lastDayTicket);
            $stmt->bindParam(4, $seatNumber);
            $stmt->bindParam(5, $discountRate);
            $stmt->bindParam(6, $this->bookingID);

            // تغيير رقم المقعد للتذكرة التالية (أو يمكن تخصيصها بشكل مختلف)
            $seatNumber = 'A' . ($i + 1);

            // تنفيذ الاستعلام
            if (!$stmt->execute()) {
                return false; // إذا فشلت العملية، ترجع false
            }
        }

        return true; // إذا تم الإدخال بنجاح، ترجع true
    }

    // جلب آخر مقعد تم حجزه لهذا النوع من التذاكر
    public function getLastSeatNumber($eventID, $ticketType) {
        $seatQuery = "SELECT MAX(seatNumber) AS lastSeat FROM Tickets WHERE eventID = :eventID AND ticketType = :ticketType";
        $seatStmt = $this->db->prepare($seatQuery);
        $seatStmt->bindParam(":eventID", $eventID);
        $seatStmt->bindParam(":ticketType", $ticketType);
        $seatStmt->execute();
        return $seatStmt->fetch(PDO::FETCH_ASSOC)['lastSeat'] ?? 0;
    }

    // إضافة التذاكر إلى جدول Tickets
    public function addTickets($eventID, $userID, $numberOfTickets, $ticketType, $isRefundable, $lastSeat) {
        $insertTicketQuery = "INSERT INTO Tickets (eventID, userID, seatNumber, ticketType, isRefundable) 
                              VALUES (:eventID, :userID, :seatNumber, :ticketType, :isRefundable)";
        $insertTicketStmt = $this->db->prepare($insertTicketQuery);
    
        // افترض أن $lastSeat يتضمن حرفًا متبوعًا برقم (مثل A1)
        $letter = substr($lastSeat, 0, 1); // استخراج الحرف
        $number = (int)substr($lastSeat, 1); // استخراج الرقم وتحويله إلى عدد صحيح
    
        for ($i = 1; $i <= $numberOfTickets; $i++) {
            // إضافة الرقم إلى الرقم المستخلص من $lastSeat
            $newSeatNumber = $letter . ($number + $i);
    
            $insertTicketStmt->bindParam(":eventID", $eventID);
            $insertTicketStmt->bindParam(":userID", $userID);
            $insertTicketStmt->bindParam(":seatNumber", $newSeatNumber);
            $insertTicketStmt->bindParam(":ticketType", $ticketType);
            $insertTicketStmt->bindParam(":isRefundable", $isRefundable);
            $insertTicketStmt->execute();
        }
    }
    
    

    // تحديث عدد المقاعد المتبقية في جدول Events
    public function updateSeatAvailability($eventID, $ticketType, $numberOfTickets) {
        $seatsColumn = ($ticketType === 'premium') ? 'vipSeats' : 'regularSeats';
        $updateSeatsQuery = "UPDATE Events SET $seatsColumn = $seatsColumn - :numberOfTickets WHERE eventID = :eventID";
        $updateSeatsStmt = $this->db->prepare($updateSeatsQuery);
        $updateSeatsStmt->bindParam(":numberOfTickets", $numberOfTickets, PDO::PARAM_INT);
        $updateSeatsStmt->bindParam(":eventID", $eventID, PDO::PARAM_INT);
        return $updateSeatsStmt->execute();
    }
}
?>
