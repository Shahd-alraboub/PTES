<?php 

include_once '../includes/Database.php';  // ملف الاتصال بقاعدة البيانات
include_once 'event.php';   
// إنشاء الاتصال بقاعدة البيانات
$database = new Database();
$db = $database->getConnection(); // الحصول على الاتصال



?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة الفعاليات</title>
    <link rel="stylesheet" href="../includes/assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <header class="main-header">
            <h1>نظام إدارة الفعاليات</h1>
            <nav class="nav">
                <a href="../includes/main.php">الرئيسية</a>
                <a href="event_viwe.php">الفعاليات</a>   
                <a href="../booking/ticket_viwe.php">تذاكري</a>
                <a href="../notifications.php">الاشعارات</a>
            </nav>
        </header>

        <section class="search-section">
            <div class="search-container">
                <form method="POST" action="">
                    <input type="text" name="searchTerm" placeholder="ابحث عن فعالية..." value="<?php echo isset($_POST['searchTerm']) ? $_POST['searchTerm'] : ''; ?>">
                    <button type="submit">بحث</button>
                </form>
            </div>
        </section>

        <section class="filter-section">
            <form method="POST" action="">
                <button type="submit" name="filter" value="all" class="filter-btn <?php echo (!isset($_POST['filter']) || $_POST['filter'] == 'all') ? 'active' : ''; ?>">جميع الفعاليات</button>
                <button type="submit" name="filter" value="today" class="filter-btn <?php echo (isset($_POST['filter']) && $_POST['filter'] == 'today') ? 'active' : ''; ?>">فعاليات اليوم</button>
                <button type="submit" name="filter" value="upcoming" class="filter-btn <?php echo (isset($_POST['filter']) && $_POST['filter'] == 'upcoming') ? 'active' : ''; ?>">الفعاليات القادمة</button>
            </form>
        </section>

        <?php
        // استعلام البحث وتصفيته
        $query = "SELECT * FROM events WHERE 1";
        $params = [];

        if (isset($_POST['searchTerm']) && !empty($_POST['searchTerm'])) {
            $query .= " AND title LIKE :searchTerm";
            $params[':searchTerm'] = "%" . $_POST['searchTerm'] . "%";
        }

        if (isset($_POST['filter'])) {
            $filter = $_POST['filter'];
            if ($filter == 'vip') {
                $query .= " AND vipSeats > 0";
            } elseif ($filter == 'today') {
                $query .= " AND DATE(date) = CURDATE()";
            } elseif ($filter == 'upcoming') {
                $query .= " AND date > CURDATE()";
            }
        }

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        ?>

        <?php if ($stmt->rowCount() > 0): ?>
            
            <div class="events-grid">
                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                    extract($row);
                    $event = new Event($db);
                    $event->eventID = $eventID;
                    $remainingRegularSeats = $regularSeats; // المقاعد العادية المتبقية
                    $remainingVIPSeats = $vipSeats; // المقاعد المميزة المتبقية
                    ?>
                    <div class="event-card" data-vip="<?php echo ($vipSeats > 0) ? 'true' : 'false'; ?>">
                        <h2 class="event-title"><?php echo $title; ?></h2>
                        
                        <div class="event-info">
                            <p class="date"><i class="fas fa-calendar"></i> التاريخ: <?php echo date('Y/m/d', strtotime($date)); ?></p>
                            <p class="location"><i class="fas fa-map-marker-alt"></i> المكان: <?php echo $location; ?></p>
                            <p class="seats">المقاعد العادية المتبقية: <?php echo $remainingRegularSeats; ?></p>
                            <p class="seats">مقاعد VIP المتبقية: <?php echo $remainingVIPSeats; ?></p>
                        </div>

                        <div class="event-price">
                            <p>سعر التذكرة العادية: <?php echo number_format($regularPrice, 2); ?> دينار </p>
                            <p> VIPسعر التذكرة  : <?php echo number_format($vipPrice, 2); ?>دينار </p>
                        </div>

                        <div class="event-actions">
                            <?php if($remainingRegularSeats > 0 || $remainingVIPSeats > 0): ?>
                                <a href="<?php echo htmlspecialchars('../booking/booking_event.php?id=' . $eventID); ?>" class="book-btn">احجز الآن</a>
                            <?php else: ?>
                                <button class="waitlist-btn" onclick="joinWaitlist(<?php echo $eventID; ?>)">انضم لقائمة الانتظار</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-events">
                <p>لا توجد فعاليات متاحة حالياً</p>
                <a href="#subscribe" class="notify-btn">أبلغني عند إضافة فعاليات جديدة</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
