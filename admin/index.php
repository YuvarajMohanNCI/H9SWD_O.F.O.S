<!DOCTYPE html>
<html lang="en">

<?php
include("../connection/connect.php");
error_reporting(0);
session_start();

$sessionTimeout = 10 * 60; // 10 minutes in seconds


if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $sessionTimeout)) {
    session_unset();
    session_destroy();
    header("location: index.php");
    exit; 
}

// Update last activity time on each request
$_SESSION['last_activity'] = time();


function setLoginCookie($userId) {
    $cookieName = 'adm_id';
    $cookieValue = $userId;
    $cookieExpire = time() + (30 * 24 * 60 * 60); // Cookie expires in 30 days

    setcookie($cookieName, $cookieValue, $cookieExpire, "/");
}


function checkLoginStatus() {
    return isset($_COOKIE['adm_id']);
}

$message = '';
$success = '';

if (isset($_POST['submit'])) {

    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);
    $recaptchaSecretKey = "6LciMCopAAAAAIDfgHpsQx0k7Qt-ut0oBbvTUdCE"; 
    $recaptchaResponse = $_POST['g-recaptcha-response'];

    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => $recaptchaSecretKey,
        'response' => $recaptchaResponse,
    ];

    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
        ],
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $response = json_decode($result, true);

    if ($response['success']) {
        if (!empty($_POST["submit"])) {
            $loginquery = "SELECT * FROM admin WHERE username=?";
            $stmt = $db->prepare($loginquery);
            if ($stmt === false) {
                header("Location: error_page.php");
                exit; 
            }
            $stmt->bind_param("s", $username);
            $stmt->execute();
            if (!$stmt->execute()) {
                header("Location: error_page.php");
                exit; 
            }
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            if ($row && password_verify($password, $row['password'])) {
                $_SESSION["adm_id"] = $row['adm_id'];
                header("refresh:1;url=dashboard.php");
            } else {
                echo "<script>alert('Invalid Username or Password!');</script>";
            }
        }
    } else {
        // CAPTCHA verification failed
        $message = "CAPTCHA verification failed. Please try again.";
    }
}

if (checkLoginStatus() && !isset($_SESSION["adm_id"])) {
    $_SESSION["adm_id"] = $_COOKIE['adm_id'];
}
?>

<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css">
    <link rel='stylesheet prefetch' href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,500,700,900'>
    <link rel='stylesheet prefetch' href='https://fonts.googleapis.com/css?family=Montserrat:400,700'>
    <link rel='stylesheet prefetch' href='https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css'>
    <link rel="stylesheet" href="css/login.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body>

    <div class="container">
        <div class="info">
            <h1>Admin Panel </h1>
        </div>
    </div>
    <div class="form">
        <div class="thumbnail"><img src="images/manager.png" /></div>
        <span style="color:red;"><?php echo $message; ?></span>
        <span style="color:green;"><?php echo $success; ?></span>
        <form class="login-form" action="index.php" method="post">
            <input type="text" placeholder="Username" name="username" />
            <input type="password" placeholder="Password" name="password" />
            <div class="g-recaptcha" data-sitekey="6LciMCopAAAAAHgOF7Ev49qTpnYuXz7eotAU9PSi"></div>
            <input type="submit" name="submit" value="Login" />
        </form>
    </div>

    <script src='http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
    <script src='js/index.js'></script>
</body>

</html>
