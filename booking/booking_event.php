<?php
session_start();
include_once '../includes/Database.php';
include_once '../events/Event.php';   
include_once 'booking.php';
include_once "../payment.php";

$userID = Booking::checkLogin();
$database = new Database();
$db = $database->getConnection();

// Initialize Payment object
$payment = new Payment($db);

if (!isset($_GET['id'])) {
    die('خطأ: لم يتم تحديد الفعالية');
}

// Fetch event details
$query = "SELECT e.*, 
          (SELECT COUNT(*) FROM Tickets WHERE eventID = e.eventID) as sold_tickets,
          e.regularSeats, e.vipSeats, e.regularPrice, e.vipPrice,
          (SELECT COUNT(*) FROM Tickets WHERE eventID = e.eventID AND isRefundable = 1) as refundable_tickets,
          (SELECT COUNT(*) FROM Tickets WHERE eventID = e.eventID AND isRefundable = 0) as non_refundable_tickets
          FROM Events e
          WHERE e.eventID = :eventID";

$stmt = $db->prepare($query);
$stmt->bindParam(":eventID", $_GET['id']);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    die('خطأ: الفعالية غير موجودة');
}

$eventDetails = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numberOfTickets = isset($_POST['numberOfTickets']) ? (int)$_POST['numberOfTickets'] : 0;
    $ticketType = isset($_POST['ticketType']) ? $_POST['ticketType'] : 'standard';
    $isRefundable = isset($_POST['isRefundable']) ? 1 : 0;
    $paymentMethod = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';

    $basePrice = ($ticketType === 'premium') ? $eventDetails['vipPrice'] : $eventDetails['regularPrice'];
    $total = $basePrice;

    if ($isRefundable) {
        $total *= 1.1;
    }

    $discount = ($numberOfTickets >= 5) ? 10 : 0;
    $finalPrice = $total * (1 - ($discount / 100)) * $numberOfTickets;

    $booking = new Booking($db);
    $booking->eventID = $_GET['id'];
    $booking->userID = $userID;
    $booking->numberOfTickets = $numberOfTickets;
    $booking->ticketType = $ticketType;
    $booking->isRefundable = $isRefundable;
    $booking->totalAmount = $finalPrice;

    $availableSeats = ($ticketType === 'premium') ? $eventDetails['vipSeats'] : $eventDetails['regularSeats'];
    
    if ($numberOfTickets > $availableSeats) {
        $error = "عذراً، لا يوجد عدد كافٍ من المقاعد المتوفرة";
    } else {
        if ($booking->createBooking()) {
            $lastSeat = $booking->getLastSeatNumber($_GET['id'], $ticketType);
            $booking->addTickets($_GET['id'], $userID, $numberOfTickets, $ticketType, $isRefundable, $lastSeat);
            $booking->updateSeatAvailability($_GET['id'], $ticketType, $numberOfTickets);

            // Process payment
            try {
                $payment->setPaymentDetails($booking->getLastBookingId(), $finalPrice, $paymentMethod);
                if ($payment->addPayment()) {
                    $successMessage = "تم حجز التذاكر والدفع بنجاح! سيتم تحويلك إلى صفحة التذاكر";
                    echo "<script>
                            alert('$successMessage');
                            setTimeout(function() {
                                window.location.href = 'ticket_viwe.php';
                            }, 100);
                          </script>";
                    exit();
                }
            } catch (Exception $e) {
                $error = "حدث خطأ أثناء معالجة الدفع: " . $e->getMessage();
            }
        } else {
            $error = "حدث خطأ أثناء إنشاء الحجز";
        }
    }
}
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حجز ودفع فعالية - <?php echo htmlspecialchars($eventDetails['title']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Combined CSS styles from both files */
        body { 
            font-family: 'Cairo', sans-serif; 
            margin: 0; 
            padding: 20px; 
            background-color: #f5f5f5; 
        }
        .booking-container { 
            max-width: 800px; 
            margin: 0 auto; 
            background: white; 
            padding: 20px; 
            border-radius: 10px; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1); 
        }
        .form-group { 
            margin-bottom: 15px; 
        }
        .form-group label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: bold; 
        }
        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .payment-methods {
            display: flex;
            gap: 15px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        .payment-method {
            flex: 1;
            min-width: 150px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
        }
        .payment-method:hover {
            border-color: #007bff;
            background-color: #f8f9fa;
        }
        .submit-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        .submit-btn:hover {
            background: #0056b3;
        }
        .error-message {
            color: red;
            padding: 10px;
            background: #ffe6e6;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .summary-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .total {
            font-weight: bold;
            border-top: 2px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="booking-container">
        <h1>حجز ودفع فعالية</h1>
        
        <div class="event-details">
            <h2><?php echo htmlspecialchars($eventDetails['title']); ?></h2>
            <p>التاريخ: <?php echo date('Y/m/d', strtotime($eventDetails['date'])); ?></p>
            <p>المكان: <?php echo htmlspecialchars($eventDetails['location']); ?></p>
            <p>السعر الأساسي للتذكرة العادية: <?php echo number_format($eventDetails['regularPrice'], 2); ?> دينار</p>
            <p>السعر الأساسي للتذكرة VIP: <?php echo number_format($eventDetails['vipPrice'], 2); ?> دينار</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>نوع التذكرة:</label>
                <div class="radio-group">
                    <input type="radio" id="standard" name="ticketType" value="standard" checked>
                    <label for="standard">عادية</label>
                    <input type="radio" id="premium" name="ticketType" value="premium">
                    <label for="premium">VIP مميزة</label>
                </div>
            </div>

            <div class="form-group">
                <input type="checkbox" id="isRefundable" name="isRefundable">
                <label for="isRefundable">تذكرة قابلة للاسترداد (+10%)</label>
            </div>

            <div class="form-group">
                <label for="numberOfTickets">عدد التذاكر:</label>
                <input type="number" id="numberOfTickets" name="numberOfTickets" min="1" max="10" required class="form-control">
            </div>

            <div class="payment-methods">
                <label class="payment-method">
                    <input type="radio" name="payment_method" value="Sadad" required>
                    <span>سداد</span>
                </label>
                <label class="payment-method">
                    <input type="radio" name="payment_method" value="MobiCash" required>
                    <span>موبي كاش</span>
                </label>
                <label class="payment-method">
                    <input type="radio" name="payment_method" value="LocalCard" required>
                    <span>بطاقة محلية</span>
                </label>
            </div>

            <button type="submit" class="submit-btn">تأكيد الحجز والدفع</button>
        </form>
    </div>
</body>
</html>