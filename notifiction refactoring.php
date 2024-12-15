<?php
class NotificationManager {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // دالة لجلب الإشعارات مع Pagination
    public function getUserNotifications($userID, $limit = 10, $offset = 0) {
        try {
            $query = "SELECT notificationID, message, created_at 
                      FROM notifications 
                      WHERE userID = :userID 
                      ORDER BY created_at DESC 
                      LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // إدارة الأخطاء
            error_log("Database error: " . $e->getMessage());
            return [];
        }
    }
}
