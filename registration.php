<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("connection/connect.php");

function writeToLog($message) {
   $logFile = 'OFOS.log';
   $currentDateTime = date('Y-m-d H:i:s');
   $logMessage = $currentDateTime . ' - ' . $message . PHP_EOL;

  
   if (!file_exists($logFile)) {
       file_put_contents($logFile, '', FILE_APPEND);
   }

   
   file_put_contents($logFile, $logMessage, FILE_APPEND);
}

$errors = [];

if (isset($_POST['submit'])) {
    if (empty($_POST['firstname']) || empty($_POST['lastname']) || empty($_POST['email']) || empty($_POST['phone']) || empty($_POST['password']) || empty($_POST['cpassword']) || empty($_POST['username']) || empty($_POST['address'])) {
        $errors[] = "All fields must be filled out!";
    } else {

        $check_user_email = mysqli_prepare($db, "SELECT username, email FROM users WHERE username = ? OR email = ?");

        mysqli_stmt_bind_param($check_user_email, 'ss', $_POST['username'], $_POST['email']);

        mysqli_stmt_execute($check_user_email);

        mysqli_stmt_store_result($check_user_email);

        if ($_POST['password'] != $_POST['cpassword']) {
            $errors[] = "Passwords do not match!";
        } elseif (strlen($_POST['password']) < 7 || !preg_match('/^(?=.*[0-9!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{6,}$/', $_POST['password'])) {
            $errors[] = "Password must be at least 7 characters long and include at least one number or symbol!";
        } elseif (strlen($_POST['phone']) < 10) {
            $errors[] = "Invalid phone number!";
        } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email address! Please enter a valid email.";
        } if (mysqli_stmt_num_rows($check_user_email) > 0) {
            $check_user_email_result = mysqli_stmt_get_result($check_user_email);
            $existing_data = mysqli_fetch_assoc($check_user_email_result);
         
            if ($existing_data['username'] == $_POST['username']) {
                $errors[] = "Username already exists!";
            }
         
            if ($existing_data['email'] == $_POST['email']) {
                $errors[] = "Email already exists!";
            }
        }
       else {
         try{

            $mql = "INSERT INTO users(username, f_name, l_name, email, phone, password, address) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($db, $mql);

            // Hash the password
            $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            // Sanitize inputs
            $sanitized_username = htmlspecialchars($_POST['username']);
            $sanitized_firstname = htmlspecialchars($_POST['firstname']);
            $sanitized_lastname = htmlspecialchars($_POST['lastname']);
            $sanitized_email = htmlspecialchars($_POST['email']);
            $sanitized_phone = htmlspecialchars($_POST['phone']);
            $sanitized_address = htmlspecialchars($_POST['address']);

            // Bind parameters
            mysqli_stmt_bind_param($stmt, 'sssssss', $sanitized_username, $sanitized_firstname, $sanitized_lastname, $sanitized_email, $sanitized_phone, $hashed_password, $sanitized_address);

            if (mysqli_stmt_execute($stmt)) {
               writeToLog("New user added: $sanitized_username");
                header("refresh:0.1;url=login.php");
            } else {
                $errors[] = "Registration failed. Please try again later.";
                
            }
        }catch (Exception $e) {
         $message = 'Error: ' . $e->getMessage();
         header("Location: error_page.php");
     }
        
      }
    }
}
?>


<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="#">
    <title>Registration</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/animsition.min.css" rel="stylesheet">
    <link href="css/animate.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet"> </head>
<body>
<div style=" background-image: url('images/img/pimg.jpg');">
         <header id="header" class="header-scroll top-header headrom">
            <nav class="navbar navbar-dark">
               <div class="container">
                  <button class="navbar-toggler hidden-lg-up" type="button" data-toggle="collapse" data-target="#mainNavbarCollapse">&#9776;</button>
                  <a class="navbar-brand" href="index.php"> <img class="img-rounded" src="images/icn.png" alt=""> </a>
                  <div class="collapse navbar-toggleable-md  float-lg-right" id="mainNavbarCollapse">
                     <ul class="nav navbar-nav">
							<li class="nav-item"> <a class="nav-link active" href="index.php">Home <span class="sr-only">(current)</span></a> </li>
                            <li class="nav-item"> <a class="nav-link active" href="restaurants.php">Restaurants <span class="sr-only"></span></a> </li>
                            
							<?php
						if(empty($_SESSION["user_id"]))
							{
								echo '<li class="nav-item"><a href="login.php" class="nav-link active">Login</a> </li>
							  <li class="nav-item"><a href="registration.php" class="nav-link active">Register</a> </li>';
							}
						else
							{
									
									
										echo  '<li class="nav-item"><a href="your_orders.php" class="nav-link active">My Orders</a> </li>';
									echo  '<li class="nav-item"><a href="logout.php" class="nav-link active">Logout</a> </li>';
							}

						?>
                  <?php if (!empty($errors)) : ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach ($errors as $error) : ?>
                <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
							 
                        </ul>
                  </div>
               </div>
            </nav>
         </header>
         <div class="page-wrapper">
            
               <div class="container">
                  <ul>
                    
                    
                  </ul>
               </div>
            
            <section class="contact-page inner-page">
               <div class="container ">
                  <div class="row ">
                     <div class="col-md-12">
                        <div class="widget" >
                           <div class="widget-body">
                            
							  <form action="" method="post">
                                 <div class="row">
								  <div class="form-group col-sm-12">
                                       <label for="exampleInputEmail1">User-Name</label>
                                       <input class="form-control" type="text" name="username" id="example-text-input"> 
                                    </div>
                                    <div class="form-group col-sm-6">
                                       <label for="exampleInputEmail1">First Name</label>
                                       <input class="form-control" type="text" name="firstname" id="example-text-input"> 
                                    </div>
                                    <div class="form-group col-sm-6">
                                       <label for="exampleInputEmail1">Last Name</label>
                                       <input class="form-control" type="text" name="lastname" id="example-text-input-2"> 
                                    </div>
                                    <div class="form-group col-sm-6">
                                       <label for="exampleInputEmail1">Email Address</label>
                                       <input type="text" class="form-control" name="email" id="exampleInputEmail1" aria-describedby="emailHelp"> 
                                    </div>
                                    <div class="form-group col-sm-6">
                                       <label for="exampleInputEmail1">Phone number</label>
                                       <input class="form-control" type="text" name="phone" id="example-tel-input-3"> 
                                    </div>
                                    <div class="form-group col-sm-6">
                                       <label for="exampleInputPassword1">Password</label>
                                       <input type="password" class="form-control" pattern="^(?=.*[0-9!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{7,}$" name="password" id="exampleInputPassword1" placeholder="Password must be at least 7 characters long and include at least one number or symbol" > 
                                    </div>
                                    <div class="form-group col-sm-6">
                                       <label for="exampleInputPassword1">Confirm password</label>
                                       <input type="password" class="form-control" pattern="^(?=.*[0-9!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{7,}$" name="cpassword" id="exampleInputPassword2"> 
                                    </div>
									 <div class="form-group col-sm-12">
                                       <label for="exampleTextarea">Delivery Address</label>
                                       <textarea class="form-control" id="exampleTextarea"  name="address" rows="3"></textarea>
                                    </div>
                                   
                                 </div>
                                
                                 <div class="row">
                                    <div class="col-sm-4">
                                       <p> <input type="submit" value="Register" name="submit" class="btn theme-btn"> </p>
                                    </div>
                                 </div>
                              </form>
                  
						   </div>
           
                        </div>
                     
                     </div>
                    
                  </div>
               </div>
            </section>
            
      
            <footer class="footer">
               <div class="container">
           
                  <div class="row bottom-footer">
                     <div class="container">
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
                                    <h5>Phone: 75696969855</a></h5> </div>
                                <div class="col-xs-12 col-sm-5 additional-info color-gray">
                                    <h5>Addition informations</h5>
                                   <p>Join thousands of other restaurants who benefit from having partnered with us.</p>
                                </div>
                        </div>
                     </div>
                  </div>
      
               </div>
            </footer>
         
         </div>
       
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