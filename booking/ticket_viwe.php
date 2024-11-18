<?php
// تضمين ملف الاتصال بقاعدة البيانات
include_once '../includes/Database.php';
include_once 'Ticket.php';  // تضمين كلاس Ticket

// إنشاء الاتصال بقاعدة البيانات
$database = new Database();
$conn = $database->getConnection();
session_start();

// التحقق من وجود userID في الجلسة
if (!isset($_SESSION['userID'])) {
    echo "يرجى تسجيل الدخول أولاً.";
    exit; // توقف تنفيذ الكود إذا لم يكن هناك userID في الجلسة
}

$userID = $_SESSION['userID'];

// استرجاع جميع التذاكر الخاصة بالمستخدم
$tickets = Ticket::getUserTickets($userID, $conn);

// التحقق من طلب POST لإلغاء التذكرة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_ticket_id'])) {
    $cancelTicketID = $_POST['cancel_ticket_id'];

    // إنشاء كائن تذكرة بناءً على الـ ticketID
    $ticketToCancel = new Ticket($cancelTicketID, $userID, null, null, null, null, null, null, $conn);

    
    // إلغاء التذكرة
    $message = $ticketToCancel->cancelTicket($conn);  // تأكد من تمرير الاتصال
    echo "<div class='message'>$message</div>";
}

?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>عرض التذاكر</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            direction: rtl;
            background-color: #f9f9f9;
            color: #333;
        }
        .ticket-container {
            width: 80%;
            margin: 20px auto;
            padding: 15px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .ticket-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .ticket-info {
            margin-bottom: 5px;
        }
        .cancel-button {
            color: #ff0000;
            font-size: 14px;
            background-color: transparent;
            border: none;
            cursor: pointer;
        }
        .cancel-button:hover {
            text-decoration: underline;
        }
        .message {
            padding: 10px;
            background-color: #e0ffe0;
            color: #006400;
            border: 1px solid #b2d8b2;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .print-button {
            color: #0000ff;
            font-size: 14px;
            background-color: transparent;
            border: none;
            cursor: pointer;
        }
        .print-button:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h2>تذاكري</h2>

   
    <?php 
    if (empty($tickets)) {
        echo "لا توجد تذاكر للاستخدام.";
    } else {
        foreach ($tickets as $ticket) {
            echo $ticket->displayTicketDetails(); // عرض تفاصيل التذكرة باستخدام دالة displayTicketDetails
        }
    }
    ?>
</body>
</html>
