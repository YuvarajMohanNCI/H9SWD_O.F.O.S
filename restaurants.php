<!DOCTYPE html>
<html lang="en">
<?php
include("connection/connect.php"); 
error_reporting(0);
session_start();


if (empty($_SESSION["user_id"]) || (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 600))) {
    session_unset();
    session_destroy();
    header("Location: login.php"); 
    exit();
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();
?>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Browse and order from your favorite restaurants">
    <meta name="author" content="Your Name or Company">
    <link rel="icon" href="images/favicon.ico">
    <title>Restaurants</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/animsition.min.css" rel="stylesheet">
    <link href="css/animate.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet"> 
</head>

<body>
    <header id="header" class="header-scroll top-header headrom">
        <nav class="navbar navbar-dark">
            <div class="container">
                <button class="navbar-toggler hidden-lg-up" type="button" data-toggle="collapse" data-target="#mainNavbarCollapse">&#9776;</button>
                <a class="navbar-brand" href="index.php"> <img class="img-rounded" src="images/icn.png" alt="Restaurant Logo"> </a>
                <div class="collapse navbar-toggleable-md float-lg-right" id="mainNavbarCollapse">
                    <ul class="nav navbar-nav">
                        <li class="nav-item"> <a class="nav-link active" href="index.php">Home <span class="sr-only">(current)</span></a> </li>
                        <li class="nav-item"> <a class="nav-link active" href="restaurants.php">Restaurants <span class="sr-only"></span></a> </li>
                        
                        <?php
                        if(empty($_SESSION["user_id"])) {
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
    <div class="page-wrapper">
        <div class="top-links">
            <div class="container">
                <ul class="row links">
                    <li class="col-xs-12 col-sm-4 link-item active"><span>1</span><a href="#">Choose Restaurant</a></li>
                    <li class="col-xs-12 col-sm-4 link-item"><span>2</span><a href="#">Pick Your Favorite Food</a></li>
                    <li class="col-xs-12 col-sm-4 link-item"><span>3</span><a href="#">Order and Pay</a></li>
                </ul>
            </div>
        </div>
        <div class="inner-page-hero bg-image" data-image-src="images/img/pimg.jpg">
            <div class="container"> </div>
        </div>
        <div class="result-show">
            <div class="container">
                <div class="row">     
                </div>
            </div>
        </div>
        <section class="restaurants-page">
            <div class="container">
                <div class="row">
                    <div class="col-xs-12 col-sm-5 col-md-5 col-lg-3">
                    </div>
                    <div class="col-xs-12 col-sm-7 col-md-7 col-lg-9">
                        <div class="bg-gray restaurant-entry">
                            <div class="row">
                                <?php 
                                try {
                                    if ($db->connect_error) {
                                        throw new Exception("Connection failed: " . $db->connect_error);
                                    }
                                    $stmt = $db->prepare("SELECT * FROM restaurant");
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    while($rows = $result->fetch_assoc()) {
                                        $title = htmlspecialchars($rows['title'], ENT_QUOTES, 'UTF-8');
                                        $address = htmlspecialchars($rows['address'], ENT_QUOTES, 'UTF-8');
                                        $image = htmlspecialchars($rows['image'], ENT_QUOTES, 'UTF-8');
                                        $rs_id = htmlspecialchars($rows['rs_id'], ENT_QUOTES, 'UTF-8');

                                        echo '<div class="col-sm-12 col-md-12 col-lg-8 text-xs-center text-sm-left">
                                            <div class="entry-logo">
                                                <a class="img-fluid" href="dishes.php?res_id=' . $rs_id . '">
                                                <img src="admin/Res_img/' . $image . '" alt="Food logo"></a>
                                            </div>
                                            <div class="entry-dscr">
                                                <h5><a href="dishes.php?res_id=' . $rs_id . '">' . $title . '</a></h5>
                                                <span>' . $address . '</span>
                                            </div>
                                        </div>';
                                    }
                                } catch (Exception $e) {
                                    header("Location: error_page.php");
                                    exit();
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <footer class="footer">
        <div class="container">
            <div class="bottom-footer">
                <div class="row">
                    <div class="col-xs-12 col-sm-3 payment-options color-gray">
                        <h5>Payment Options</h5>
                        <ul>
                            <li><a href="#"> <img src="images/paypal.png" alt="Paypal"> </a></li>
                            <li><a href="#"> <img src="images/mastercard.png" alt="Mastercard"> </a></li>
                            <li><a href="#"> <img src="images/maestro.png" alt="Maestro"> </a></li>
                            <li><a href="#"> <img src="images/stripe.png" alt="Stripe"> </a></li>
                            <li><a href="#"> <img src="images/bitcoin.png" alt="Bitcoin"> </a></li>
                        </ul>
                    </div>
                    <div class="col-xs-12 col-sm-4 address color-gray">
                        <h5>Address</h5>
                        <p>1086 Stockert Hollow Road, Seattle</p>
                        <h5>Phone: 75696969855</h5> 
                    </div>
                    <div class="col-xs-12 col-sm-5 additional-info color-gray">
                        <h5>Additional Information</h5>
                        <p>Join thousands of other restaurants who benefit from having partnered with us.</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="js/jquery.min.js"></script>
    <script src="js/tether.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/animsition.min.js"></script>
    <script src="js/bootstrap-slider.min.js"></script>
    <script src="js/jquery.isotope.min.js"></script>
    <script src="js/headroom.js"></script>
    <script src="js/foodpicky.min.js"></script>
</body>
</html>
