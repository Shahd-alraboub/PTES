<?php
include_once '../includes/Database.php';  // ملف الاتصال بقاعدة البيانات
include_once '../events/Event.php';     // تضمين كلاس Event

// إنشاء اتصال بقاعدة البيانات
$database = new Database();
$conn = $database->getConnection();

// إذا كانت طريقة الطلب POST، نعالج الإضافة أو التحديث أو الحذف
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event = new Event($conn);

    // إضافة حدث جديد
    if (isset($_POST['add_event'])) {
        $event->date = $_POST['date'];
        $event->title = $_POST['title'];
        $event->location = $_POST['location'];
        $event->regularSeats = $_POST['regularSeats'];
        $event->vipSeats = $_POST['vipSeats'];
        $event->regularPrice = $_POST['regularPrice'];
        $event->vipPrice = $_POST['vipPrice'];

        if ($event->addEvent()) {
            echo "تم إضافة الحدث بنجاح!";
        } else {
            echo "حدث خطأ أثناء إضافة الحدث.";
        }
    }

    // تحديث حدث
    if (isset($_POST['update_event'])) {
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

    // حذف حدث
    if (isset($_POST['delete_event'])) {
        $event->eventID = $_POST['eventID'];
        if ($event->deleteEvent()) {
            echo "تم حذف الحدث بنجاح!";
        } else {
            echo "حدث خطأ أثناء حذف الحدث.";
        }
    }
}

// استرجاع جميع الأحداث
$eventObj = new Event($conn);
$events = $eventObj->getEvents();
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم الأدمن</title>
    <style>
        /* تنسيق الصفحة */
        body {
            font-family: Arial, sans-serif;
            direction: rtl;
            margin: 20px;
        }
        .container {
            width: 80%;
            margin: 0 auto;
        }
        .event-form, .event-table {
            margin-bottom: 20px;
        }
        .event-form input, .event-form select {
            padding: 10px;
            margin: 5px;
            width: 100%;
            max-width: 300px;
        }
        .event-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .event-table table, .event-table th, .event-table td {
            border: 1px solid #ddd;
        }
        .event-table th, .event-table td {
            padding: 8px;
            text-align: center;
        }
        .button {
            padding: 10px 20px;
            margin: 5px;
            cursor: pointer;
        }
        .add-button { background-color: #4CAF50; color: white; }
        .update-button { background-color: #2196F3; color: white; }
        .delete-button { background-color: #f44336; color: white; }
    </style>
</head>
<body>

<div class="container">
    <h2>لوحة تحكم الأدمن</h2>

    <!-- إضافة حدث -->
    <div class="event-form">
        <h3>إضافة حدث جديد</h3>
        <form method="POST">
            <input type="date" name="date" required placeholder="تاريخ الحدث">
            <input type="text" name="title" required placeholder="عنوان الحدث">
            <input type="text" name="location" required placeholder="موقع الحدث">
            <input type="number" name="regularSeats" required placeholder="عدد المقاعد العادية">
            <input type="number" name="vipSeats" required placeholder="عدد المقاعد VIP">
            <input type="number" step="0.01" name="regularPrice" required placeholder="سعر التذكرة العادية">
            <input type="number" step="0.01" name="vipPrice" required placeholder="سعر التذكرة VIP">
            <button type="submit" name="add_event" class="button add-button">إضافة الحدث</button>
        </form>
    </div>

    <div class="event-table">
    <h3>إدارة الأحداث</h3>
    <table>
        <tr>
            <th>رقم الحدث</th>
            <th>التاريخ</th>
            <th>العنوان</th>
            <th>الموقع</th>
            <th>الإجراءات</th>
        </tr>
        <?php foreach ($events as $event): ?>
        <tr>
            <td><?= htmlspecialchars($event['eventID']) ?></td>
            <td><?= htmlspecialchars($event['date']) ?></td>
            <td><?= htmlspecialchars($event['title']) ?></td>
            <td><?= htmlspecialchars($event['location']) ?></td>
            <td>
    <!-- نموذج لحذف الحدث -->
    <form method="POST" style="display:inline;">
        <input type="hidden" name="eventID" value="<?= htmlspecialchars($event['eventID']) ?>">
        <button type="submit" name="delete_event" class="button delete-button">حذف</button>
    </form>
    <!-- رابط لتعديل الحدث -->
    <a href="editEvent.php?eventID=<?= htmlspecialchars($event['eventID']) ?>" class="button update-button">تعديل</a>
</td>

        </tr>
        <?php endforeach; ?>
    </table>
</div>
</div>

</body>
</html>
