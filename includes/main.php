<?php
// Database Connection
include_once 'Database.php';
//include_once 'event_viwe.php';

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Fetch events from database
$query = "SELECT * FROM Events ORDER BY date ASC";
$stmt = $db->prepare($query);
$stmt->execute();
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام حجز الفعاليات</title>
    <style>
* {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f5f5;
        }

        .header {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 1rem;
        }

        .nav {
            background-color: #34495e;
            padding: 1rem;
            text-align: center;
        }

        .nav a {
            color: white;
            text-decoration: none;
            margin: 0 1rem;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .nav a:hover {
            background-color: #2c3e50;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        h2 {
            text-align: center;
            margin-bottom: 2rem;
            color: #2c3e50;
        }

        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .event-card {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .event-card:hover {
            transform: translateY(-5px);
        }

        .event-image {
            background-color: #3498db;
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .event-details {
            padding: 1.5rem;
        }

        .event-title {
            margin-bottom: 1rem;
            color: #2c3e50;
        }

        .vip-badge {
            background-color: #e74c3c;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            margin-left: 0.5rem;
        }

        .event-info p {
            margin: 0.5rem 0;
            color: #666;
        }

        .event-price {
            font-size: 1.2rem;
            font-weight: bold;
            color: #e74c3c;
            margin: 1rem 0;
        }

        .btn {
            display: block;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            padding: 0.8rem;
            text-align: center;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        @media (max-width: 768px) {
            .nav {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }

            .nav a {
                display: block;
                margin: 0.2rem 0;
            }

            .events-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>نظام حجز الفعاليات</h1>
    </header>

    <nav class="nav">
        <a href="main.php">الرئيسية</a>
        <a href="../events/event_viwe.php">الفعاليات</a>
        <a href="../booking/ticket_viwe.php">تذاكري</a>
        <a href="../view_notifications.php">الاشعارات</a>
        <a href="../viwo_gif.php">الهدايا</a>
        <a href="../auth/login.php">تسجيل دخول</a>
        <a href="../auth/logout.php">تسجيل خروج</a>
    </nav>

<div class="container">
    <h2>الفعاليات المتاحة</h2>
    <div class="events-grid">
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <div class="event-card">
                <div class="event-image">
                    <h3 class="event-title"><?= htmlspecialchars($row['title']); ?></h3>
                </div>
                <div class="event-details">
                    <div class="event-info">
                        <p>التاريخ: <?= htmlspecialchars($row['date']); ?></p>
                        <p>الموقع: <?= htmlspecialchars($row['location']); ?></p>
                        <p>عدد المقاعد العادية: <?= htmlspecialchars($row['regularSeats']); ?></p>
                        <p>عدد مقاعد VIP: <?= htmlspecialchars($row['vipSeats']); ?></p>
                        <p class="event-price">السعر العادي: <?= htmlspecialchars($row['regularPrice']); ?> دينار</p>
                        <p class="event-price">السعر VIP: <?= htmlspecialchars($row['vipPrice']); ?> دينار</p>
                    </div>
                    <a href="../booking/booking_event.php?id=<?php echo htmlspecialchars($row['eventID']); ?>" class="btn">احجز الآن</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>
