<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);


	$username = $_POST["username"];
	$email    = $_POST["email"];
	$password = $_POST["password"];
	$walletid = "WID" . date('mdy-his', time());

	$response = array();
    $result   = array();
    $error    = false;
    $message  = "";

    $stmt_find = $pdo->prepare("SELECT * FROM fc_registration WHERE email = :email");
    $stmt_find->bindParam(":email",$email,PDO::PARAM_STR);

    if ($stmt_find->execute()) {
        $row = $stmt_find->fetch(PDO::FETCH_ASSOC);
        $stmt_find->closeCursor();

        if ($row) {
            $error = true;
            $message = "Email already exist";
        } else {
            $stmt_find = $pdo->prepare("SELECT * FROM fc_registration WHERE username = :username");
            $stmt_find->bindParam(":username",$username,PDO::PARAM_STR);

            if ($stmt_find->execute()) {
                $row = $stmt_find->fetch(PDO::FETCH_ASSOC);
                $stmt_find->closeCursor();

                if ($row) {
                    $error = true;
                    $message = "Username already exist";
                } else {
                    $stmt_insert = $pdo->prepare("INSERT INTO fc_registration (walletID,username,email,password,dateCreated) VALUES (:walletID,:username,:email,MD5(:password),:dateCreated)");
                    $stmt_insert->bindParam(":walletID",$walletid,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":username",$username,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":email",$email,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":password",$password,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":dateCreated",$global_date,PDO::PARAM_STR);

                    if ($stmt_insert->execute()) {
                        $error   = false;
                        $message = "Account has been saved. You can now login your account";
                    } else {
                        $error   = true;
                        $message = "Error saving account details";
                    }
                }
            } else {
                $error = true;
                $message = "Error checking account";
            }
        }
    } else {
		$error = true;
        $message = "Error checking account";
    }

    $response["error"]   = $error;
    $response["message"] = $message;
    $response["result"]  = $result;
    
    echo json_encode($response);
?>