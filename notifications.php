<?php
session_start();
include 'includes/Database.php';
include 'Notification.php';

// تهيئة الاتصال بقاعدة البيانات
$database = new Database();
$db = $database->getConnection();

// إنشاء كائن الإشعارات
$notification = new Notification($db);

// معالجة إرسال الإشعار
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_message'])) {
    try {
        $identifier = htmlspecialchars($_POST['user_identifier']);
        $message = htmlspecialchars($_POST['notification_message']);

        if (empty($message)) {
            throw new Exception("الرجاء إدخال نص الإشعار");
        }

        if (empty($identifier)) {
            // إرسال إشعار عام (لكل المستخدمين)
            $userID = null;
        } else {
            $userID = $notification->getUserIDByEmailOrID($identifier);

            if (!$userID) {
                throw new Exception("المستخدم غير موجود");
            }
        }

        $notification->addNotification($userID, $message);
        $_SESSION['success'] = "تم إرسال الإشعار بنجاح!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// معالجة حذف الإشعار
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notification'])) {
    try {
        $notificationID = (int) $_POST['delete_notification'];
        $notification->deleteNotification($notificationID);
        $_SESSION['success'] = "تم حذف الإشعار بنجاح!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// جلب الإشعارات
$currentUserID = isset($_SESSION['userID']) ? $_SESSION['userID'] : null; // تحديد المستخدم الحالي
$notifications = $notification->getAllNotifications($currentUserID);

// استخراج رسائل النجاح والخطأ من الجلسة
$success = isset($_SESSION['success']) ? $_SESSION['success'] : null;
$error = isset($_SESSION['error']) ? $_SESSION['error'] : null;
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام الإشعارات</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
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
        h1, h2 {
            color: #007BFF;
        }
        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .notification-form, .notification-list {
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button.btn {
            background-color: #007BFF;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button.btn:hover {
            background-color: #0056b3;
        }
        button.btn-danger {
            background-color: #dc3545;
        }
        button.btn-danger:hover {
            background-color: #c82333;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            padding: 10px;
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
        <h1>نظام الإشعارات</h1>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="notification-form">
            <h2>إرسال إشعار جديد</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="user_identifier">ID المستخدم أو البريد الإلكتروني (اتركه فارغاً لإرسال إشعار عام):</label>
                    <input type="text" id="user_identifier" name="user_identifier">
                </div>
                <div class="form-group">
                    <label for="notification_message">رسالة الإشعار:</label>
                    <textarea id="notification_message" name="notification_message" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn">إرسال</button>
            </form>
        </div>

        <div class="notification-list">
            <h2>الإشعارات السابقة</h2>
            <?php if (empty($notifications)): ?>
                <p>لا توجد إشعارات حالياً</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($notifications as $note): ?>
                        <li>
                            <span class="notification-message"><?php echo htmlspecialchars($note['message']); ?></span>
                            <br>
                            <span class="notification-date"><?php echo htmlspecialchars($note['created_at']); ?></span>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="delete_notification" value="<?php echo htmlspecialchars($note['notificationID']); ?>">
                                <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
