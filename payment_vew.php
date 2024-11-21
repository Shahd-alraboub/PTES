<?php
    include_once 'payment';
    include_once 'include/Database.php';


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
        
        <!-- نموذج الدفع -->
        <div class="payment-form">
            <h2>إجراء عملية دفع جديدة</h2>
            
            <div class="summary-box">
                <div class="summary-item">
                    <span>قيمة التذكرة:</span>
                    <span>200 ريال</span>
                </div>
                <div class="summary-item">
                    <span>الضريبة:</span>
                    <span>30 ريال</span>
                </div>
                <div class="summary-item total">
                    <span>الإجمالي:</span>
                    <span>230 ريال</span>
                </div>
            </div>

            <div class="payment-methods">
                <div class="payment-method" onclick="selectPaymentMethod(this, 'Sadad')">
                    <h3>سداد</h3>
                    <p>الدفع عبر نظام سداد</p>
                </div>
                <div class="payment-method" onclick="selectPaymentMethod(this, 'MobiCash')">
                    <h3>موبي كاش</h3>
                    <p>الدفع عبر موبي كاش</p>
                </div>
                <div class="payment-method" onclick="selectPaymentMethod(this, 'LocalCard')">
                    <h3>بطاقة محلية</h3>
                    <p>الدفع ببطاقة مدى</p>
                </div>
            </div>

            <form id="paymentForm">
                <div class="form-group">
                    <label>رقم البطاقة</label>
                    <input type="text" placeholder="xxxx xxxx xxxx xxxx" maxlength="19">
                </div>
                
                <div class="form-group">
                    <label>تاريخ الانتهاء</label>
                    <input type="text" placeholder="MM/YY" maxlength="5">
                </div>
                
                <div class="form-group">
                    <label>رمز الأمان CVV</label>
                    <input type="password" placeholder="***" maxlength="3">
                </div>

                <button type="submit" class="btn">إتمام عملية الدفع</button>
            </form>
        </div>

        <!-- سجل المدفوعات -->
        <div class="payment-history">
            <h2>سجل المدفوعات</h2>
            <table>
                <thead>
                    <tr>
                        <th>رقم العملية</th>
                        <th>التاريخ</th>
                        <th>المبلغ</th>
                        <th>طريقة الدفع</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#123456</td>
                        <td>2024-11-20</td>
                        <td>230 ريال</td>
                        <td>سداد</td>
                        <td><span class="status-badge status-success">تم الدفع</span></td>
                    </tr>
                    <tr>
                        <td>#123457</td>
                        <td>2024-11-19</td>
                        <td>180 ريال</td>
                        <td>موبي كاش</td>
                        <td><span class="status-badge status-pending">قيد المعالجة</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function selectPaymentMethod(element, method) {
            // إزالة التحديد من جميع الطرق
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('selected');
            });
            // تحديد الطريقة المختارة
            element.classList.add('selected');
        }

        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // هنا يمكن إضافة كود معالجة الدفع
            alert('جاري معالجة عملية الدفع...');
        });

        // تنسيق رقم البطاقة
        document.querySelector('input[placeholder="xxxx xxxx xxxx xxxx"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            value = value.replace(/\D/g, '');
            let newValue = '';
            for(let i = 0; i < value.length; i++) {
                if(i > 0 && i % 4 === 0) {
                    newValue += ' ';
                }
                newValue += value[i];
            }
            e.target.value = newValue;
        });

        // تنسيق تاريخ الانتهاء
        document.querySelector('input[placeholder="MM/YY"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if(value.length >= 2) {
                value = value.substring(0,2) + '/' + value.substring(2);
            }
            e.target.value = value;
        });
    </script>
</body>
</html>