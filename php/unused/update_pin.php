<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$id  = $_POST["id"];
    $pin = $_POST["pin"];
	$response = array();
    $error    = false;
    $message  = "";

    $stmt_insert = $pdo->prepare("UPDATE fc_registration SET pin = MD5(:pin) WHERE id = :id");
    $stmt_insert->bindParam(":pin",$pin,PDO::PARAM_STR);
    $stmt_insert->bindParam(":id",$id,PDO::PARAM_STR);

    if ($stmt_insert->execute()) {
        $error   = false; 
        $message = "PIN has been reset.";
    } else {
        $error   = true;
        $message = "Error changing PIN";
    }

    $response["error"]   = $error;
    $response["message"] = $message;
    
    echo json_encode($response);
?>