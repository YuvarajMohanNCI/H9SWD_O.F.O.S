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

if (isset($_GET['cat_del'])) {
    $categoryId = $_GET['cat_del'];

    // Prepare the SQL statement
    $stmt = $db->prepare("DELETE FROM res_category WHERE c_id = ?");
    if ($stmt === false) {
        header("Location: error_page.php");
        die("Prepare failed: " . htmlspecialchars($db->error));
    }

    // Bind parameters
    $stmt->bind_param("i", $categoryId);

    // Execute the statement
    if (!$stmt->execute()) {
        header("Location: error_page.php");
        die("Execute failed: " . htmlspecialchars($stmt->error));
    }

    $stmt->close();

    // Redirect to the category page
    header("location:add_category.php");
} else {
    echo "Invalid request!";
}
?>


