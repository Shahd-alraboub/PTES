<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include_once '../includes/Database.php';
    
    $database = new Database();
    $db = $database->getConnection();
    
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $query = "SELECT userID, name, password FROM Users WHERE email = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['userID'] = $user['userID'];
            $_SESSION['name'] = $user['name'];

            // إعادة التوجيه بعد تسجيل الدخول الناجح
            header("Location:../events/event_viwe.php");
            exit();
        } else {
            $error = "كلمة المرور غير صحيحة";
        }
    } else {
        $error = "البريد الإلكتروني أو كلمة المرور غير صحيحة";
    }
}
?>


<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Cairo', sans-serif;
        }
        
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .login-btn {
            background: #007bff;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            width: 100%;
            cursor: pointer;
        }

        .error-message {
            color: #dc3545;
            margin-bottom: 20px;
            padding: 10px;
            background: #ffe6e6;
            border-radius: 4px;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>تسجيل الدخول</h2>
        
        <?php if(isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">البريد الإلكتروني:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">كلمة المرور:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="login-btn">دخول</button>
        </form>

        <div class="register-link">
            <p>ليس لديك حساب؟ <a href="register.php">سجل الآن</a></p>
        </div>
    </div>
</body>
</html>