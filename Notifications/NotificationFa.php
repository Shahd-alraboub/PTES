<?php
require_once "Notificationn.php";


class NotificationFactory {
    public static function createNotification($db) {
        return new Notification($db);
    }
}
