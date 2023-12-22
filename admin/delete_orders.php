<?php
include("../connection/connect.php");
error_reporting(0);
session_start();

if (empty($_SESSION["adm_id"]) || (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 600))) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

if (isset($_GET['order_del'])) {
    $orderId = $_GET['order_del'];

    $stmt = $db->prepare("DELETE FROM users_orders WHERE o_id = ?");
    if ($stmt === false) {
        header("Location: error_page.php");
        die("Prepare failed: " . htmlspecialchars($db->error));
    }
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    if (!$stmt->execute()) {
        header("Location: error_page.php");
        die("Execute failed: " . htmlspecialchars($stmt->error));
    }
    $stmt->close();

    header("location:all_orders.php");
} else {
    // Handle the case when 'order_del' is not set
    // You may want to redirect to an error page or handle it differently
    echo "Invalid request!";
}
?>
