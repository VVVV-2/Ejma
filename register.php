<?php

    include "includes/config.php";


    if(isset($_POST['email']) && isset($_POST['password']) && isset($_POST['cPassword'])) {
      if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
        $msg = "Invalid email format. ";
      } else if($_POST['password'] != $_POST['cPassword']){
        $msg = "The passwords do not match.";
      } else {

        $alias = $_POST['alias'];
        $password = $_POST['password'];
        $email = $_POST['email'];
        $hashed = password_hash($password, PASSWORD_DEFAULT);
    
        $str = str_shuffle("qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM123456789");
        $token = substr($str, 0, 10);
    
        //New connection
        try {
          $con = new PDO('mysql:host=ejma.nu.mysql;dbname=ejma_nucore', USER, DBPASS);
            $con->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            } catch(PDOException $e) {
            echo $e;
            file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);
        };
    
    
        try {
            $sql = $con->prepare("SELECT email, alias FROM accounts WHERE email = :email OR alias = :alias");
            $sql->bindParam(':email', $email);
            $sql->bindParam(':alias', $alias);
            $sql->execute();
            $res = $sql->fetch();

            if ($sql->rowCount() > 0) {
              
              if($res['email'] === $email){
                
                $msg = "This email is already in use.";
              
              } else if( $res['alias'] === $alias){
                
                $msg = "This alias is taken.";

              }
                
            } else {

              try {
    
                $sql = $con->prepare("INSERT INTO accounts ( alias, email, password, token) VALUES (:alias, :email, :password, :token)");
                        
                        $sql->bindParam(':alias', $alias);
                        $sql->bindParam(':email', $email);
                        $sql->bindParam(':password', $hashed);
                        $sql->bindParam(':token', $token);
                        $sql->execute();
        
                        $mail->setFrom("william@ejma.nu");
                        $mail->addAddress($email);
                        $mail->Subject = "Please verify email!";
                        $mail->isHTML(true);
                        $mail->Body = "Hello, " . $alias . "! Please click this <a href='https://ejma.nu/verifyEmail.php?email=$email&token=$token'>link</a> to verify your email.";
                        $msg =  "Account created. A verification link has been sent to your email adress.";
        
                        try {
                            $mail->send();
                        } catch (Exception $e) {
                            file_put_contents('PHPMailerErrors.txt', $mail->ErrorInfo, FILE_APPEND);
                        }
        
                } catch(PDOException $e) {
                    $msg = "Something went wrong, please try again.";
                    file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);
                }
              
            }
        } catch(PDOException $e) {
            $msg = "Something went wrong, please try again.";
            file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);
        };
      };
    };


?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Ejma | Create account </title>
  </head>
  <body>
  <?php include "includes/header.php"; ?>
  <div class="container vf">
      <form action="" method="post" class="vf">
        <fieldset>
          <legend>Register account</legend>
          <p>
            <label for="alias">Alias</label>
            <input id="alias" type="text" name="alias" placeholder="Alias" required />
          </p>
          <p>
            <label for="email">Email</label>
            <input id="email" type="text" name="email" placeholder="Email address" required />
          </p>
          <p>
            <label for="password">Password</label>
            <input id="password" type="password" name="password" placeholder="Password" required />
          </p>
          <p>
            <label for="password-confirm">Confirm password</label>
            <input id="password-confirm" type="password" name="cPassword" placeholder="Confirm password" required />
          </p>
          <p>
            <input type="checkbox" name="terms" id="terms" required>I understand the&#32;<a href="terms.php">terms and conditions</a>.
          </p>
          <button type="submit">
            Create account
          </button>
        </fieldset>
        <?php if(isset($msg)){ echo "<p>" . $msg . "</p>"; }?>
      </form>
      </div>
      <?php include "includes/footer.php"; ?>
  </body>
</html>
