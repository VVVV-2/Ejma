<?php
    include "includes/config.php";

    if (!isset($_GET['email']) || !isset($_GET['token']))  {

        Header('Location: register.php');

    } else {

        try {
            $con = new PDO('mysql:host=ejma.nu.mysql;dbname=ejma_nucore', USER, DBPASS);
            $con->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            } catch(PDOException $e) {
            echo $e;
            file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);
        };

        try {
            $sql = $con->prepare("SELECT id FROM accounts WHERE email = :email AND token = :token AND emailConfirmed = 0");
            $sql->bindParam(':email', $_GET['email']);
            $sql->bindParam(':token', $_GET['token']);
            $sql->execute();
            if ($sql->rowCount() > 0) {
                $sql = $con->prepare("UPDATE accounts SET emailConfirmed = 1 WHERE email = :email AND token = :token");
                $sql->bindParam(':email', $_GET['email']);
                $sql->bindParam(':token', $_GET['token']);
                $sql->execute();

                Header('Location: login.php');
                
            } 
        } catch(PDOException $e) {
            echo $e;
            file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);
        }
    }

?>