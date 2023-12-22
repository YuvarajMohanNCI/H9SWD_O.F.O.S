<!DOCTYPE html>
<html lang="en">
<?php
include("connection/connect.php");  
error_reporting(0); 
session_start(); 

$error_message = '';

$current_hour = (int) date('G'); // 'G' returns hours in 24-hour format without leading zeros
$start_hour = 0; // 12 midnight
$end_hour = 6;   // 6 in the morning

if ($current_hour >= $start_hour && $current_hour < $end_hour) {
    // Set the error message
    $error_message = "Sorry, we are not accepting orders at this time. Please try again after 6 AM.";
}
function writeToLog($message) {
    $logFile = 'OFOS.log';
    $currentDateTime = date('Y-m-d H:i:s');
    $logMessage = $currentDateTime . ' - ' . $message . PHP_EOL;

    // Check if log file exists, create if not
    if (!file_exists($logFile)) {
        file_put_contents($logFile, '', FILE_APPEND);
    }

    // Append the log message to the file
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

?>

<head>
    <meta charset="UTF-8">
    <title>Login</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css">

    <link rel='stylesheet prefetch' href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,500,700,900|RobotoDraft:400,100,300,500,700,900'>
    <link rel='stylesheet prefetch' href='https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css'>

    <link rel="stylesheet" href="css/login.css">

    <style type="text/css">
        #buttn {
            color: #fff;
            background-color: #5c4ac7;
        }

    </style>
    <style>
        .error-message {
            color: red;
            text-align: center;
            font-size: 16px;
            margin-top: 10px;
        }
    </style>

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/animsition.min.css" rel="stylesheet">
    <link href="css/animate.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

</head>

<body>
    <header id="header" class="header-scroll top-header headrom">
        <nav class="navbar navbar-dark">
            <div class="container">
                <button class="navbar-toggler hidden-lg-up" type="button" data-toggle="collapse" data-target="#mainNavbarCollapse">&#9776;</button>
                <a class="navbar-brand" href="index.php"> <img class="img-rounded" src="images/icn.png" alt=""> </a>
                <div class="collapse navbar-toggleable-md  float-lg-right" id="mainNavbarCollapse">
                    <ul class="nav navbar-nav">
                        <!-- <li class="nav-item"> <a class="nav-link active" href="index.php">Home <span class="sr-only">(current)</span></a> </li>
                        <li class="nav-item"> <a class="nav-link active" href="restaurants.php">Restaurants <span class="sr-only"></span></a> </li> -->

                        <?php

                        if (empty($_SESSION["user_id"])) {
                            echo '<li class="nav-item"><a href="login.php" class="nav-link active">Login</a> </li>
                                  <li class="nav-item"><a href="registration.php" class="nav-link active">Register</a> </li>';
                        } else {
                            echo  '<li class="nav-item"><a href="your_orders.php" class="nav-link active">My Orders</a> </li>';
                            echo  '<li class="nav-item"><a href="logout.php" class="nav-link active">Logout</a> </li>';
                        }

                        ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <div style=" background-image: url('images/img/pimg.jpg');">

    <?php


    // Database Connection Error Handling
    if ($db->connect_error) {
        header("Location: error_page.php");
    }

    // Define $message and $success variables
    $message = '';
    $success = '';

    // Session Timeout Handling
    $sessionTimeout = 10 * 60; // 10 minutes in seconds
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $sessionTimeout)) {
        session_unset();
        session_destroy();
        header("location: login.php");
        exit();
    }
    $_SESSION['last_activity'] = time();

    // Function definitions
    function setLoginCookie($userId) {
        $cookieName = 'user_id';
        $cookieValue = $userId;
        $cookieExpire = time() + (30 * 24 * 60 * 60); // Cookie expires in 30 days
    
        setcookie($cookieName, $cookieValue, $cookieExpire, "/");
    }
    
    // Function to check if the user is logged in based on the cookie
    function checkLoginStatus() {
        return isset($_COOKIE['user_id']);
    }

    if (isset($_POST['submit'])) {
        try {
                $username = htmlspecialchars($_POST['username']); //Input sanitization
                $password = htmlspecialchars($_POST['password']);
                $recaptchaSecretKey = "6LciMCopAAAAAIDfgHpsQx0k7Qt-ut0oBbvTUdCE"; // Replace with your reCAPTCHA secret key
                $recaptchaResponse = $_POST['g-recaptcha-response'];
            
                // reCAPTCHA verification
                if (!empty($recaptchaResponse)) {
                    $url = 'https://www.google.com/recaptcha/api/siteverify';
                    $data = [
                        'secret' => $recaptchaSecretKey,
                        'response' => $recaptchaResponse,
                        'remoteip' => $_SERVER['REMOTE_ADDR']
                    ];
            
                    $options = [
                        'http' => [
                            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                            'method' => 'POST',
                            'content' => http_build_query($data)
                        ]
                    ];
            
                    $context = stream_context_create($options);
                    $result = file_get_contents($url, false, $context);
                    $response = json_decode($result);
            
                    if ($response->success) {
                        // reCAPTCHA validated successfully
                        if (!empty($_POST["submit"])) {
                            // Input sanitation
                            $username = filter_var($username, FILTER_SANITIZE_STRING);
                            // Get current user's failed attempts and last attempt time
            $checkLockQuery = "SELECT failed_attempts, last_attempt_time FROM users WHERE username = ?";
            $checkLockStmt = $db->prepare($checkLockQuery);
            $checkLockStmt->bind_param("s", $username);
            $checkLockStmt->execute();
            $lockResult = $checkLockStmt->get_result()->fetch_assoc();
            $checkLockStmt->close();
            
            $failedAttempts = $lockResult['failed_attempts'];
            $lastAttemptTime = strtotime($lockResult['last_attempt_time']);
            date_default_timezone_set('Europe/Dublin');
            $currentTime = time();
            writeToLog("Current timezone: " . date_default_timezone_get());
            writeToLog("Current time: " . date('Y-m-d H:i:s', $currentTime));
            writeToLog("Last attempt time: " . date('Y-m-d H:i:s', $lastAttemptTime));
            writeToLog("time difference: ". $currentTime - ($lastAttemptTime + 3600));
            
            // Check if account is locked
            if($failedAttempts >= 3 && $currentTime - ($lastAttemptTime + 3600) < 30 * 60 ) { // 30 minutes lockout
                writeToLog("Failed login attempt for user(max attempt): $username");
                $message = "Account locked due to multiple failed login attempts. Try again later.";
            } else {
                $loginquery = "SELECT * FROM users WHERE username=?";
                            $stmt = $db->prepare($loginquery);
                
                            if ($stmt) {
                                $stmt->bind_param("s", $username);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $row = $result->fetch_assoc();
                                $stmt->close();
                
                                if ($row && password_verify($password, $row['password'])) {
                                    // Reset failed attempts on successful login
                                    writeToLog("Successful login for user: $username");
                                    $resetAttemptsQuery = "UPDATE users SET failed_attempts = 0, last_attempt_time = NULL WHERE username = ?";
                                    $resetStmt = $db->prepare($resetAttemptsQuery);
                                    $resetStmt->bind_param("s", $username);
                                    $resetStmt->execute();
                                    $resetStmt->close();
                                    $_SESSION["user_id"] = $row['u_id'];
                                    setLoginCookie($row['u_id']); // Set a cookie on successful login
                                    header("refresh:1;url=index.php");
                                } else {
                                    // Increment failed attempts
                                    
                                    writeToLog("Failed login attempt for user: $username");
                                    date_default_timezone_set('Europe/Dublin');
                                    $updateAttemptTime = time();
                                    $formattedTime = date('Y-m-d H:i:s', $updateAttemptTime);
                                    writeToLog("updated time in DB: 
                                    $formattedTime");
                                    $updateAttemptsQuery = "UPDATE users SET failed_attempts = failed_attempts + 1, last_attempt_time = ? WHERE username = ?";
                                    $updateStmt = $db->prepare($updateAttemptsQuery);
                                    $updateStmt->bind_param("ss", $formattedTime, $username);
                                    $updateStmt->execute();
                                    $updateStmt->close();
                                    $message = "Invalid Username or Password!";
                                    
                                }
                            } else {
                                echo "Error preparing statement: " . $db->error;
                            }
            }      
                        }
            
                    } else {
                        // reCAPTCHA validation failed
                        $message = "CAPTCHA verification failed. Please try again.";
                    }
                } else {
                    // reCAPTCHA response is empty
                    $message = "Please check the CAPTCHA box.";
                }
            

            // $response = json_decode($result, true);

           
            // Further processing...
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            header("Location: error_page.php");
        }
    }

    // Check for the cookie on each page load
    if (checkLoginStatus() && !isset($_SESSION["user_id"])) {
        $_SESSION["user_id"] = $_COOKIE['user_id'];
    }
    ?>

        <div class="pen-title">
            <
        </div>
        <div>
        <?php if (!empty($error_message)): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>
        </div>

        <div class="module form-module">
            <div class="toggle">

            </div>
            <div class="form">
                <h2>Login to your account</h2>
                <span style="color:red;"><?php echo $message; ?></span>
                <span style="color:green;"><?php echo $success; ?></span>
                <form action="" method="post">
                    <input type="text" placeholder="Username" name="username" />
                    <input type="password" placeholder="Password" name="password" />
                    
                    <div class="g-recaptcha" data-sitekey="6LciMCopAAAAAHgOF7Ev49qTpnYuXz7eotAU9PSi" 
                    style="transform:scale(0.77);-webkit-transform:scale(0.77);transform-origin:0 0;-webkit-transform-origin:0 0;">
                    </div>
                    
                    <input type="submit" id="buttn" name="submit" value="Login" />
                </form>
            </div>

            <div class="cta">Not registered?<a href="registration.php" style="color:#5c4ac7;"> Create an account</a></div>
        </div>
        <script src='http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
        <div class="container-fluid pt-3">
            <p></p>
        </div>
        <footer class="footer">
            <div class="container">
                <div class="bottom-footer">
                    <div class="row">
                        <div class="col-xs-12 col-sm-3 payment-options color-gray">
                            <h5>Payment Options</h5>
                            <ul>
                                <li>
                                    <a href="#"> <img src="images/paypal.png" alt="Paypal"> </a>
                                </li>
                                <li>
                                    <a href="#"> <img src="images/mastercard.png" alt="Mastercard"> </a>
                                </li>
                                <li>
                                    <a href="#"> <img src="images/maestro.png" alt="Maestro"> </a>
                                </li>
                                <li>
                                    <a href="#"> <img src="images/stripe.png" alt="Stripe"> </a>
                                </li>
                                <li>
                                    <a href="#"> <img src="images/bitcoin.png" alt="Bitcoin"> </a>
                                </li>
                            </ul>
                        </div>
                        <div class="col-xs-12 col-sm-4 address color-gray">
                            <h5>Address</h5>
                            <p>1086 Stockert Hollow Road, Seattle</p>
                            <h5>Phone: 75696969855</h5> </div>
                        <div class="col-xs-12 col-sm-5 additional-info color-gray">
                            <h5>Addition informations</h5>
                            <p>Join thousands of other restaurants who benefit from having partnered with us.</p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
</body>

</html>
