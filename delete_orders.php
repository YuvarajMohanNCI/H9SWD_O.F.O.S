<?php
include("connection/connect.php");
error_reporting(0);
session_start();

try {
    
    if (isset($_GET['order_del'])) {
        $orderDel = mysqli_real_escape_string($db, $_GET['order_del']);

        $stmt = $db->prepare("DELETE FROM users_orders WHERE o_id = ?");
        if ($stmt === false) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }

        $stmt->bind_param("s", $orderDel);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        header("Location: your_orders.php");
        exit();
    } else {
        header("Location: your_orders.php?error=noidprovided");
        exit();
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    header("Location: error_page.php");
    exit();
}
?>
