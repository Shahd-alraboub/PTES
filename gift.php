<?php
// Gift.php
include "includes/Database.php";

class Gift {
    private $conn;

    public function __construct($connection) {
        $this->conn = $connection;
    }

  // إضافة تذكرة هدية جديدة
public function addGiftTicket($senderID, $receiverID, $eventID) {
 
    $validation = $this->validateGift($eventID, $receiverID);
    if (!$validation['status']) {
        return $validation;
    }

    // تعديل الاستعلام لإزالة created_at
    $query = "INSERT INTO gifttickets (senderID, receiverID, eventID) 
              VALUES (:senderID, :receiverID, :eventID)";

    $stmt = $this->conn->prepare($query);

    if ($stmt) {
        $stmt->bindParam(':senderID', $senderID, PDO::PARAM_INT);
        $stmt->bindParam(':receiverID', $receiverID, PDO::PARAM_INT);
        $stmt->bindParam(':eventID', $eventID, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return [
                'status' => true,
                'message' => 'تم إرسال الهدية بنجاح',
                'giftID' => $this->conn->lastInsertId()
            ];
        } else {
            return [
                'status' => false,
                'message' => 'حدث خطأ أثناء إرسال الهدية'
            ];
        }
    }

    return [
        'status' => false,
        'message' => 'حدث خطأ في الاتصال'
    ];
}


    // الحصول على الهدايا المستلمة لمستخدم معين
    public function getReceivedGifts($receiverID) {
        $query = "SELECT g.*, e.title AS eventName, e.date AS eventDate, u.name AS senderName 
                  FROM gifttickets g 
                  LEFT JOIN events e ON g.eventID = e.eventID 
                  LEFT JOIN users u ON g.senderID = u.userID 
                  WHERE g.receiverID = :receiverID";

        $stmt = $this->conn->prepare($query);

        if ($stmt) {
            $stmt->bindParam(':receiverID', $receiverID, PDO::PARAM_INT);
            $stmt->execute();
            $gifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'status' => true,
                'gifts' => $gifts
            ];
        }

        return [
            'status' => false,
            'message' => 'حدث خطأ أثناء استرجاع الهدايا'
        ];
    }

    // الحصول على الهدايا المرسلة لمستخدم معين
    public function getSentGifts($senderID) {
        $query = "SELECT g.*, e.title AS eventName, e.date AS eventDate, u.name AS receiverName 
                  FROM gifttickets g 
                  LEFT JOIN events e ON g.eventID = e.eventID 
                  LEFT JOIN users u ON g.receiverID = u.userID 
                  WHERE g.senderID = :senderID";

        $stmt = $this->conn->prepare($query);

        if ($stmt) {
            $stmt->bindParam(':senderID', $senderID, PDO::PARAM_INT);
            $stmt->execute();
            $gifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'status' => true,
                'gifts' => $gifts
            ];
        }

        return [
            'status' => false,
            'message' => 'حدث خطأ أثناء استرجاع الهدايا المرسلة'
        ];
    }

    // التحقق من صحة الهدية
    public function validateGift($eventID, $receiverID) {
        // التحقق من وجود الحدث
        $query = "SELECT * FROM events WHERE eventID = :eventID AND date > NOW()";
        $stmt = $this->conn->prepare($query);

        if ($stmt) {
            $stmt->bindParam(':eventID', $eventID, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                return [
                    'status' => false,
                    'message' => 'الحدث غير متاح أو انتهى'
                ];
            }

            // التحقق من وجود هدية مكررة
            $checkQuery = "SELECT * FROM gifttickets WHERE eventID = :eventID AND receiverID = :receiverID";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':eventID', $eventID, PDO::PARAM_INT);
            $checkStmt->bindParam(':receiverID', $receiverID, PDO::PARAM_INT);
            $checkStmt->execute();
            $checkResult = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($checkResult) {
                return [
                    'status' => false,
                    'message' => 'تم إرسال هدية لهذا الحدث مسبقاً'
                ];
            }

            return [
                'status' => true,
                'message' => 'يمكن إرسال الهدية'
            ];
        }

        return [
            'status' => false,
            'message' => 'حدث خطأ أثناء التحقق من الهدية'
        ];
    }

    // استلام الهدية
    public function receiveGift($giftID, $userID) {
        // لا حاجة للتحقق من "status"، لأن هذا العمود لا يوجد في قاعدة البيانات
        $query = "INSERT INTO tickets (eventID, userID) 
                  SELECT eventID, receiverID 
                  FROM gifttickets 
                  WHERE giftTicketID = :gift_id AND receiverID = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':gift_id', $giftID, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userID, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true; // تم إضافة التذكرة بنجاح
        }

        return false;
    }
    // Gift.php
public function getGiftsNotReceived($userID) {
    // استعلام للحصول على الهدايا التي لم يتم استلامها بعد
    $query = "SELECT g.*, e.title AS eventName, e.date AS eventDate, u.name AS senderName 
    FROM gifttickets g 
    LEFT JOIN events e ON g.eventID = e.eventID 
    LEFT JOIN users u ON g.senderID = u.userID 
    WHERE g.receiverID = :userID 
    AND NOT EXISTS (
        SELECT 1 FROM tickets t 
        WHERE t.eventID = g.eventID 
        AND t.userID = g.receiverID
    )";

    $stmt = $this->conn->prepare($query);

    if ($stmt) {
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();
        $gifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $gifts;  // نعيد الهدايا فقط
    }

    return [];  // في حال حدوث خطأ نعيد مصفوفة فارغة
}

}
?>
