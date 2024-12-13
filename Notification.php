<?php
class Notification {
    private $conn;
    private $table_name = "notifications";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function addNotification($userID, $message) {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                    (userID, message, created_at) 
                    VALUES (:user_id, :message, NOW())";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $userID);
            $stmt->bindParam(":message", $message);

            return $stmt->execute();
        } catch(PDOException $e) {
            throw new Exception("فشل في إضافة الإشعار: " . $e->getMessage());
        }
    }

    public function getUserIDByEmailOrID($identifier) {
        try {
            $query = "SELECT userID FROM users WHERE userID = :identifier OR email = :identifier";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':identifier', $identifier);
            $stmt->execute();

            return $stmt->fetchColumn();
        } catch(PDOException $e) {
            throw new Exception("فشل في جلب المستخدم: " . $e->getMessage());
        }
    }

    public function getAllNotificationsForUser($userID) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE userID = :userID";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            throw new Exception("فشل في جلب الإشعارات: " . $e->getMessage());
        }
    }

    public function deleteNotification($notificationID) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE notificationID = :notificationID";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':notificationID', $notificationID, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("فشل في حذف الإشعار: " . $e->getMessage());
        }
    }
    public function getAllNotifications($userID = null) {
        try {
            if ($userID) {
                // جلب الإشعارات العامة والخاصة بالمستخدم
                $query = "SELECT * FROM " . $this->table_name . " 
                          WHERE userID IS NULL OR userID = :userID
                          ORDER BY created_at DESC";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
            } else {
                // جلب جميع الإشعارات
                $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
                $stmt = $this->conn->prepare($query);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            throw new Exception("فشل في جلب الإشعارات: " . $e->getMessage());
        }
    }
}
