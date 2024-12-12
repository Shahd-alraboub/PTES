<?php
session_start();
include 'includes/Database.php'; // تأكد من وجود ملف إعداد الاتصال بقاعدة البيانات

// التحقق من تسجيل دخول المستخدم
if (!isset($_SESSION['userID'])) {
    // إعادة التوجيه إلى صفحة تسجيل الدخول إذا لم يتم تسجيل الدخول
    header('Location:../auth/login.php');
    exit;
}

// جلب userID من الجلسة
$currentUserID = $_SESSION['userID'];

// تهيئة الاتصال بقاعدة البيانات
$database = new Database();
$db = $database->getConnection();

// جلب الإشعارات الخاصة بالمستخدم الحالي
$query = "SELECT notificationID, message, created_at FROM notifications WHERE userID = :userID ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':userID', $currentUserID, PDO::PARAM_INT);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إشعارات المستخدم</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #007BFF;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            padding: 15px;
            border: 1px solid #ddd;
            margin-bottom: 10px;
            border-radius: 4px;
            background-color: #f9f9f9;
        }
        .notification-date {
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>إشعاراتي</h1>
        <?php if (empty($notifications)): ?>
            <p>لا توجد إشعارات حاليًا.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($notifications as $notification): ?>
                    <li>
                        <p><?php echo htmlspecialchars($notification['message']); ?></p>
                        <span class="notification-date"><?php echo htmlspecialchars($notification['created_at']); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>
