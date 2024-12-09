<?php
include_once 'Discount.php';

$resultMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userType = $_POST['userType'];
    $originalPrice = floatval($_POST['originalPrice']);
    $baseDiscount = floatval($_POST['discount']);

    if ($originalPrice > 0 && $baseDiscount >= 0 && $baseDiscount <= 100) {
        $database = new Database();
        $conn = $database->getConnection();
        
        $discount = new Discount($conn, $userType, $originalPrice, $baseDiscount);

        $finalDiscount = $discount->calculateFinalDiscount();
        $finalPrice = $discount->calculateFinalPrice();

        if ($discount->addDiscountToDatabase()) {
            $resultMessage = "تم إضافة الخصم بنجاح. المبلغ النهائي بعد الخصم ($finalDiscount%): " . number_format($finalPrice, 2) . " دينار";
        } else {
            $resultMessage = "فشل في إضافة الخصم إلى قاعدة البيانات.";
        }
    } else {
        $resultMessage = "يرجى إدخال بيانات صحيحة.";
    }
}
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تطبيق الخصومات</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            direction: rtl;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #4CAF50;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        select, input, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }

        .result {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>تطبيق الخصومات</h1>
        <form method="POST" action="">
            <label for="userType">نوع المستخدم:</label>
            <select id="userType" name="userType" required>
                <option value="normal">مستخدم عادي</option>
                <option value="vip">مستخدم VIP</option>
                <option value="student">طالب</option>
                <option value="military">عسكري</option>
                <option value="teacher">معلم</option>
                <option value="elderly">كبار السن</option>
            </select>

            <label for="originalPrice">المبلغ الأصلي:</label>
            <input type="number" id="originalPrice" name="originalPrice" placeholder="أدخل المبلغ" required>

            <label for="discount">الخصم الأساسي (%):</label>
            <input type="number" id="discount" name="discount" placeholder="أدخل نسبة الخصم" required>

            <button type="submit">تطبيق الخصم</button>
        </form>

        <?php if (!empty($resultMessage)): ?>
            <div class="result"><?php echo $resultMessage; ?></div>
        <?php endif; ?>
    </div>
</body>
</html>