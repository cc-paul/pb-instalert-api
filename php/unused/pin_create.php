<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$wid = $_POST["wid"];
    $pin = $_POST["pin"];
	$response = array();
    $error    = false;
    $message  = "";

    $stmt_insert = $pdo->prepare("UPDATE fc_registration SET pin = MD5(:pin) WHERE walletID = :walletID");
    $stmt_insert->bindParam(":pin",$pin,PDO::PARAM_STR);
    $stmt_insert->bindParam(":walletID",$wid,PDO::PARAM_STR);

    if ($stmt_insert->execute()) {
        $error   = false;
        $message = "PIN Code has been created. You can now proceed logging in";
    } else {
        $error   = true;
        $message = "Error creating account";
    }

    $response["error"]   = $error;
    $response["message"] = $message;
    
    echo json_encode($response);
?>