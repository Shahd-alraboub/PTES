<?php
session_start();
include '../includes/Database.php';
include '../classes/notifiction refactoring.php'; // استدعاء الكلاس

// التحقق من تسجيل دخول المستخدم
if (!isset($_SESSION['userID'])) {
    header('Location: ../auth/login.php');
    exit;
}

// جلب userID من الجلسة
$currentUserID = $_SESSION['userID'];

// تهيئة الاتصال بقاعدة البيانات
$database = new Database();
$db = $database->getConnection();

// إنشاء كائن NotificationManager
$notificationManager = new NotificationManager($db);

// إعداد Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// جلب الإشعارات
$notifications = $notificationManager->getUserNotifications($currentUserID, $limit, $offset);
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
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        .pagination a {
            margin: 0 5px;
            text-decoration: none;
            color: #007BFF;
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
                        <p><?php echo htmlspecialchars($notification['message'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <span class="notification-date"><?php echo htmlspecialchars($notification['created_at'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <!-- Pagination Links -->
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>">السابق</a>
                <?php endif; ?>
                <a href="?page=<?php echo $page + 1; ?>">التالي</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
