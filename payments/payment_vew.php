<?php 
//include "booking/ticket_viwe.php";
include "payment.php";

// تهيئة الاتصال بقاعدة البيانات
$database = new Database();
$conn = $database->getConnection();

// إنشاء كائن الدفع
$payment = new Payment($conn);

// معالجة عملية تقديم الدفع
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $ticketID = 1; 
        $amount = 230;
        $paymentType = $_POST['payment_method'];
        
        $payment->setPaymentDetails($ticketID, $amount, $paymentType);
        $payment->addPayment();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام الدفع - BTS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            direction: rtl;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .payment-form {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #45a049;
        }

        .payment-history {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 14px;
        }

        .status-success {
            background-color: #d4edda;
            color: #155724;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
        }

        .payment-methods {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .payment-method {
            flex: 1;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .payment-method:hover {
            border-color: #4CAF50;
        }

        .payment-method.selected {
            border-color: #4CAF50;
            background-color: #f0f8f0;
        }

        @media (max-width: 768px) {
            .payment-methods {
                flex-direction: column;
            }

            .payment-method {
                margin-bottom: 10px;
            }

            table {
                display: block;
                overflow-x: auto;
            }
        }

        .summary-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
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
    <div class="container">
        <h1>نظام الدفع</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- نموذج الدفع -->
        <div class="payment-form">
            <h2>إجراء عملية دفع جديدة</h2>
            <div class="summary-box">
                <div class="summary-item">
                    <span>قيمة التذكرة:</span>
                    <span>200 دينار </span>
                </div>
                <div class="summary-item total">
                    <span>الإجمالي:</span>
                    <span>230 دينار </span>
                </div>
            </div>
            <form method="POST" action="">
                <div class="payment-methods">
                    <label>
                        <input type="radio" name="payment_method" value="Sadad" required>
                        <span>سداد</span>
                    </label>
                    <label>
                        <input type="radio" name="payment_method" value="MobiCash" required>
                        <span>موبي كاش</span>
                    </label>
                    <label>
                        <input type="radio" name="payment_method" value="LocalCard" required>
                        <span>بطاقة محلية</span>
                    </label>
                </div>
                <button type="submit" class="btn">إتمام عملية الدفع</button>
            </form>
        </div>

    </div>
</body>
</html>