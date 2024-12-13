<?php
include "../includes/Database.php";
class Discount
{
    private $conn;
    private $userType;
    private $originalPrice;
    private $baseDiscount;
    private $discountRate;

    public function __construct($conn, $userType, $originalPrice, $baseDiscount, $discountRate = 0)
    {
        $this->conn = $conn;
        $this->userType = $userType;
        $this->originalPrice = $originalPrice;
        $this->baseDiscount = $baseDiscount;
        $this->discountRate = $discountRate;
    }

    public function calculateFinalDiscount()
    {
        return $this->baseDiscount + $this->discountRate;
    }

    public function calculateFinalPrice()
    {
        $totalDiscount = $this->calculateFinalDiscount();
        return $this->originalPrice - ($this->originalPrice * $totalDiscount / 100);
    }

    public function addDiscountToDatabase()
    {
        $query = "INSERT INTO discounts (userType, originalPrice, baseDiscount, discountRate, expiryDate) 
                  VALUES (:userType, :originalPrice, :baseDiscount, :discountRate, DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY))";
    
        // Prepare the statement
        $stmt = $this->conn->prepare($query);
    
        // Bind parameters using bindParam
        $stmt->bindParam(':userType', $this->userType);
        $stmt->bindParam(':originalPrice', $this->originalPrice);
        $stmt->bindParam(':baseDiscount', $this->baseDiscount);
        $stmt->bindParam(':discountRate', $this->discountRate);
    
        // Execute the statement and return the result
        return $stmt->execute();
    }
    
}
?>