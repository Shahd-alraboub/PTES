<?php
session_start();
session_destroy();
header("Location:../includes/main.php");
exit();
?>