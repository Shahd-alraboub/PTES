<?php
class Event {
    private $conn;
    private $table_name = "Events";

    public $eventID;
    public $date;
    public $title;
    public $location;
    public $regularSeats;
    public $vipSeats;
    public $regularPrice;
    public $vipPrice;
    public $isVIP;

    public function __construct($db) {
        $this->conn = $db;
    }

    // إضافة حدث جديد
    public function addEvent() {
        $stmt = $this->conn->prepare("INSERT INTO Events (date, title, location, regularSeats, vipSeats, regularPrice, vipPrice) 
                                      VALUES (:date, :title, :location, :regularSeats, :vipSeats, :regularPrice, :vipPrice)");
        $stmt->bindParam(':date', $this->date);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':location', $this->location);
        $stmt->bindParam(':regularSeats', $this->regularSeats);
        $stmt->bindParam(':vipSeats', $this->vipSeats);
        $stmt->bindParam(':regularPrice', $this->regularPrice);
        $stmt->bindParam(':vipPrice', $this->vipPrice);
        return $stmt->execute();
    }

    // تحديث حدث
    public function updateEvent() {
        $stmt = $this->conn->prepare("UPDATE Events SET date = :date, title = :title, location = :location,
                                      regularSeats = :regularSeats, vipSeats = :vipSeats, 
                                      regularPrice = :regularPrice, vipPrice = :vipPrice WHERE eventID = :eventID");
        $stmt->bindParam(':date', $this->date);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':location', $this->location);
        $stmt->bindParam(':regularSeats', $this->regularSeats);
        $stmt->bindParam(':vipSeats', $this->vipSeats);
        $stmt->bindParam(':regularPrice', $this->regularPrice);
        $stmt->bindParam(':vipPrice', $this->vipPrice);
        $stmt->bindParam(':eventID', $this->eventID);
        return $stmt->execute();
    }

    // حذف حدث
    public function deleteEvent() {
        $stmt = $this->conn->prepare("DELETE FROM Events WHERE eventID = :eventID");
        $stmt->bindParam(':eventID', $this->eventID);
        return $stmt->execute();
    }

    // استرجاع جميع الأحداث
    public function getEvents() {
        $stmt = $this->conn->prepare("SELECT * FROM Events ORDER BY date ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // استرجاع حدث واحد حسب ID
    public function getEventById($eventID) {
        $stmt = $this->conn->prepare("SELECT * FROM Events WHERE eventID = :eventID");
        $stmt->bindParam(':eventID', $eventID);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // التحقق إذا كانت التذاكر قد بيعت بالكامل
    public function isSoldOut($totalCapacity = 100) {
        $query = "SELECT COUNT(*) as sold FROM Tickets WHERE eventID = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->eventID);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $totalCapacity - $row['sold'] <= 0;
    }

    // استرجاع المراجعات الخاصة بالحدث
    public function getEventReviews() {
        $query = "SELECT r.*, u.name FROM Reviews r JOIN Users u ON r.userID = u.userID WHERE r.eventID = ? ORDER BY r.reviewID DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->eventID);
        $stmt->execute();
        return $stmt;
    }

    // حساب التقييم المتوسط
    public function getAverageRating() {
        $query = "SELECT AVG(rating) as avg_rating FROM Reviews WHERE eventID = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->eventID);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return isset($row['avg_rating']) ? round($row['avg_rating'], 1) : 0;
    }

    // تحقق إذا كان الحدث قريب من تاريخه للخصومات اللحظية
    public function checkLastMinuteDiscount() {
        if (empty($this->date)) {
            return false;
        }
        $eventDate = new DateTime($this->date);
        $today = new DateTime();
        $interval = $today->diff($eventDate);
        return ($interval->days <= 3 && $interval->invert == 0);
    }

    // استرجاع جميع الأحداث التي هي VIP فقط
    public function getVIPEvents() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE isVIP = 1 ORDER BY date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    public function getRemainingSeats() {
        $query = "SELECT COUNT(*) as sold FROM Tickets WHERE eventID = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->eventID);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return 100 - $row['sold'];
    }
}
?>
