<?php
session_start();

function clearLoginCookie() {
    $cookieName = 'user_id';
    
    
    setcookie($cookieName, '', time() - 3600, "/");
}

function writeToLog($message) {
    $logFile = 'OFOS.log';
    $currentDateTime = date('Y-m-d H:i:s');
    $logMessage = $currentDateTime . ' - ' . $message . PHP_EOL;

    
    if (!file_exists($logFile)) {
        file_put_contents($logFile, '', FILE_APPEND);
    }

    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}



// Clear session variables
$_SESSION = array();



if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Clear the login cookie
clearLoginCookie();

// Destroy the session
session_destroy();
writeToLog("Account logout for user: $username");
header('Location: login.php');
exit();
?>

