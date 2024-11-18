<?php
include_once '../includes/Database.php';  // ملف الاتصال بقاعدة البيانات
include_once '../events/Event.php';      // تضمين كلاس Event

// إنشاء اتصال بقاعدة البيانات
$database = new Database();
$conn = $database->getConnection();

// استرجاع الـ eventID من الرابط
$eventID = $_GET['eventID'] ?? null; // الحصول على الـ eventID من الرابط

if (!$eventID) {
    die("الحدث غير موجود.");
}

// استرجاع بيانات الحدث
$event = new Event($conn);
$eventData = $event->getEventById($eventID);

if (!$eventData) {
    die("الحدث غير موجود.");
}

// إذا كانت طريقة الطلب POST، نقوم بتحديث البيانات
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // تحديث بيانات الحدث
    $event->eventID = $_POST['eventID'];
    $event->date = $_POST['date'];
    $event->title = $_POST['title'];
    $event->location = $_POST['location'];
    $event->regularSeats = $_POST['regularSeats'];
    $event->vipSeats = $_POST['vipSeats'];
    $event->regularPrice = $_POST['regularPrice'];
    $event->vipPrice = $_POST['vipPrice'];

    if ($event->updateEvent()) {
        echo "تم تحديث الحدث بنجاح!";
    } else {
        echo "حدث خطأ أثناء تحديث الحدث.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تعديل الحدث</title>
    <style>
        /* تنسيق الصفحة */
        body {
            font-family: Arial, sans-serif;
            direction: rtl;
            margin: 20px;
        }
        .container {
            width: 50%;
            margin: 0 auto;
        }
        .event-form input, .event-form select {
            padding: 10px;
            margin: 5px;
            width: 100%;
            max-width: 300px;
        }
        .button {
            padding: 10px 20px;
            margin: 5px;
            cursor: pointer;
        }
        .update-button { background-color: #2196F3; color: white; }
    </style>
</head>
<body>

<div class="container">
    <h2>تعديل الحدث</h2>

    <form method="POST">
        <!-- حقل مخفي لـ eventID -->
        <input type="hidden" name="eventID" value="<?= htmlspecialchars($eventData['eventID']) ?>">

        <input type="date" name="date" required placeholder="تاريخ الحدث" value="<?= htmlspecialchars($eventData['date']) ?>">
        <input type="text" name="title" required placeholder="عنوان الحدث" value="<?= htmlspecialchars($eventData['title']) ?>">
        <input type="text" name="location" required placeholder="موقع الحدث" value="<?= htmlspecialchars($eventData['location']) ?>">
        <input type="number" name="regularSeats" required placeholder="عدد المقاعد العادية" value="<?= htmlspecialchars($eventData['regularSeats']) ?>">
        <input type="number" name="vipSeats" required placeholder="عدد المقاعد VIP" value="<?= htmlspecialchars($eventData['vipSeats']) ?>">
        <input type="number" step="0.01" name="regularPrice" required placeholder="سعر التذكرة العادية" value="<?= htmlspecialchars($eventData['regularPrice']) ?>">
        <input type="number" step="0.01" name="vipPrice" required placeholder="سعر التذكرة VIP" value="<?= htmlspecialchars($eventData['vipPrice']) ?>">

        <button type="submit" name="update_event" class="button update-button">تحديث الحدث</button>
    </form>
</div>

</body>
</html>
