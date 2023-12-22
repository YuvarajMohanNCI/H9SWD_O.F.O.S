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

if (isset($_GET['user_del'])) {
    $userId = $_GET['user_del'];

    $stmt = $db->prepare("DELETE FROM users WHERE u_id = ?");
    if ($stmt === false) {
        header("Location: error_page.php");
        die("Prepare failed: " . htmlspecialchars($db->error)); // Corrected here
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    if (!$stmt->execute()) {
        header("Location: error_page.php");
        die("Execute failed: " . htmlspecialchars($stmt->error)); // Corrected here
    }
    $stmt->close();

    header("location:all_users.php");
} else {
    // Handle the case when 'user_del' is not set
    // You may want to redirect to an error page or handle it differently
    echo "Invalid request!";
}
?>
