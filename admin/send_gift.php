<?php
// send_gift.php
session_start();

require_once '../gift.php';
$db = new Database();
$conn = $db->getConnection();
$gift = new Gift($conn);

// Get logged in user ID
$userID = $_SESSION['userID'] ?? null;

// Check if user is logged in
// if (!$userID) {
//     die('<div class="message error">يرجى تسجيل الدخول أولاً</div>');
// }

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiverID = $_POST['receiver'] ?? 0;
    $eventID = $_POST['event'] ?? 0;
    
    if ($receiverID && $eventID) {
        // Call addGiftTicket with the proper senderID
        $result = $gift->addGiftTicket($userID, $receiverID, $eventID);
        
        if ($result['status']) {
            echo '<div class="message success">' . $result['message'] . '</div>';
        } else {
            echo '<div class="message error">' . $result['message'] . '</div>';
        }
    } else {
        echo '<div class="message error">يرجى اختيار المستلم والحدث.</div>';
    }
}
// الحصول على قائمة المستخدمين
$query = "SELECT userID, name FROM users WHERE userID != :userID";
$stmt = $conn->prepare($query);
$stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// الحصول على قائمة الأحداث المتاحة
$query = "SELECT * FROM events WHERE date > NOW() ORDER BY date";
$events = $conn->query($query);

// تعديل دالة إضافة الهدية لتقبل المعاملات المتوقعة
function addGiftTicket($userID, $receiverID, $eventID, $conn) {
    $gift = new Gift($conn);
    return $gift->addGiftTicket($userID, $receiverID, $eventID, date('Y-m-d H:i:s'));
}

// تعديل دالة التحقق من صلاحية الهدية
function validateGift($eventID, $receiverID, $userID) {
    $gift = new Gift($GLOBALS['conn']);
    return $gift->validateGift($eventID, $receiverID, $userID);
}
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إرسال هدية تذكرة</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            direction: rtl;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        select, input[type="submit"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            padding: 10px;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .message {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>إرسال هدية تذكرة</h1>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $receiverID = $_POST['receiver'] ?? 0;
            $eventID = $_POST['event'] ?? 0;
            
            if ($receiverID && $eventID) {
                // التحقق من صلاحية الهدية
                $validation = validateGift($eventID, $receiverID, $userID);
                
                if ($validation['status']) {
                    $result = addGiftTicket($userID, $receiverID, $eventID, $conn);
                    if ($result['status']) {
                        echo '<div class="message success">' . $result['message'] . '</div>';
                    } else {
                        echo '<div class="message error">' . $result['message'] . '</div>';
                    }
                } else {
                    echo '<div class="message error">' . $validation['message'] . '</div>';
                }
            } else {
                echo '<div class="message error">يرجى اختيار المستلم والحدث.</div>';
            }
        }
        ?>

        <form method="POST">
            <div class="form-group">
                <label>اختر المستلم:</label>
                <select name="receiver" required>
                    <option value="">-- اختر المستلم --</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['userID']; ?>">
                            <?php echo htmlspecialchars($user['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>اختر الحدث:</label>
                <select name="event" required>
                    <option value="">-- اختر الحدث --</option>
                    <?php while ($event = $events->fetch(PDO::FETCH_ASSOC)): ?>
                        <option value="<?php echo $event['eventID']; ?>">
                            <?php echo htmlspecialchars($event['title']); ?>
                            (<?php echo date('Y-m-d', strtotime($event['date'])); ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <input type="submit" value="إرسال الهدية">
        </form>
    </div>
</body>
</html>