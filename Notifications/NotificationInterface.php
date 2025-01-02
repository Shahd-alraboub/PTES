<?php
interface NotificationInterface {
    public function addNotification($userID, $message);
    public function getAllNotificationsForUser($userID);
    public function deleteNotification($notificationID);
    public function getUserIDByEmailOrID($identifier);
    public function getAllNotifications($userID = null);
}
