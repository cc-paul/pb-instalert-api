<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$wid = $_POST["wid"];
	$response = array();
    $error    = false;
    $message  = "";

    $stmt_insert = $pdo->prepare("UPDATE fc_registration SET isVerified = 1 WHERE walletID = :walletID");
    $stmt_insert->bindParam(":walletID",$wid,PDO::PARAM_STR);

    if ($stmt_insert->execute()) {
        $error   = false;
        $message = "Account is now verified. Enjoy!";
    } else {
        $error   = true;
        $message = "Error verifiying account";
    }

    $response["error"]   = $error;
    $response["message"] = $message;
    
    echo json_encode($response);
?>