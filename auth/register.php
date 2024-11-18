<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include_once '../includes/Database.php';
    
    $database = new Database();
    $db = $database->getConnection();
    
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $userType = $_POST['userType'];
    $vipMembership = isset($_POST['vipMembership']) ? 1 : 0;

    if ($password !== $confirmPassword) {
        $error = "كلمتا المرور غير متطابقتين";
    } else {
        $checkEmailQuery = "SELECT * FROM Users WHERE email = ?";
        $stmt = $db->prepare($checkEmailQuery);
        $stmt->bindParam(1, $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $error = "البريد الإلكتروني مسجل مسبقًا";
        } else {
            $query = "INSERT INTO Users (name, email, password, phone, address, userType, vipMembership) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bindParam(1, $name);
            $stmt->bindParam(2, $email);
            $stmt->bindParam(3, $hashedPassword);
            $stmt->bindParam(4, $phone);
            $stmt->bindParam(5, $address);
            $stmt->bindParam(6, $userType);
            $stmt->bindParam(7, $vipMembership);

            if ($stmt->execute()) {
                $_SESSION['success'] = "تم التسجيل بنجاح. يمكنك الآن تسجيل الدخول";
                header("Location: login.php");
                exit();
            } else {
                $error = "حدث خطأ أثناء التسجيل. حاول مرة أخرى";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>التسجيل</title>
    <style>
        * {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            box-sizing: border-box;
        }
        body {
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .register-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #34495e;
        }
        input[type="text"], input[type="email"], input[type="password"], select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-top: 5px;
        }
        .register-btn {
            background-color: #3498db;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }
        .register-btn:hover {
            background-color: #2980b9;
        }
        .error-message {
            color: #e74c3c;
            background-color: #ffe6e6;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>التسجيل</h2>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="name">الاسم الكامل:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">البريد الإلكتروني:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone">رقم الهاتف:</label>
                <input type="text" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="address">العنوان:</label>
                <input type="text" id="address" name="address" required>
            </div>
            <div class="form-group">
                <label for="userType">نوع المستخدم:</label>
                <select id="userType" name="userType" required>
                    <option value="student">طالب</option>
                    <option value="military">عسكري</option>
                    <option value="teacher">معلم</option>
                    <option value="the_elderly">كبير السن</option>
                </select>
            </div>
            <div class="form-group">
                <label for="password">كلمة المرور:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirmPassword">تأكيد كلمة المرور:</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required>
            </div>
            <button type="submit" class="register-btn">تسجيل</button>
        </form>

        <div class="login-link">
            <p>لديك حساب؟ <a href="login.php">سجل الدخول</a></p>
        </div>
    </div>
</body>
</html>
