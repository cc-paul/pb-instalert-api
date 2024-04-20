<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$wid      = $_POST["wid"];
    $address  = $_POST["address"];
	$response = array();
    $error    = false;
    $message  = "";

    $stmt_insert = $pdo->prepare("UPDATE fc_approval SET address = :address WHERE walletID = :walletID AND `status` = 'Approved'");
    $stmt_insert->bindParam(":address",$address,PDO::PARAM_STR);
    $stmt_insert->bindParam(":walletID",$wid,PDO::PARAM_STR);

    if ($stmt_insert->execute()) {
        $error   = false;
        $message = "Address has benn updated";
    } else {
        $error   = true;
        $message = "Error updating address";
    }

    $response["error"]   = $error;
    $response["message"] = $message;
    
    echo json_encode($response);
?>