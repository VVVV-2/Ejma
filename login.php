<?php 

    include "includes/config.php";

    //Logging in

    if(isset($_POST['email']) && isset($_POST['password'])) {

        $email = $_POST['email'];
        $password = $_POST['password'];

        //New connection
        try {
            $con = new PDO('mysql:host=ejma.nu.mysql;dbname=ejma_nucore', USER, DBPASS);
            $con->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            } catch(PDOException $e) {
            echo $e;
            file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);
        };


        try {
            $sql = $con->prepare("SELECT id, token, emailConfirmed, stripeCustomerId, password FROM accounts WHERE email = :email");
            $sql->bindParam(':email', $email);
            $sql->execute();
                
        } catch(PDOException $e) {
            echo $e;
            $msg =  "Something went wrong fetching user from db, please try again.";
            file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);
        }

        if($sql->rowCount() === 0) {

            $msg = "This email does not belong to a registered account.";

        } else if ($sql->rowCount() > 0) {
            
            $res = $sql->fetch();
            $emailConfirmed = $res['emailConfirmed'];
            $hashed = $res['password'];
            $token = $res['token'];
            $customer = $res['stripeCustomerId'];


            switch($emailConfirmed) {
                case 0:
                    $msg =  "This email has not been verified. Check your spam filter for another verification link.";
                    $mail = new PHPMailer(true);
                    $mail->setFrom("william@ejma.nu");
                    $mail->addAddress($email);
                    $mail->Subject = "Please verify email!";
                    $mail->isHTML(true);
                    $mail->Body = "Please click this <a href='https://ejma.nu/confirm-email?email=$body->email&token=$token'>link</a> to verify your email.";
                    try {
                        $mail->send();
                    } catch (Exception $e) {
                        file_put_contents('PHPMailerErrors.txt', $mail->ErrorInfo, FILE_APPEND);
                    }
                    break;
                case 1: 
                    if ( password_verify($password, $hashed )) {
                        try{
        
                            // Create a new customer object
                            $customer = $stripe->customers->create([
                                'email' => $email,
                            ]);
            
                            $sql = $con->prepare("UPDATE accounts SET emailConfirmed = 2, stripeCustomerId = '$customer->id' WHERE email = :email AND emailConfirmed = 1 AND stripeCustomerId IS NULL");
                            $sql->bindParam(':email', $email);
                            
                            
                            if ( $sql->execute() && password_verify($password, $hashed )) {
        
                                
                                $_SESSION['customer'] = $customer->id;

                            
                            } else {
                                $msg = "Invalid password.";
                            };
        
                        } catch(PDOException $e) {
                            echo $e;
                            file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);
                        };
                    } else {
                        $msg =  "Invalid password.";
                    };
                    
                    break;
                case 2:
                    if ( password_verify($password, $hashed )) {
                        
                        $_SESSION['customer'] = $customer;
                        Header('Location: account.php');

                    } else {
                        $msg = "Invalid password.";
                    };
                    break;
            }
                

            } else {
                $msg =  "This email does not belong to a registered account.";
            }
    
    }
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Ejma | Log in</title>
</head>
<body>
<?php include "includes/header.php"; ?>
<div class="container vf">
    <form action="" method="post">
        <fieldset>
            <legend>Log in</legend>
            <p>
                <label for="email">Email</label>
                <input type="text" name="email" id="email" placeholder="Email" required>
            </p>
            <p>
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Password" required>
            </p>
            <button type="submit">Log in</button>
        </fieldset> 
        <?php if(isset($msg)){ echo "<p>" . $msg . "</p>"; }?>
    </form>
    </div>
    <?php include "includes/footer.php"; ?>
</body>
</html>